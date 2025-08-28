<?php

namespace App\Exports;

use App\Models\SalesTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color as PhpColor;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesByBrandExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $startMonth;
    protected $endMonth;
    protected $startDate;
    protected $endDate;
    protected Collection $data;

    public function __construct(string $startMonth, string $endMonth)
    {
        $this->startMonth = $startMonth;
        $this->endMonth = $endMonth;
        $this->startDate = Carbon::parse($startMonth)->startOfMonth();
        $this->endDate = Carbon::parse($endMonth)->endOfMonth();

        $this->data = SalesTransaction::query()
            ->select(
                'pt_prod_line',
                'pl_desc as brand',
                DB::raw('SUM(tr_ton) as total_tonnage'),
                DB::raw('SUM(value) as total_value'),
                DB::raw('SUM(margin) as total_margin')
            )
            ->whereBetween('tr_effdate', [$this->startDate, $this->endDate])
            ->groupBy('pt_prod_line', 'pl_desc')
            ->orderBy('pt_prod_line')
            ->get();
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($row): array
    {
        $percentage = ($row->total_value > 0) ? ($row->total_margin / $row->total_value) : 0;

        return [
            $row->pt_prod_line,
            $row->brand,
            $row->total_tonnage,
            $row->total_value,
            $row->total_margin,
            $percentage,
        ];
    }

    public function headings(): array
    {
        return []; // Dikosongkan karena header dibuat manual
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // --- MODIFIKASI: Mengatur ruang sisipan untuk tata letak baru ---
                $sheet->insertNewRowBefore(1, 4); // Data akan dimulai di baris ke-5

                // 1. Tambahkan Judul Utama Laporan (di baris 1)
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Sales by Brand Report');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- MODIFIKASI: Menambahkan Effective Date sesuai format baru ---
                $dateString = 'Effective Date : ' . $this->startDate->format('d/m/Y') . ' - ' . $this->endDate->format('d/m/Y');
                
                $sheet->mergeCells('C2:D2'); // Gabungkan sel C2 dan D2
                $sheet->setCellValue('C2', $dateString);
                $sheet->getStyle('C2')->getFont()->setBold(true);
                // --- AKHIR MODIFIKASI KRITERIA ---

                // 3. Buat Header Kustom (sekarang di baris 4)
                $headerRange = 'A4:F4';
                $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2E3E5');
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A4:B4');
                $sheet->setCellValue('A4', 'BRAND');
                $sheet->setCellValue('C4', 'Tonage');
                $sheet->setCellValue('D4', 'Value');
                $sheet->setCellValue('E4', 'Margin');
                $sheet->setCellValue('F4', '%');
                
                // Data dimulai dari baris ke-5 sekarang
                $lastRow = $sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                // 4. Hitung dan tambahkan Baris GRAND TOTAL
                $totalTonnage = $this->data->sum('total_tonnage');
                $totalValue   = $this->data->sum('total_value');
                $totalMargin  = $this->data->sum('total_margin');
                $totalPercentage = ($totalValue > 0) ? ($totalMargin / $totalValue) : 0;
                
                $sheet->setCellValue("B{$totalRow}", 'GRAND TOTAL COMPANY');
                $sheet->setCellValue("C{$totalRow}", $totalTonnage);
                $sheet->setCellValue("D{$totalRow}", $totalValue);
                $sheet->setCellValue("E{$totalRow}", $totalMargin);
                $sheet->setCellValue("F{$totalRow}", $totalPercentage);
                
                $sheet->getStyle("B{$totalRow}:F{$totalRow}")->getFont()->setBold(true)->setColor(new PhpColor('9C0006'));

                // 5. Tambahkan "End of Report"
                $endReportRow = $totalRow + 1;
                $sheet->mergeCells("C{$endReportRow}:D{$endReportRow}");
                $sheet->setCellValue("C{$endReportRow}", 'End of Report');
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(12);
        
        // --- MODIFIKASI: Data sekarang dimulai dari baris ke-5 ---
        $lastDataRow = $sheet->getHighestRow() + 1;
        $numberFormat = '_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)';
        
        // Terapkan format ke range data yang baru (mulai dari baris 5)
        $sheet->getStyle("C5:E{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle("F5:F{$lastDataRow}")->getNumberFormat()->setFormatCode('0.00%');

        // Terapkan perataan ke range data yang baru (mulai dari baris 5)
        $sheet->getStyle("C5:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}