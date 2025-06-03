<?php

namespace App\Http\Controllers;

use App\Models\StandardBudget;
use App\Imports\StandardBudgetsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Ensure this is present
use Illuminate\Support\Facades\Log; // For logging errors
use PhpOffice\PhpSpreadsheet\Spreadsheet; // For downloadSample
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;  // For downloadSample
use PhpOffice\PhpSpreadsheet\Style\Alignment; // For downloadSample
use PhpOffice\PhpSpreadsheet\Style\Fill; // For downloadSample


class StandardBudgetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StandardBudget::query();

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('amount', function ($row) {
                    // Using number_format as per your existing code for Indonesian locale
                    return number_format($row->amount, 2, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<button data-id="' . $row->id . '" class="edit-btn p-1 text-blue-600 hover:text-blue-800" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                    </svg>
                                </button>';
                    $deleteBtn = '<button data-id="' . $row->id . '" class="delete-btn p-1 text-red-600 hover:text-red-800" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>';
                    return '<div class="flex items-center justify-center space-x-1">' . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['action', 'amount'])
                ->make(true);
        }

        $years = StandardBudget::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        // Ensure your view path is correct, e.g., 'admin.standard-budgets.index' or just 'standard-budgets.index'
        return view('page.standard_budgets.index', compact('years'));
    }

    public function store(Request $request)
    {
        $isUpdate = (bool)$request->id;

        $rules = [
            'amount'      => 'required|numeric|min:0',
            'year'        => [
                'required',
                'integer',
                'digits:4',
                'min:1990',
                'max:' . (date('Y') + 10)
            ],
            'name_region' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standard_budgets')->where(function ($query) use ($request) {
                    return $query->where('name_region', trim($request->name_region)) // Ensure trim is used for comparison
                                 ->where('year', (int)$request->year);
                })->when($isUpdate, function ($rule) use ($request) {
                    return $rule->ignore($request->id);
                }),
            ],
        ];

        $messages = [
            'name_region.unique' => 'Kombinasi Name Region dan Tahun sudah ada.',
            'year.max' => 'Tahun maksimal ' . (date('Y') + 10),
            // You can add other custom messages from your importer here if you want consistency
            // or handle them primarily in the importer.
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        StandardBudget::updateOrCreate(
            ['id' => $request->id],
            [
                'name_region' => trim($request->name_region),
                'amount'      => (float)$request->amount,
                'year'        => (int)$request->year,
            ]
        );

        $message = $isUpdate ? 'Standard Budget berhasil diperbarui!' : 'Standard Budget berhasil ditambahkan!';
        return response()->json(['success' => $message]);
    }

    public function edit($id)
    {
        $standardBudget = StandardBudget::findOrFail($id);
        return response()->json($standardBudget);
    }

    public function destroy($id)
    {
        try {
            $standardBudget = StandardBudget::findOrFail($id);
            $standardBudget->delete();
            return response()->json(['success' => 'Standard Budget berhasil dihapus!']);
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violation or other DB errors
            Log::error("Error deleting Standard Budget ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data. Kemungkinan data ini terkait dengan data lain.'], 500);
        } catch (\Exception $e) {
            Log::error("Error deleting Standard Budget ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data.'], 500);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ], [
            'excel_file.required' => 'File Excel wajib diunggah.',
            'excel_file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'excel_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $import = new StandardBudgetsImport();

        try {
            Excel::import($import, $request->file('excel_file'));

            $failures = $import->failures();

            if (count($failures) > 0) {
                return redirect()->route('standard-budgets.index')
                    ->with('failures', $failures)
                    ->with('warning', 'Beberapa data gagal diimport. Silakan periksa detail di bawah.');
            }

            return redirect()->route('standard-budgets.index')
                ->with('success', 'Semua data dari file Excel berhasil diimport.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // This catches exceptions if SkipsOnFailure is not used or if there's a global validation issue.
            $failures = $e->failures();
            Log::warning('Excel Import ValidationException: ', $failures);
            return redirect()->route('standard-budgets.index')
                ->with('failures', $failures)
                ->with('error', 'Terjadi kesalahan validasi saat import. Silakan periksa detail di bawah.');
        } catch (\Exception $e) {
            Log::error('Excel Import General Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->route('standard-budgets.index')
                ->with('error', 'Terjadi kesalahan saat mengimport file: ' . $e->getMessage());
        }
    }

    public function downloadSample()
    {
        $templateDir = public_path('templates');
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        $filePath = $templateDir . '/standard_budget_template.xlsx';

        if (!file_exists($filePath)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header Title
            $sheet->setCellValue('A1', 'Template Import Standard Budget');
            $sheet->mergeCells('A1:C1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Column Headers
            $sheet->setCellValue('A2', 'name_region');
            $sheet->setCellValue('B2', 'amount');
            $sheet->setCellValue('C2', 'year');
            $headerStyle = $sheet->getStyle('A2:C2');
            $headerStyle->getFont()->setBold(true);
            $headerStyle->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFD3D3D3'); // Light Gray

            // Example Data
            $sheet->setCellValue('A3', 'Region A / Indonesia');
            $sheet->setCellValueExplicit('B3', '150000.50', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('B3')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('C3', 2023);

            $sheet->setCellValue('A4', 'Region B / USA');
            $sheet->setCellValueExplicit('B4', '2500000', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('B4')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('C4', 2024);
            
            // Auto-size columns
            foreach (range('A', 'C') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
        }

        return response()->download($filePath, 'standard_budget_template.xlsx')->deleteFileAfterSend(false); // Set to true if you want to delete after download
    }
}