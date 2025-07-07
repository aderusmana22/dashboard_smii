<?php

namespace App\Imports;

use App\Models\StandardBudget;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
// use Maatwebsite\Excel\Concerns\WithBatchInserts; // Keep BATCHING COMMENTED OUT for now
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // For DB::raw if needed, though usually not for this

class StandardBudgetsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithStartRow,
    // WithBatchInserts, // Keep BATCHING COMMENTED OUT
    WithChunkReading
{
    use Importable, SkipsFailures;

    protected array $monthNameMapping;
    private int $excelRowCounterForLog = 0;

    public function __construct()
    {
        $this->monthNameMapping = [
            'januari' => 1, 'jan' => 1, 'january' => 1, 'februari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3, 'april' => 4, 'apr' => 4, 'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6, 'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'aug' => 8, 'august' => 8, 'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10, 'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
        ];
        $this->excelRowCounterForLog = $this->startRow() - 1;
    }

    public function startRow(): int { return 3; }
    public function headingRow(): int { return 2; }

    protected function getMonthNumberFromString(?string $monthInput): ?int
    {
        if ($monthInput === null || trim($monthInput) === '') return null;
        if (is_numeric($monthInput)) {
            $monthNum = (int)$monthInput;
            return ($monthNum >= 1 && $monthNum <= 12) ? $monthNum : null;
        }
        $monthInputLower = strtolower(trim($monthInput));
        return $this->monthNameMapping[$monthInputLower] ?? null; // Simpler lookup
    }

    private function findKeyCaseInsensitive(string $targetKey, array $array): ?string
    {
        $targetKeyLower = strtolower($targetKey);
        foreach (array_keys($array) as $key) {
            if (strtolower(trim((string)$key)) === $targetKeyLower) return $key;
        }
        return null;
    }

    public function model(array $row)
    {
        $this->excelRowCounterForLog++;
        $logPrefix = "[IMPORTER][ExcelRow:{$this->excelRowCounterForLog}]";
        Log::debug("{$logPrefix} Processing raw row data: " . json_encode($row));

        // --- Key Finding (ensure this part is robust) ---
        $brandNameKey = $this->findKeyCaseInsensitive('brand_name', $row);
        $nameRegionKey = $this->findKeyCaseInsensitive('name_region', $row);
        $amountKey = $this->findKeyCaseInsensitive('amount', $row);
        $monthKey = $this->findKeyCaseInsensitive('month', $row);
        $yearKey = $this->findKeyCaseInsensitive('year', $row);

        if (!$brandNameKey || !$nameRegionKey || !$amountKey || !$monthKey || !$yearKey) {
            Log::warning("{$logPrefix} Missing one or more required column headers. Skipping. Row data: " . json_encode($row));
            $this->failures[] = new Failure($this->excelRowCounterForLog, 'structure_error', ['Missing required headers.'], $row);
            return null;
        }

        // --- Data Parsing ---
        // For brand_name and name_region, we'll use them as-is for storing,
        // but for matching, we might need a case-insensitive approach.
        $brandName = trim((string)($row[$brandNameKey] ?? null));
        $nameRegion = trim((string)($row[$nameRegionKey] ?? null));

        $amountRaw = (string)($row[$amountKey] ?? null);
        $amountStr = $amountRaw;
        // Your amount parsing logic... (ensure it handles various decimal formats correctly)
        if (strpos($amountRaw, ',') !== false && strpos($amountRaw, '.') !== false) {
            if (strrpos($amountRaw, ',') > strrpos($amountRaw, '.')) { // Comma is decimal
                $amountStr = str_replace('.', '', $amountRaw); $amountStr = str_replace(',', '.', $amountStr);
            } else { /* Dot is decimal */ $amountStr = str_replace(',', '', $amountRaw); }
        } elseif (strpos($amountRaw, ',') !== false) { $amountStr = str_replace(',', '.', $amountRaw); }
        $amount = is_numeric($amountStr) ? (float)$amountStr : null;

        $monthExcelValue = (string)($row[$monthKey] ?? null);
        $monthNumber = $this->getMonthNumberFromString($monthExcelValue);
        $year = isset($row[$yearKey]) && is_numeric($row[$yearKey]) ? (int)$row[$yearKey] : null;

        // --- Logging Parsed Values (Essential for Debugging) ---
        Log::info("{$logPrefix} Parsed - Brand: '{$brandName}', Region: '{$nameRegion}', Amount: {$amount}, MonthNum: {$monthNumber}, Year: {$year}");

        // --- Validation (Data from prepareForValidation is already validated by rules) ---
        // But we can add a check here if critical parsed values are null
        if ($brandName === "" || $nameRegion === "" || $amount === null || $monthNumber === null || $year === null) {
            Log::warning("{$logPrefix} One or more critical parsed values are null/empty after parsing. Brand: '{$brandName}', Region: '{$nameRegion}', Amount: " . ($amount ?? 'NULL') . ", Month: " . ($monthNumber ?? 'NULL') . ", Year: " . ($year ?? 'NULL') . ". Skipping.");
            $this->failures[] = new Failure($this->excelRowCounterForLog, 'parsing_error', ['Critical data missing after parsing.'], $row);
            return null;
        }


        // --- Database Operation with Case-Insensitive Matching for brand_name and name_region ---
        try {
            // Attributes to uniquely identify the record.
            // For case-insensitive search, we build a more explicit query.
            $query = StandardBudget::query()
                ->whereRaw('LOWER(brand_name) = ?', [strtolower($brandName)])
                ->whereRaw('LOWER(name_region) = ?', [strtolower($nameRegion)])
                ->where('month', $monthNumber)
                ->where('year', $year);

            $existingBudget = $query->first();

            // Data to be inserted or updated
            // We use the original casing for brand_name and name_region for storage.
            $dataForStorage = [
                'brand_name'  => $brandName, // Store with original casing from Excel
                'name_region' => $nameRegion, // Store with original casing
                'month'       => $monthNumber,
                'year'        => $year,
                'amount'      => $amount,
            ];

            if ($existingBudget) {
                Log::info("{$logPrefix} Found existing record with ID: {$existingBudget->id}. Attempting to update amount.");
                $existingBudget->amount = $amount; // Only update amount, or other fields as needed
                $existingBudget->save();
                $budget = $existingBudget;
                if ($budget->wasChanged()) {
                     Log::info("{$logPrefix} Record UPDATED. ID: {$budget->id}. Changes: " . json_encode($budget->getChanges()));
                } else {
                     Log::info("{$logPrefix} Record found (ID: {$budget->id}) but amount was already the same. No actual update performed.");
                }
            } else {
                Log::info("{$logPrefix} No existing record found. Attempting to create new record.");
                $budget = StandardBudget::create($dataForStorage); // create() uses $fillable
                Log::info("{$logPrefix} Record CREATED. New ID: {$budget->id}");
            }

            return $budget;

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("{$logPrefix} QueryException during DB operation: " . $e->getMessage());
            Log::error("{$logPrefix} SQL: " . $e->getSql() . " Bindings: " . json_encode($e->getBindings()));
            $this->failures[] = new Failure($this->excelRowCounterForLog, 'db_error', [$e->getMessage()], $row);
            return null;
        } catch (\Exception $e) {
            Log::error("{$logPrefix} General Exception during DB operation: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
            $this->failures[] = new Failure($this->excelRowCounterForLog, 'general_error', [$e->getMessage()], $row);
            return null;
        }
    }

    // --- Validation Section (rules are applied to data from prepareForValidation) ---
    public function prepareForValidation($data, $index) {
        // This is called BEFORE model(). Data returned here is validated.
        $logPrefix = "[IMPORTER][PREPARE_VALIDATION][ExcelRow:" . ($this->startRow() + $index) . "]"; // $index is 0-based for data rows

        // Parse month to numeric for validation
        $monthExcelValue = $data[$this->findKeyCaseInsensitive('month', $data)] ?? null;
        $data['month_numeric_for_validation'] = $this->getMonthNumberFromString((string)$monthExcelValue);
        Log::debug("{$logPrefix} month_excel: '{$monthExcelValue}', month_numeric_for_validation: " . ($data['month_numeric_for_validation'] ?? 'null'));


        // Parse amount to numeric for validation
        $amountRaw = (string)($data[$this->findKeyCaseInsensitive('amount', $data)] ?? null);
        $amountStr = $amountRaw;
        if (strpos($amountRaw, ',') !== false && strpos($amountRaw, '.') !== false) {
            if (strrpos($amountRaw, ',') > strrpos($amountRaw, '.')) {
                $amountStr = str_replace('.', '', $amountRaw); $amountStr = str_replace(',', '.', $amountStr);
            } else { $amountStr = str_replace(',', '', $amountRaw); }
        } elseif (strpos($amountRaw, ',') !== false) { $amountStr = str_replace(',', '.', $amountRaw); }
        $data['amount_numeric_for_validation'] = $amountStr; // Keep as string for 'numeric' rule, it will be cast later
        Log::debug("{$logPrefix} amount_raw: '{$amountRaw}', amount_numeric_for_validation: '{$data['amount_numeric_for_validation']}'");

        // Case-normalize brand_name and name_region for validation if needed,
        // though usually validation rules like 'string' don't care about case.
        // For storage, we keep original case. For matching, we handle case-insensitivity in model()
        $data['brand_name_for_validation'] = trim((string)($data[$this->findKeyCaseInsensitive('brand_name', $data)] ?? null));
        $data['name_region_for_validation'] = trim((string)($data[$this->findKeyCaseInsensitive('name_region', $data)] ?? null));


        return $data;
    }

    public function rules(): array {
        return [
            // Validate the original headers if they exist, or the derived keys for validation
            '*.brand_name_for_validation' => ['required', 'string', 'max:255'],
            '*.name_region_for_validation' => ['required', 'string', 'max:255'],

            // 'amount' (original value) should be present
            '*.amount' => ['required'],
            // 'amount_numeric_for_validation' should be numeric
            '*.amount_numeric_for_validation' => ['required', 'numeric', 'min:0'], // Max value can be added if needed

            // 'month' (original value) should be present and a string
            '*.month' => ['required', 'string'],
            // 'month_numeric_for_validation' should be a valid month number
            '*.month_numeric_for_validation' => ['required', 'integer', 'min:1', 'max:12'],

            '*.year' => ['required', 'integer', 'digits:4', 'min:1990', 'max:' . (date('Y') + 10)],
        ];
    }

    public function customValidationMessages() {
        return [
            '*.brand_name_for_validation.required' => 'Brand Name (brand_name) wajib diisi.',
            '*.name_region_for_validation.required' => 'Name Region (name_region) wajib diisi.',
            '*.amount.required' => 'Amount (amount) wajib diisi.',
            '*.amount_numeric_for_validation.required' => 'Amount (amount) harus angka yang valid.',
            '*.amount_numeric_for_validation.numeric' => 'Amount (amount) harus berupa angka.',
            '*.amount_numeric_for_validation.min' => 'Amount (amount) tidak boleh negatif.',
            '*.month.required' => 'Month (month) wajib diisi.',
            '*.month.string' => 'Month (month) harus berupa teks atau angka.',
            '*.month_numeric_for_validation.required' => 'Month (month) tidak valid atau tidak dikenali (misal: Jan, 1, Februari).',
            '*.month_numeric_for_validation.integer' => 'Month (month) tidak valid.',
            '*.month_numeric_for_validation.min' => 'Month (month) tidak valid (min 1).',
            '*.month_numeric_for_validation.max' => 'Month (month) tidak valid (max 12).',
            '*.year.required' => 'Year (year) wajib diisi.',
            '*.year.integer' => 'Year (year) harus berupa angka.',
            '*.year.digits' => 'Year (year) harus 4 digit.',
            '*.year.min' => 'Year (year) minimal 1990.',
            '*.year.max' => 'Year (year) maksimal ' . (date('Y') + 10) . '.',
        ];
    }

    public function onFailure(Failure ...$failures) {
        foreach ($failures as $failure) {
            $logPrefix = "[IMPORTER][VALIDATION_FAILURE][ExcelRow:{$failure->row()}]";
            Log::warning("{$logPrefix} Attribute: '{$failure->attribute()}', Errors: " . implode(', ', $failure->errors()) . ", Values: " . json_encode($failure->values()));

            // Add to the collection of failures that will be passed back to the controller
            $this->failures[] = $failure;
        }
    }

    // public function batchSize(): int { return 500; } // Keep BATCHING COMMENTED OUT
    public function chunkSize(): int { return 500; }
}