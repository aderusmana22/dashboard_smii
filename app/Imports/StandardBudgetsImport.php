<?php

namespace App\Imports;

use App\Models\StandardBudget;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // This Concern is important
use Maatwebsite\Excel\Concerns\SkipsFailures;  // This Trait is important
use Maatwebsite\Excel\Validators\Failure;       // This is the Failure object
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;

class StandardBudgetsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure, // Tells the import to skip rows that fail validation and call onFailure
    WithStartRow,
    WithBatchInserts,
    WithChunkReading
{
    use Importable, SkipsFailures; // This trait provides $this->failures and the failures() method

    // ... (startRow, headingRow, model, rules, customValidationMessages, batchSize, chunkSize are the same) ...
    // Your existing startRow(), headingRow(), model(), rules(), customValidationMessages(), batchSize(), chunkSize() methods remain as they are.


    /**
     * This method is called by the SkipsOnFailure concern for each row that fails validation.
     * The SkipsFailures trait then typically uses this hook to add the failure to its internal collection.
     * If you define this method, ensure it doesn't prevent the trait from collecting.
     * However, often, if you just use the SkipsFailures trait, it handles the collection
     * when SkipsOnFailure is active.
     *
     * For logging purposes, this is fine.
     */
    public function onFailure(Failure ...$failures)
    {
        // Your existing logging is good.
        foreach ($failures as $failure) {
            Log::warning(
                'Excel import validation failure on Excel row ' . $failure->row() .
                ' for attribute "' . $failure->attribute() .
                '": ' . implode(', ', $failure->errors()) .
                '. Input Values: ' . json_encode($failure->values())
            );
        }

        // IMPORTANT: Call the parent onFailure if the trait defines one,
        // OR ensure the SkipsFailures trait mechanism for collecting is not broken.
        // Many implementations of SkipsFailures might rely on this method being called
        // on the trait itself if it's also defined there.
        // However, a common pattern is for the trait to have a protected method
        // or directly manipulate its internal failures array when this hook is called by SkipsOnFailure.

        // Let's ensure the trait's collection logic runs by explicitly adding.
        // This is what SkipsFailures trait's own onFailure usually does.
        foreach ($failures as $failure) {
            $this->failures[] = $failure; // Add to the protected $failures array provided by the trait
        }
    }

     // PASTE YOUR EXISTING METHODS HERE:
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 3;
    }

    /**
     * @return int
     */
    public function headingRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $nameRegion = trim($row['name_region']);
        $amount = (float)$row['amount'];
        $year = (int)$row['year'];

        if (empty($nameRegion) && empty($row['amount']) && empty($row['year'])) {
             return null;
        }
        return StandardBudget::updateOrCreate(
            ['name_region' => $nameRegion, 'year' => $year,],
            ['amount' => $amount,]
        );
    }

    public function rules(): array
    {
        return [
            '*.name_region' => ['required', 'string', 'max:255'],
            '*.amount'      => ['required', 'numeric', 'min:0'],
            '*.year'        => ['required', 'integer', 'digits:4', 'min:1990', 'max:' . (date('Y') + 10)],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.name_region.required' => 'Kolom Name Region wajib diisi.',
            '*.name_region.string'   => 'Kolom Name Region harus berupa teks.',
            '*.name_region.max'      => 'Name Region maksimal 255 karakter.',
            '*.amount.required' => 'Kolom Amount wajib diisi.',
            '*.amount.numeric'  => 'Kolom Amount harus berupa angka.',
            '*.amount.min'      => 'Kolom Amount minimal 0.',
            '*.year.required' => 'Kolom Tahun wajib diisi.',
            '*.year.integer'  => 'Kolom Tahun harus berupa angka bulat.',
            '*.year.digits'   => 'Kolom Tahun harus terdiri dari 4 digit.',
            '*.year.min'      => 'Kolom Tahun minimal 1990.',
            '*.year.max'      => 'Kolom Tahun maksimal ' . (date('Y') + 10) . '.',
        ];
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