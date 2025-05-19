<?php

namespace App\Http\Controllers;

use App\Models\StandardBudget;
use Illuminate\Http\Request;
use DataTables;
use App\Imports\StandardBudgetsImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StandardBudgetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StandardBudget::query();

            if ($request->filled('year') && $request->year != '') {
                $query->where('year', $request->year);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $row->id . '">Edit</button> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '">Delete</button>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $years = StandardBudget::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        // Path view sudah benar jika file ada di resources/views/page/standard_budgets/index.blade.php
        return view('page.standard_budgets.index', compact('years'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_region' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'year' => 'required|integer|digits:4|min:1990|max:' . (date('Y') + 10),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        StandardBudget::create($request->all());
        return response()->json(['success' => 'Standard Budget berhasil ditambahkan.']);
    }

    public function edit(StandardBudget $standardBudget)
    {
        if (request()->ajax()) {
            return response()->json($standardBudget);
        }
        abort(404);
    }

    public function update(Request $request, StandardBudget $standardBudget)
    {
        $validator = Validator::make($request->all(), [
            'name_region' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'year' => 'required|integer|digits:4|min:1990|max:' . (date('Y') + 10),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $standardBudget->update($request->all());
        return response()->json(['success' => 'Standard Budget berhasil diperbarui.']);
    }

    public function destroy(StandardBudget $standardBudget)
    {
        try {
            $standardBudget->delete();
            return response()->json(['success' => 'Standard Budget berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus data. Error: ' . $e->getMessage()], 500);
        }
    }

    // public function showImportForm() // HAPUS METHOD INI
    // {
    //     // return view('standard_budgets.import_form'); // Tidak digunakan lagi
    // }

public function importExcel(Request $request){
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('excel_file');

        try {
            // Log import attempt
            Log::info('Starting Excel import for standard budgets');
            
            // Create import instance
            $import = new StandardBudgetsImport();
            
            // Import the file with explicit configuration
            Excel::import($import, $file);

            $failures = $import->failures();

            if (count($failures) > 0) {
                // Log failures
                Log::warning('Some rows failed during import: ' . count($failures) . ' failure(s)');
                
                return redirect()->route('standard-budgets.index')
                                 ->with('warning', 'Beberapa data berhasil diimport, namun ada beberapa yang gagal.')
                                 ->with('failures', $failures);
            }

            Log::info('Excel import completed successfully');
            return redirect()->route('standard-budgets.index')
                             ->with('success', 'Data Standard Budget berhasil diimport dari Excel.');

        } catch (ValidationException $e) {
            $failures = $e->failures();
            Log::error('Excel validation exception: ' . $e->getMessage());
            
            return redirect()->route('standard-budgets.index')
                             ->with('error', 'Gagal mengimport data. Ada kesalahan validasi pada file Excel.')
                             ->with('failures', $failures);
        } catch (\Exception $e) {
            Log::error('Excel import error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->route('standard-budgets.index')
                             ->with('error', 'Terjadi kesalahan saat mengimport file: ' . $e->getMessage());
        }
    }
}