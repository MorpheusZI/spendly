<?php

namespace App\Http\Controllers;

use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelController extends Controller
{
    public function export(ExcelService $excelService)
    {
        $spreadsheet = $excelService->export(auth()->user());

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            'Spendly-Transaksi-'.now()->format('Y-m-d').'.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function template(ExcelService $excelService)
    {
        $spreadsheet = $excelService->template(auth()->user());
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            'Spendly-Template-Import.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function import(Request $request, ExcelService $excelService)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx',
                'max:10240',
            ],
        ], [
            'file.required' => 'Silakan pilih file Excel.',
            'file.mimes' => 'File harus berformat .xlsx.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        Log::info('Spendly import request received', [
            'user_id' => auth()->id(),
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);

        try {
            $result = $excelService->import(
                auth()->user(),
                $request->file('file')->getRealPath()
            );

            Log::info('Spendly import result', [
                'user_id' => auth()->id(),
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Spendly Excel import failed', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('transactions.index')
                ->with('error', 'Import gagal memproses file Excel.');
        }

        return redirect()
            ->route('transactions.index')
            ->with('excel_import_result', $result);
    }
}
