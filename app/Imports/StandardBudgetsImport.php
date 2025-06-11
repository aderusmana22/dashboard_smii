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
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StandardBudgetsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithStartRow,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsFailures;

    protected $monthNameMapping;

    public function __construct()
    {
        $this->monthNameMapping = [
            'januari' => 1, 'jan' => 1, 'january' => 1,
            'februari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3,
            'april' => 4, 'apr' => 4, 'april' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6,
            'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'aug' => 8, 'august' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10, 'october' => 10,
            'november' => 11, 'nov' => 11, 'november' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12, 'december' => 12,
        ];
    }


    public function startRow(): int
    {
        return 3;
    }

    public function headingRow(): int
    {
        return 2;
    }

    protected function getMonthNumberFromString(?string $monthInput): ?int
    {
        if (empty($monthInput)) {
            return null;
        }
        if (is_numeric($monthInput)) {
            $monthNum = (int)$monthInput;
            if ($monthNum >= 1 && $monthNum <= 12) {
                return $monthNum;
            }
        }

        $monthInputLower = strtolower(trim($monthInput));
        if (isset($this->monthNameMapping[$monthInputLower])) {
            return $this->monthNameMapping[$monthInputLower];
        }

        try {
            $date = Carbon::parse($monthInput);
            return $date->month;
        } catch (\Exception $e) {
        }
        return null;
    }

    public function model(array $row)
    {
        $brandNameKey = $this->findKeyCaseInsensitive('brand_name', $row);
        $nameRegionKey = $this->findKeyCaseInsensitive('name_region', $row);
        $amountKey = $this->findKeyCaseInsensitive('amount', $row);
        $monthKey = $this->findKeyCaseInsensitive('month', $row);
        $yearKey = $this->findKeyCaseInsensitive('year', $row);

        if (!$brandNameKey || !$nameRegionKey || !$amountKey || !$monthKey || !$yearKey) {
             Log::warning('Excel import skipped a row due to missing key columns: ' . json_encode(array_keys($row)));
             $this->failures[] = new Failure(
                 $row['excel_row_number'] ?? $this->getExcelRowNumber(),
                 'structure',
                 ['Missing one or more required column headers (brand_name, name_region, amount, month, year).'],
                 $row
             );
             return null;
        }

        $brandName = trim($row[$brandNameKey]);
        $nameRegion = trim($row[$nameRegionKey]);
        $amountStr = str_replace('.', '', (string)$row[$amountKey]);
        $amountStr = str_replace(',', '.', $amountStr);
        $amount = (float)$amountStr;

        $monthExcelValue = $row[$monthKey];
        $monthNumber = $this->getMonthNumberFromString((string)$monthExcelValue);

        $year = (int)$row[$yearKey];

        if (empty($brandName) && empty($nameRegion) && empty($amount) && $monthNumber === null && empty($year)) {
             return null;
        }

        if ($monthNumber === null && !empty($monthExcelValue)) {
            Log::warning("Gagal mengkonversi nama bulan '{$monthExcelValue}' ke angka pada baris Excel.");
        }


        return StandardBudget::updateOrCreate(
            [
                'brand_name' => $brandName,
                'name_region' => $nameRegion,
                'month' => $monthNumber,
                'year' => $year,
            ],
            [
                'amount' => $amount,
            ]
        );
    }

    private function findKeyCaseInsensitive(string $targetKey, array $array): ?string
    {
        foreach (array_keys($array) as $key) {
            if (strtolower(trim((string)$key)) === strtolower($targetKey)) {
                return $key;
            }
        }
        return null;
    }

    private $currentRowNumber = 0;
    protected function getExcelRowNumber() {
        if (property_exists($this, 'headingRowOffset')) {
             return $this->headingRowOffset + $this->currentRowNumber + $this->startRow() -1;
        }
        return $this->currentRowNumber + $this->startRow();
    }
     public function prepareForValidation($data, $index)
    {
        $this->currentRowNumber = $index;
        $monthExcelValue = $data[$this->findKeyCaseInsensitive('month', $data)] ?? null;
        $data['month_numeric'] = $this->getMonthNumberFromString((string)$monthExcelValue);
        return $data;
    }


    public function rules(): array
    {
        return [
            '*.brand_name'  => ['required', 'string', 'max:255'],
            '*.name_region' => ['required', 'string', 'max:255'],
            '*.amount'      => ['required', 'numeric', 'min:0'],
            '*.month_numeric' => ['required', 'integer', 'min:1', 'max:12'],
            '*.month'       => ['required', 'string'],
            '*.year'        => ['required', 'integer', 'digits:4', 'min:1990', 'max:' . (date('Y') + 10)],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.brand_name.required' => 'Kolom Brand Name (brand_name) wajib diisi.',
            '*.month.required' => 'Kolom Bulan (month) wajib diisi dengan nama bulan (misal: Januari, Februari).',
            '*.month.string'   => 'Kolom Bulan (month) harus berupa teks nama bulan.',
            '*.month_numeric.required' => 'Nama bulan pada kolom "month" tidak valid atau tidak dapat dikenali.',
            '*.month_numeric.integer'  => 'Nama bulan pada kolom "month" tidak valid atau tidak dapat dikenali.',
            '*.month_numeric.min'      => 'Nama bulan pada kolom "month" tidak valid atau tidak dapat dikenali.',
            '*.month_numeric.max'      => 'Nama bulan pada kolom "month" tidak valid atau tidak dapat dikenali.',
            '*.year.required' => 'Kolom Tahun (year) wajib diisi.',
            '*.year.integer'  => 'Kolom Tahun (year) harus berupa angka bulat.',
            '*.year.digits'   => 'Kolom Tahun (year) harus terdiri dari 4 digit.',
            '*.year.min'      => 'Kolom Tahun (year) minimal 1990.',
            '*.year.max'      => 'Kolom Tahun (year) maksimal ' . (date('Y') + 10) . '.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $attribute = $failure->attribute();
            if ($attribute === 'month_numeric') {
                $newErrors = ['Nama bulan pada kolom "month" tidak valid atau tidak dapat dikenali. Nilai yang diberikan: ' . ($failure->values()['month'] ?? 'Kosong')];
                 $customFailure = new Failure($failure->row(), 'month', $newErrors, $failure->values());
                 Log::warning(
                    'Excel import validation failure on Excel row ' . $customFailure->row() .
                    ' for attribute "' . $customFailure->attribute() .
                    '": ' . implode(', ', $customFailure->errors()) .
                    '. Input Values: ' . json_encode($customFailure->values())
                );
                $this->failures[] = $customFailure;
            } else {
                Log::warning(
                    'Excel import validation failure on Excel row ' . $failure->row() .
                    ' for attribute "' . $attribute .
                    '": ' . implode(', ', $failure->errors()) .
                    '. Input Values: ' . json_encode($failure->values())
                );
                $this->failures[] = $failure;
            }
        }
    }


    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}