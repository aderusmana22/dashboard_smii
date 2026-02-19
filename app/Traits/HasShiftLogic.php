<?php

namespace App\Traits;

use App\Models\OilShift;
use Carbon\Carbon;

trait HasShiftLogic
{
    /**
     * Mendapatkan Context Shift Lengkap.
     * Mengembalikan object berisi:
     * - current_shift & current_date
     * - editable_list (Collection untuk dropdown Supervisor)
     */
    public function getShiftContext()
    {
        $now = Carbon::now(); // Waktu Server (misal: 2026-02-16 09:55)
        $currentTime = $now->format('H:i:s');

        // 1. Ambil Config Shift dari DB (Pastikan tabel oil_shifts ada isinya)
        // Default fallback jika DB kosong
        $shifts = OilShift::orderBy('id')->get();

        $currentShift = 1;
        $operationalDate = $now->format('Y-m-d');

        // 2. Tentukan Current Shift berdasarkan Jam
        if ($shifts->isEmpty()) {
            // Hardcode Fallback jika tabel shifts kosong
            $hour = $now->hour;
            if ($hour >= 6 && $hour < 14)
                $currentShift = 1;
            elseif ($hour >= 14 && $hour < 22)
                $currentShift = 2;
            else {
                $currentShift = 3;
                if ($hour < 6)
                    $operationalDate = $now->copy()->subDay()->format('Y-m-d');
            }
        } else {
            // Logic Dinamis dari DB
            foreach ($shifts as $s) {
                // Cek Cross Day (Start > End, misal 22:00 - 06:00)
                if ($s->start_time > $s->end_time) {
                    if ($currentTime >= $s->start_time || $currentTime < $s->end_time) {
                        $currentShift = $s->id;
                        // Jika jam 00:00 s/d jam berakhir shift, itu tanggal kemarin
                        if ($currentTime < $s->end_time) {
                            $operationalDate = $now->copy()->subDay()->format('Y-m-d');
                        }
                        break;
                    }
                } else {
                    // Shift Normal (Start < End, misal 06:00 - 14:00)
                    if ($currentTime >= $s->start_time && $currentTime < $s->end_time) {
                        $currentShift = $s->id;
                        break;
                    }
                }
            }
        }

        // 3. Generate Daftar Shift yang Boleh Diedit Supervisor
        $editableList = collect([]);

        // A. Current Shift (Selalu ada)
        $editableList->push([
            'shift' => $currentShift,
            'date' => $operationalDate,
            'label' => "Current: Shift $currentShift (" . Carbon::parse($operationalDate)->format('d M') . ")"
        ]);

        // B. Logic Mundur (Previous Shifts)
        // Aturan: 
        // - Shift 1 Pagi -> Bisa edit Shift 3 Kemarin.
        // - Shift 2 Siang -> Bisa edit Shift 1 Hari Ini.
        // - Shift 3 Malam -> Bisa edit Shift 2 & 1 Hari Ini.

        if ($currentShift == 1) {
            // Mundur ke Shift 3 Kemarin
            $prevDate = Carbon::parse($operationalDate)->subDay()->format('Y-m-d');
            $editableList->push([
                'shift' => 3,
                'date' => $prevDate,
                'label' => "Previous: Shift 3 (" . Carbon::parse($prevDate)->format('d M') . ")"
            ]);
        } elseif ($currentShift == 2) {
            // Mundur ke Shift 1 Hari Ini
            $editableList->push([
                'shift' => 1,
                'date' => $operationalDate,
                'label' => "Previous: Shift 1 (Today)"
            ]);
        } elseif ($currentShift == 3) {
            // Mundur ke Shift 2 & 1 Hari Ini
            $editableList->push([
                'shift' => 2,
                'date' => $operationalDate,
                'label' => "Previous: Shift 2 (Today)"
            ]);
            $editableList->push([
                'shift' => 1,
                'date' => $operationalDate,
                'label' => "Previous: Shift 1 (Today)"
            ]);
        }

        return (object) [
            'current_shift' => $currentShift,
            'current_date' => $operationalDate,
            'editable_list' => $editableList
        ];
    }
}