<?php

namespace App\Imports;

use App\Models\ForecastImport;
use App\Rules\ValidMonthFormat; // <-- 1. Panggil Rule kustom kita
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

// 2. Hapus 'PrepareForValidation' dari sini
class ForecastsImport implements ToModel, WithHeadingRow, WithValidation
{
    // Fungsi normalizeMonth tetap kita butuhkan untuk mengubah data SEBELUM disimpan
    private function normalizeMonth($monthValue)
    {
        if (is_numeric($monthValue) && $monthValue >= 1 && $monthValue <= 12) {
            return (int)$monthValue;
        }
        if (!is_string($monthValue)) {
            return $monthValue;
        }
        $cleanedMonth = strtolower(trim($monthValue));
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];
        return $monthMap[$cleanedMonth] ?? $monthValue;
    }

    public function headingRow(): int
    {
        return 2;
    }

    // 3. Hapus method prepareForValidation() dari sini

    public function model(array $row)
    {
        // Kita tetap harus menormalisasi bulan di sini sebelum menyimpan ke database
        $normalizedMonth = $this->normalizeMonth($row['month']);

        return ForecastImport::updateOrCreate(
            [
                'item_number' => $row['item_number'],
                'month'       => $normalizedMonth, // Gunakan bulan yang sudah dinormalisasi
                'year'        => $row['year'],
            ],
            [
                'description' => $row['description'],
                'unit'        => $row['unit'],
                'tonage'      => $row['tonage'],
            ]
        );
    }

    public function rules(): array
    {
        return [
            // 4. Ganti aturan validasi untuk 'month' dengan Rule kustom kita
            '*.item_number' => 'required',
            '*.month'       => ['required', new ValidMonthFormat()], // <-- PERUBAHAN DI SINI
            '*.year'        => 'required|integer|min:2000',
            '*.unit'        => 'required|numeric',
            '*.tonage'      => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        // Kita tidak perlu lagi pesan kustom untuk 'month.integer' karena sudah ditangani oleh Rule
        return [
            '*.item_number.required' => 'Kolom item_number tidak boleh kosong.',
            '*.month.required'       => 'Kolom month tidak boleh kosong.',
            '*.year.required'        => 'Kolom year tidak boleh kosong.',
            '*.year.integer'         => 'Kolom year harus berupa angka (contoh: 2025).',
            '*.year.min'             => 'Tahun minimal harus 2000.',
            '*.unit.required'        => 'Kolom unit tidak boleh kosong.',
            '*.unit.numeric'         => 'Kolom unit harus berupa angka.',
            '*.tonage.required'      => 'Kolom tonage tidak boleh kosong.',
            '*.tonage.numeric'       => 'Kolom tonage harus berupa angka.',
        ];
    }
}