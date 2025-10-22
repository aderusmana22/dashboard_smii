<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidMonthFormat implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Jika sudah berupa angka yang valid, cek rentangnya
        if (is_numeric($value)) {
            return $value >= 1 && $value <= 12;
        }

        // Jika bukan string, langsung gagal
        if (!is_string($value)) {
            return false;
        }

        // Ubah ke huruf kecil dan hapus spasi untuk pencocokan
        $cleanedMonth = strtolower(trim($value));

        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];

        // Aturan lolos jika nama bulan ada di dalam map
        return array_key_exists($cleanedMonth, $monthMap);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Kolom :attribute harus berupa angka (1-12) atau nama bulan yang valid (e.g., Januari, Jan).';
    }
}