<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PPICStockExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $data;
    protected $date;

    public function __construct(array $data, string $date)
    {
        $this->data = $data;
        $this->date = $date;
    }

    public function collection()
    {
        // Mengubah array data menjadi collection
        return collect($this->data)->map(function ($item) {
            // Pastikan urutan kolom sesuai dengan headings
            return [
                'item_number'    => $item['item_number'],
                'description'    => $item['description'],
                'month'          => $item['month'],
                'year'           => $item['year'],
                'inventory_qty'  => $item['inventory_qty'],
                'dispatch_qty'   => $item['dispatch_qty'],
                'allocated_qty'  => $item['allocated_qty'],
                'so_outstanding' => $item['so_outstanding'],
                'mps_qty'        => $item['mps_qty'],
                'forecast_unit'  => $item['forecast_unit'],
                'forecast_tonage'=> $item['forecast_tonage'],
            ];
        });
    }

    public function headings(): array
    {
        // Ini akan menjadi header di Baris 3
        return [
            'Item Number',
            'Description',
            'Month',
            'Year',
            'Inventory Qty',
            'Dispatch Qty',
            'Allocated Qty',
            'SO Outstanding',
            'MPS Qty',
            'Forecast Unit',
            'Forecast Tonage', // Disesuaikan dengan nama field di database
        ];
    }

    public function title(): string
    {
        return 'Report Stock PPIC';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Menambahkan 2 baris kosong di atas untuk Title dan Tanggal
                $sheet->insertNewRowBefore(1, 2);

                // 2. Mengatur Baris 1: Title
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'Report Stock PPIC');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 3. Mengatur Baris 2: Tanggal
                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue('A2', 'Tanggal: ' . $this->date);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 4. Mengatur Baris 3: Header
                $sheet->getStyle('A3:K3')->getFont()->setBold(true);

                // 5. Mengatur lebar kolom agar otomatis (opsional, tapi bagus)
                foreach (range('A', 'K') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            },
        ];
    }
}