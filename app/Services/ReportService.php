<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use FPDF;

class ReportService
{
    public function generate(
        User $user,
        string $startDate,
        string $endDate
    ): string {
        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $income = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $expense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $balance = $income - $expense;

        $pdf = new FPDF('L', 'mm', 'A4');

        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(83, 109, 254);
        $pdf->Cell(0, 10, 'Spendly', 0, 1);

        $pdf->SetTextColor(32, 36, 51);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'Laporan Keuangan', 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(119, 127, 145);

        $pdf->Cell(
            0,
            7,
            'Periode Laporan: ' .
            $this->formatDate($startDate) .
            ' - ' .
            $this->formatDate($endDate),
            0,
            1
        );

        $pdf->Cell(
            0,
            7,
            'Tanggal dibuat: ' . now()->format('d/m/Y H:i'),
            0,
            1
        );

        $pdf->Cell(
            0,
            7,
            'Nama: ' . $this->safeText($user->name),
            0,
            1
        );

        $pdf->Cell(
            0,
            7,
            'Email: ' . $this->safeText($user->email),
            0,
            1
        );

        $pdf->Ln(5);

        // Summary
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(32, 36, 51);
        $pdf->Cell(0, 8, 'Ringkasan Keuangan', 0, 1);

        $summaryWidth = 90;

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(240, 243, 255);
        $pdf->SetTextColor(32, 36, 51);

        $pdf->Cell(
            $summaryWidth,
            9,
            'Total Pemasukan',
            1,
            0,
            'L',
            true
        );

        $pdf->Cell(
            $summaryWidth,
            9,
            'Total Pengeluaran',
            1,
            0,
            'L',
            true
        );

        $pdf->Cell(
            $summaryWidth,
            9,
            'Saldo Bersih',
            1,
            1,
            'L',
            true
        );

        $pdf->SetFont('Arial', '', 11);

        $pdf->Cell(
            $summaryWidth,
            10,
            'Rp ' . $this->formatMoney($income),
            1,
            0
        );

        $pdf->Cell(
            $summaryWidth,
            10,
            'Rp ' . $this->formatMoney($expense),
            1,
            0
        );

        $pdf->Cell(
            $summaryWidth,
            10,
            'Rp ' . $this->formatMoney($balance),
            1,
            1
        );

        $pdf->Ln(8);

        // Transaction table
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Rincian Transaksi', 0, 1);

        $widths = [25, 30, 50, 35, 95, 25];

        $headers = [
            'Tanggal',
            'Jenis',
            'Kategori',
            'Jumlah (IDR)',
            'Deskripsi',
            'Bukti',
        ];

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(83, 109, 254);
        $pdf->SetTextColor(255, 255, 255);

        foreach ($headers as $index => $header) {
            $pdf->Cell(
                $widths[$index],
                9,
                $header,
                1,
                0,
                'C',
                true
            );
        }

        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(32, 36, 51);

        foreach ($transactions as $transaction) {
            if ($pdf->GetY() > 180) {
                $pdf->AddPage();

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(83, 109, 254);
                $pdf->SetTextColor(255, 255, 255);

                foreach ($headers as $index => $header) {
                    $pdf->Cell(
                        $widths[$index],
                        9,
                        $header,
                        1,
                        0,
                        'C',
                        true
                    );
                }

                $pdf->Ln();

                $pdf->SetFont('Arial', '', 8);
                $pdf->SetTextColor(32, 36, 51);
            }

            $type = $transaction->type === 'income'
                ? 'Pemasukan'
                : 'Pengeluaran';

            $row = [
                $this->formatDate($transaction->date),
                $type,
                $transaction->category->name ?? '-',
                $this->formatMoney($transaction->amount),
                $transaction->description ?: '-',
                $transaction->receipt_image_path ? 'Tersedia' : '-',
            ];

            foreach ($row as $index => $value) {
                $pdf->Cell(
                    $widths[$index],
                    8,
                    $this->safeText((string) $value),
                    1,
                    0,
                    'L'
                );
            }

            $pdf->Ln();
        }

        if ($transactions->isEmpty()) {
            $pdf->Cell(
                array_sum($widths),
                10,
                'Tidak ada transaksi pada periode ini.',
                1,
                1,
                'C'
            );
        }

        // Footer
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(119, 127, 145);

        $pdf->Cell(
            0,
            6,
            'Dibuat oleh Spendly - Aplikasi Pencatat Keuangan Pribadi',
            0,
            1,
            'C'
        );

        $pdf->Cell(
            0,
            6,
            'Seluruh nominal ditampilkan dalam Rupiah (IDR).',
            0,
            1,
            'C'
        );

        return $pdf->Output('S');
    }

    private function formatMoney($amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }

    private function formatDate($date): string
    {
        $date = Carbon::parse($date);

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->format('d') . ' ' .
            $months[(int) $date->format('m')] . ' ' .
            $date->format('Y');
    }

    private function safeText(string $text): string
    {
        $converted = iconv(
            'UTF-8',
            'Windows-1252//TRANSLIT//IGNORE',
            $text
        );

        return $converted !== false ? $converted : '';
    }
}

