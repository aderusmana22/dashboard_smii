<?php

namespace App\Http\Controllers;

use App\Models\StandardBudget;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // <<<< PERUBAHAN DI SINI
use Illuminate\Support\Facades\Validator;
use App\Imports\StandardBudgetsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class StandardBudgetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StandardBudget::query();

            if ($request->filled('year')) {
                $query->where('year', $request->input('year'));
            }

            $data = $query->select(['id', 'brand_name', 'name_region', 'amount', 'month', 'year']);

            // Sekarang DataTables::of() akan merujuk ke facade yang benar
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="ids[]" class="checkbox_ids" value="' . $row->id . '">';
                })
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 4, ',', '.');
                })
                ->editColumn('month', function ($row) {
                    return Carbon::create()->month($row->month)->translatedFormat('M');
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button type="button" class="btn btn-sm btn-primary edit-btn" data-id="' . $row->id . '">Edit</button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-danger delete-btn ms-1" data-id="' . $row->id . '">Hapus</button>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        $years = StandardBudget::distinct()->orderBy('year', 'desc')->pluck('year');
        $currentYear = date('Y');
        if (!$years->contains($currentYear)) {
            $yearsCollection = collect($years->all());
            $yearsCollection->prepend((int)$currentYear);
            $years = $yearsCollection->sortDesc()->values();
        }

        return view('page.standard_budgets.index', compact('years'));
    }

    // ... sisa controller Anda ...
    public function store(Request $request) // For manual "Add Data" modal
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => [
                'required', 'string', 'max:255',
                Rule::unique('standard_budgets')->where(function ($query) use ($request) {
                    return $query->where('name_region', $request->name_region)
                                 ->where('month', $request->month)
                                 ->where('year', $request->year);
                }),
            ],
            'name_region' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,4})?$/',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|digits:4|min:1990|max:' . (date('Y') + 10),
        ], [
            'brand_name.unique' => 'Kombinasi Brand, Region, Bulan, dan Tahun ini sudah ada.',
            'amount.regex' => 'Format Amount tidak valid. Gunakan titik (.) sebagai pemisah desimal dan maksimal 4 angka di belakang koma.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        StandardBudget::create($request->all());
        return response()->json(['success' => 'Data Standard Budget berhasil ditambahkan.']);
    }

    public function edit(StandardBudget $standardBudget) // For manual "Edit"
    {
        $standardBudget->amount = number_format($standardBudget->amount, 4, '.', '');
        return response()->json($standardBudget);
    }

    public function update(Request $request, StandardBudget $standardBudget) // For manual "Edit" save
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => [
                'required', 'string', 'max:255',
                Rule::unique('standard_budgets')->where(function ($query) use ($request) {
                    return $query->where('name_region', $request->name_region)
                                 ->where('month', $request->month)
                                 ->where('year', $request->year);
                })->ignore($standardBudget->id),
            ],
            'name_region' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,4})?$/',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|digits:4|min:1990|max:' . (date('Y') + 10),
        ], [
            'brand_name.unique' => 'Kombinasi Brand, Region, Bulan, dan Tahun ini sudah ada untuk entri lain.',
            'amount.regex' => 'Format Amount tidak valid. Gunakan titik (.) sebagai pemisah desimal dan maksimal 4 angka di belakang koma.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $standardBudget->update($request->all());
        return response()->json(['success' => 'Data Standard Budget berhasil diperbarui.']);
    }

    public function destroy(StandardBudget $standardBudget) // For single delete
    {
        $standardBudget->delete();
        return response()->json(['success' => 'Data Standard Budget berhasil dihapus.']);
    }

    public function bulkDestroy(Request $request) // For bulk delete
    {
        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['error' => 'Tidak ada data yang dipilih untuk dihapus.'], 400);
        }

        try {
            $deletedCount = StandardBudget::whereIn('id', $ids)->delete();
            return response()->json(['success' => $deletedCount . ' data berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data terpilih.'], 500);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:10240', // 10MB
        ],[
            'excel_file.required' => 'File Excel wajib diunggah.',
            'excel_file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'excel_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $import = new StandardBudgetsImport();
        try {
            Excel::import($import, $request->file('excel_file'));

            $failureCount = count($import->failures());
            $importedRows = 0; // Placeholder

            if ($failureCount > 0) {
                foreach($import->failures() as $failure) {
                    Log::debug("[CONTROLLER IMPORT FAILURE] Excel Row: {$failure->row()}, Attribute: {$failure->attribute()}, Errors: " . implode(', ', $failure->errors()) . ", Values: " . json_encode($failure->values()));
                }
                $message = 'Beberapa data mungkin berhasil diimport, namun terdapat ' . $failureCount . ' baris yang gagal.';
                return redirect()->route('standard-budgets.index')
                                 ->with('warning', $message)
                                 ->with('failures', $import->failures());
            }
            return redirect()->route('standard-budgets.index')->with('success', 'Semua data Standard Budget berhasil diimport.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            foreach($failures as $failure) {
                Log::error("[CONTROLLER VALIDATION_EXCEPTION] Excel Row: {$failure->row()}, Attribute: {$failure->attribute()}, Errors: " . implode(', ', $failure->errors()) . ", Values: " . json_encode($failure->values()));
            }
            return redirect()->route('standard-budgets.index')
                             ->with('error', 'Gagal mengimport data. Terdapat kesalahan validasi pada file Excel.')
                             ->with('failures', $failures);
        } catch (\Exception $e) {
            Log::error('Excel Import General Error in Controller: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->route('standard-budgets.index')
                             ->with('error', 'Terjadi kesalahan sistem saat mengimport file: ' . $e->getMessage());
        }
    }

    public function downloadSample()
    {
        $filePath = public_path('templates/standard_budget_sample.xlsx');
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File template contoh (standard_budget_sample.xlsx) tidak ditemukan di folder public/templates. Harap buat file tersebut.');
        }
        return response()->download($filePath);
    }
}