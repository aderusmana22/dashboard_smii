<?php

namespace App\Exports;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class TasksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected $filters;
    protected $authUser;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->authUser = Auth::user(); // Simpan user yang melakukan ekspor
    }

    public function query()
    {
        $query = Task::query()->with([
            'pengaju:id,name,nik',
            'department:id,department_name',
            'penutup:id,name,nik',
            'approvalDetails' => function ($q) {
                $q->with('approver:id,name,nik')->orderBy('processed_at', 'desc');
            }
        ])->latest('created_at');


        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['pengaju_id'])) {
            $query->where('pengaju_id', $this->filters['pengaju_id']);
        }
        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            $query->whereBetween('tasks.created_at', [
                Carbon::parse($this->filters['date_from'])->startOfDay(),
                Carbon::parse($this->filters['date_to'])->endOfDay()
            ]);
        } elseif (!empty($this->filters['date_from'])) {
            $query->where('tasks.created_at', '>=', Carbon::parse($this->filters['date_from'])->startOfDay());
        } elseif (!empty($this->filters['date_to'])) {
            $query->where('tasks.created_at', '<=', Carbon::parse($this->filters['date_to'])->endOfDay());
        }
        if (!empty($this->filters['search'])) {
            $searchTerm = '%' . $this->filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id_job', 'like', $searchTerm)
                  ->orWhere('area', 'like', $searchTerm)
                  ->orWhere('list_job', 'like', $searchTerm)
                  ->orWhereHas('pengaju', function($sq) use ($searchTerm){
                      $sq->where('name', 'like', $searchTerm);
                  })
                  ->orWhereHas('department', function($sq) use ($searchTerm){
                      $sq->where('department_name', 'like', $searchTerm);
                  });
            });
        }

        // Hak Akses dan Filter Departemen
        $user = $this->authUser; // Gunakan user yang tersimpan
        if ($user->isSuperAdmin() || $user->isAdminProject()) {
            if (!empty($this->filters['department_id'])) {
                $query->where('department_id', $this->filters['department_id']);
            }
        } else {
            // User Biasa
            if (!empty($this->filters['department_id'])) {
                if ($this->filters['department_id'] == $user->department_id) {
                    $query->where('department_id', $this->filters['department_id']);
                } else {
                    $query->where('pengaju_id', $user->id)
                          ->where('department_id', $this->filters['department_id']);
                }
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('pengaju_id', $user->id);
                    if ($user->department_id) {
                        $q->orWhere('department_id', $user->department_id);
                    }
                });
            }
        }
        return $query;
    }

public function map($task): array
{
    $processedApproval = $task->processedApprovalDetail();
    $approverUser = $processedApproval ? $processedApproval->approver : null; // Ambil user approver
    $approvalDetailStatus = $processedApproval ? $processedApproval->status_text : 'N/A'; // Gunakan accessor jika ada di JobApprovalDetail

    return [
        $task->id_job,
        $task->pengaju->name ?? 'N/A',
        $task->department->department_name ?? 'N/A',
        $task->area,
        $task->list_job,
        $task->tanggal_job_mulai ? $task->tanggal_job_mulai->format('Y-m-d') : '',
        $task->tanggal_job_selesai ? $task->tanggal_job_selesai->format('Y-m-d') : '',
        $task->status_text, // Menggunakan accessor dari model Task
        $approverUser ? $approverUser->name : 'N/A',
        $approvalDetailStatus, // Menggunakan accessor dari model JobApprovalDetail jika ada
        $processedApproval && $processedApproval->processed_at ? $processedApproval->processed_at->format('Y-m-d H:i:s') : '',
        $processedApproval ? $processedApproval->notes : '',
        $task->cancel_reason ?? '',
        $task->penutup->name ?? '',
        $task->closed_at ? $task->closed_at->format('Y-m-d H:i:s') : '',
        $task->created_at->format('Y-m-d H:i:s'),
    ];
}

    public function headings(): array
    {
        return [
            'ID Job', 'Pengaju', 'Dept. Tujuan', 'Area', 'List Job', 'Tgl Mulai',
            'Tgl Selesai', 'Status Task', 'Diproses Oleh (Approver)', 'Status Approval',
            'Tgl Proses Approval', 'Catatan Approval', 'Alasan Batal', 'Ditutup Oleh',
            'Tgl Ditutup', 'Tgl Dibuat',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:P1')->getFont()->setBold(true); // Sesuaikan range kolom P jika headings berubah
        return []; // Return array kosong jika tidak ada style spesifik lainnya
    }

    public function title(): string
    {
        return 'Laporan Tugas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->setAutoFilter('A1:' . $highestColumn . '1');
                $sheet->freezePane('A2');
            },
        ];
    }
}