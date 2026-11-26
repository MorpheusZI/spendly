<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function download(Request $request, ReportService $reportService)
    {
        $validated = $request->validate([
            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ], [
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.date' => 'Tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $pdfContent = $reportService->generate(
            Auth::user(),
            $validated['start_date'],
            $validated['end_date']
        );

        $filename = sprintf(
            'spendly-report-%s-%s.pdf',
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->streamDownload(
            fn () => print($pdfContent),
            $filename,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}
