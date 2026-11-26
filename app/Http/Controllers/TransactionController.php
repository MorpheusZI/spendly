<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->transactions()->with('category');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->input('end_date'));
        }

        $transactions = $query
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $categories = Category::where(function ($query) use ($user) {
            $query->whereNull('user_id')
                ->orWhere('user_id', $user->id);
        })
            ->orderBy('name')
            ->get();

        return view('transactions.index', compact(
            'transactions',
            'categories'
        ));
    }

    public function create()
    {
        return view('transactions.form', [
            'transaction' => new Transaction,
            'categories' => $this->availableCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTransaction($request);

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store('receipts/' . auth()->id(), 'local');
        }

        $validated['receipt_image_path'] = $receiptPath;

        auth()->user()->transactions()->create($validated);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);

        return view('transactions.form', [
            'transaction' => $transaction,
            'categories' => $this->availableCategories(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);

        $validated = $this->validateTransaction($request);

        if ($request->hasFile('receipt')) {
            $oldReceipt = $transaction->receipt_image_path;

            $newReceipt = $request->file('receipt')
                ->store('receipts/' . auth()->id(), 'local');

            $validated['receipt_image_path'] = $newReceipt;

            if ($oldReceipt) {
                Storage::disk('local')->delete($oldReceipt);
            }
        }

        $transaction->update($validated);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);

        if ($transaction->receipt_image_path) {
            Storage::disk('local')->delete(
                $transaction->receipt_image_path
            );
        }

        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function receipt(string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);

        abort_unless($transaction->receipt_image_path, 404);

        $path = $transaction->receipt_image_path;

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(
            Storage::disk('local')->path($path)
        );
    }

    private function getFilteredExportData(Request $request)
    {
        $user = Auth::user();
        $query = $user->transactions()->with('category')->latest('date');

        $periodeText = 'Semua Waktu';

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $periodeText = \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($request->end_date)->format('d/m/Y');
        } elseif ($request->filled('start_date')) {
            $periodeText = 'Mulai ' . \Carbon\Carbon::parse($request->start_date)->format('d/m/Y');
        } elseif ($request->filled('end_date')) {
            $periodeText = 'Sampai ' . \Carbon\Carbon::parse($request->end_date)->format('d/m/Y');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return [
            'user' => $user,
            'transactions' => $query->get(),
            'periode' => $periodeText,
        ];
    }
    
    public function exportPdf(Request $request)
    {
        $data = $this->getFilteredExportData($request);
        $user = $data['user'];
        $transactions = $data['transactions'];

        $pdf = new \FPDF();
        $pdf->AddPage();

        // Judul Laporan
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'Laporan Rekap Transaksi Spendly', 0, 1, 'C');
        $pdf->Ln(3);

        // Informasi Singkat User & Periode
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 6, 'Nama Pengguna', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, $user->name . ' (' . $user->email . ')', 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 6, 'Periode Rekap', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, $data['periode'], 0, 1);

        $pdf->Cell(30, 6, 'Tanggal Cetak', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, now()->format('d/m/Y H:i'), 0, 1);
        $pdf->Ln(4);

        // Header Tabel
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(28, 9, 'Tanggal', 1, 0, 'C', true);
        $pdf->Cell(42, 9, 'Kategori', 1, 0, 'L', true);
        $pdf->Cell(30, 9, 'Tipe', 1, 0, 'C', true);
        $pdf->Cell(40, 9, 'Nominal (Rp)', 1, 0, 'R', true);
        $pdf->Cell(50, 9, 'Catatan', 1, 1, 'L', true);

        // Isi Tabel & Hitung Total
        $pdf->SetFont('Arial', '', 10);
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($transactions as $trx) {
            $tanggal = $trx->date ? $trx->date->format('d/m/Y') : $trx->created_at->format('d/m/Y');
            $tipe = $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
            $catatan = mb_strimwidth($trx->description ?? '-', 0, 26, '...');

            if ($trx->type === 'income') {
                $totalIncome += (float) $trx->amount;
            } else {
                $totalExpense += (float) $trx->amount;
            }

            $pdf->Cell(28, 8, $tanggal, 1, 0, 'C');
            $pdf->Cell(42, 8, $trx->category->name ?? '-', 1, 0, 'L');
            $pdf->Cell(30, 8, $tipe, 1, 0, 'C');
            $pdf->Cell(40, 8, number_format((float) $trx->amount, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell(50, 8, $catatan, 1, 1, 'L');
        }

        // Ringkasan Total di Bawah Tabel
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 7, 'Total Pemasukan', 1, 0, 'L', true);
        $pdf->Cell(90, 7, 'Rp ' . number_format($totalIncome, 0, ',', '.'), 1, 1, 'R');

        $pdf->Cell(100, 7, 'Total Pengeluaran', 1, 0, 'L', true);
        $pdf->Cell(90, 7, 'Rp ' . number_format($totalExpense, 0, ',', '.'), 1, 1, 'R');

        $pdf->Cell(100, 7, 'Selisih / Saldo Periode', 1, 0, 'L', true);
        $pdf->Cell(90, 7, 'Rp ' . number_format($totalIncome - $totalExpense, 0, ',', '.'), 1, 1, 'R');

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="laporan-transaksi.pdf"');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getFilteredExportData($request);
        $user = $data['user'];
        $transactions = $data['transactions'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Informasi User & Periode
        $sheet->setCellValue('A1', 'LAPORAN REKAP TRANSAKSI SPENDLY');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet->setCellValue('A2', 'Nama Pengguna');
        $sheet->setCellValue('B2', ': ' . $user->name . ' (' . $user->email . ')');
        $sheet->setCellValue('A3', 'Periode Rekap');
        $sheet->setCellValue('B3', ': ' . $data['periode']);
        $sheet->setCellValue('A4', 'Tanggal Cetak');
        $sheet->setCellValue('B4', ': ' . now()->format('d/m/Y H:i'));

        // Header Tabel (mulai baris 6)
        $sheet->setCellValue('A6', 'Tanggal');
        $sheet->setCellValue('B6', 'Kategori');
        $sheet->setCellValue('C6', 'Tipe');
        $sheet->setCellValue('D6', 'Nominal');
        $sheet->setCellValue('E6', 'Catatan');
        $sheet->getStyle('A6:E6')->getFont()->setBold(true);

        // Isi Data
        $row = 7;
        foreach ($transactions as $trx) {
            $sheet->setCellValue('A' . $row, $trx->date ? $trx->date->format('d/m/Y') : $trx->created_at->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $trx->category->name ?? '-');
            $sheet->setCellValue('C' . $row, $trx->type === 'income' ? 'Pemasukan' : 'Pengeluaran');
            $sheet->setCellValue('D' . $row, (float) $trx->amount);
            $sheet->setCellValue('E' . $row, $trx->description ?? '-');
            $row++;
        }

        // Auto-size lebar kolom agar tidak terpotong di Excel
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'laporan-transaksi.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function validateTransaction(Request $request): array
    {
        return $request->validate([
            'type' => [
                'required',
                Rule::in(['income', 'expense']),
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(function ($query) use ($request) {
                        $query->where('type', $request->input('type'))
                            ->where(function ($query) {
                                $query->whereNull('user_id')
                                    ->orWhere('user_id', auth()->id());
                            });
                    }),
            ],

            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:9999999999999.99',
            ],

            'date' => [
                'required',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'receipt' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'type.in' => 'Jenis transaksi tidak valid.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.decimal' => 'Nominal maksimal memiliki 2 angka desimal.',
            'amount.min' => 'Nominal harus lebih dari 0.',
            'amount.max' => 'Nominal terlalu besar.',
            'date.required' => 'Tanggal transaksi wajib diisi.',
            'date.date' => 'Tanggal transaksi tidak valid.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'receipt.image' => 'File harus berupa gambar.',
            'receipt.mimes' => 'Struk harus berformat JPG, PNG, atau WEBP.',
            'receipt.max' => 'Ukuran struk maksimal 5 MB.',
        ]);
    }

    private function availableCategories()
    {
        return Category::where(function ($query) {
            $query->whereNull('user_id')
                ->orWhere('user_id', auth()->id());
        })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }
}
