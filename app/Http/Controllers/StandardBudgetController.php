<?php

namespace App\Http\Controllers;

use App\Models\StandardBudget;
use Illuminate\Http\Request;
use DataTables;
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

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2, ',', '.');
                })
                ->editColumn('month', function ($row) {
                    return Carbon::create()->month($row->month)->translatedFormat('M');
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button type="button" class="btn btn-sm btn-primary edit-btn" data-id="' . $row->id . '">Edit</button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-danger delete-btn ms-1" data-id="' . $row->id . '">Hapus</button>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $years = StandardBudget::distinct()->orderBy('year', 'desc')->pluck('year');
        $currentYear = date('Y');
        if (!$years->contains($currentYear)) {
            $yearsCollection = collect($years->all());
            $yearsCollection->prepend($currentYear);
            $years = $yearsCollection->sortDesc()->values();
        }


        return view('page.standard_budgets.index', compact('years'));
    }

    public function store(Request $request)
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
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|digits:4|min:1990|max:' . (date('Y') + 10),
        ], [
            'brand_name.unique' => 'Kombinasi Brand, Region, Bulan, dan Tahun ini sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        StandardBudget::create($request->all());
        return response()->json(['success' => 'Data Standard Budget berhasil ditambahkan.']);
    }

    public function edit(StandardBudget $standardBudget)
    {
        return response()->json($standardBudget);
    }

    public function update(Request $request, StandardBudget $standardBudget)
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
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|digits:4|min:1990|max:' . (date('Y') + 10),
        ], [
            'brand_name.unique' => 'Kombinasi Brand, Region, Bulan, dan Tahun ini sudah ada untuk entri lain.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $standardBudget->update($request->all());
        return response()->json(['success' => 'Data Standard Budget berhasil diperbarui.']);
    }

    public function destroy(StandardBudget $standardBudget)
    {
        $standardBudget->delete();
        return response()->json(['success' => 'Data Standard Budget berhasil dihapus.']);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $import = new StandardBudgetsImport();
        try {
            Excel::import($import, $request->file('excel_file'));

            $successMessage = 'Data Standard Budget berhasil diimport.';
            if ($import->failures()->isNotEmpty() && count($import->failures()) > 0) {
                return redirect()->route('standard-budgets.index')
                                 ->with('warning', 'Beberapa data berhasil diimport, namun ada beberapa baris yang gagal.')
                                 ->with('failures', $import->failures());
            }
            return redirect()->route('standard-budgets.index')->with('success', $successMessage);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->route('standard-budgets.index')
                             ->with('error', 'Gagal mengimport data. Terdapat kesalahan validasi.')
                             ->with('failures', $failures);
        } catch (\Exception $e) {
            Log::error('Excel Import Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->route('standard-budgets.index')
                             ->with('error', 'Terjadi kesalahan saat mengimport file: ' . $e->getMessage());
        }
    }

    public function downloadSample()
    {
        $filePath = public_path('templates/standard_budget_sample.xlsx');
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File template contoh (standard_budget_sample.xlsx) tidak ditemukan di folder public/templates.');
        }
        return response()->download($filePath);
    }
}