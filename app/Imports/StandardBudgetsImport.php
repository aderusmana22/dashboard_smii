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

class StandardBudgetsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithStartRow
{
    use Importable, SkipsFailures;
    
    /**
     * @return int
     */
    public function startRow(): int
    {
        // Start with row 3 (first data row after headers)
        return 3;
    }

    /**
     * @return int
     */
    public function headingRow(): int
    {
        // The headers are in row 2
        return 2;
    }
    
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Debug log to verify row data
        \Log::info('Processing Excel row:', $row);
        
        // Skip empty rows
        if (empty($row['name_region']) && empty($row['amount']) && empty($row['year'])) {
            return null;
        }
        
        // Trim whitespace from string fields
        $nameRegion = trim($row['name_region']);
        
        return new StandardBudget([
            'name_region' => $nameRegion,
            'amount'      => (float)$row['amount'],
            'year'        => (int)$row['year'],
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.name_region' => ['required', 'string', 'max:255'],
            '*.amount'      => ['required', 'numeric', 'min:0'],
            '*.year'        => ['required', 'integer', 'digits:4', 'min:1990', 'max:' . (date('Y') + 10)],
        ];
    }

    /**
     * @param Failure ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            \Log::warning('Excel import failure on row ' . $failure->row() . ': ' . implode(', ', $failure->errors()));
        }
    }
}