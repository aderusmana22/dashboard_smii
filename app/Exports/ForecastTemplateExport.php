<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ForecastTemplateExport implements WithHeadings, WithTitle, WithEvents, ShouldAutoSize
{
    public function headings(): array
    {
        // ===================================================================
        // PERUBAHAN KRUSIAL DI SINI:
        // Header ini HARUS sama persis dengan key yang digunakan di ForecastsImport.php
        // Gunakan huruf kecil dan underscore, bukan spasi.
        // ===================================================================
        return [
            'item_number',
            'description',
            'month',
            'year',
            'unit',
            'tonage',
        ];
    }

    public function title(): string
    {
        return 'Template Import Forecast';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Menambahkan 1 baris kosong di atas untuk Title
                $sheet->insertNewRowBefore(1, 1);

                // 2. Mengatur Baris 1: Title
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Template Import Forecast');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 3. Mengatur Baris 2: Header
                $sheet->getStyle('A2:F2')->getFont()->setBold(true);
                
                // Data akan dimulai dari baris 3 (dibiarkan kosong oleh pengguna)
            },
        ];
    }
}