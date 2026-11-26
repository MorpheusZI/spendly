<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use App\Models\Category;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ExcelService
{
    private array $headers = [
        'Tanggal',
        'Jenis',
        'Kategori',
        'Jumlah',
        'Deskripsi',
    ];

    /**
     * Export a user's transactions.
     */
    public function export(User $user): Spreadsheet
    {
        $transactions = $user->transactions()
            ->with('category')
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Transaksi');

        $this->setHeaders($sheet);

        $row = 2;

        foreach ($transactions as $transaction) {
            $sheet->setCellValueExplicit(
                "A{$row}",
                Carbon::parse($transaction->date)->format('Y-m-d'),
                DataType::TYPE_STRING
            );

            $sheet->setCellValue("B{$row}", $transaction->type);
            $sheet->setCellValue(
                "C{$row}",
                $transaction->category->name ?? '-'
            );
            $sheet->setCellValue("D{$row}", $transaction->amount);
            $sheet->setCellValue(
                "E{$row}",
                $transaction->description ?? ''
            );

            $row++;
        }

        $this->formatSheet($sheet, $row - 1);

        return $spreadsheet;
    }


    /**
     * Generate a blank import template.
     */
    public function template(User $user): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Template Transaksi');

        $this->setHeaders($sheet);

        $sheet->setCellValue('A2', '2026-11-25');
        $sheet->setCellValue('B2', 'expense');
        $sheet->setCellValue('C2', 'Makan & Minum');
        $sheet->setCellValue('D2', 25000);
        $sheet->setCellValue('E2', 'Makan siang');

        $this->formatSheet($sheet, 2);

        return $spreadsheet;
    }

    /**
     * Validate and import transaction rows.
     */
    public function import(User $user, string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            throw new \RuntimeException(
                'File Excel tidak memiliki data transaksi.'
            );
        }
// Build a category lookup including global and user categories.
        $categories = Category::query()
            ->where(function ($query) use ($user) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->get()
            ->keyBy(function ($category) {
                return strtolower(
                    trim($category->type) . '|' . trim($category->name)
                );
        });
        $validated = [];
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber === 1) {
                continue;
            }

            // Skip entirely empty rows.
            if (
                empty($row['A']) &&
                empty($row['B']) &&
                empty($row['C']) &&
                empty($row['D']) &&
                empty($row['E'])
            ) {
                continue;
            }

            $date = trim((string) ($row['A'] ?? ''));
            $type = strtolower(trim((string) ($row['B'] ?? '')));
            $categoryName = trim((string) ($row['C'] ?? ''));

            // Normalize transaction type.
            $type = match ($type) {
                'pemasukan' => 'income',
                'pengeluaran' => 'expense',
                default => $type,
            };
            $amount = $row['D'] ?? null;
            $description = trim((string) ($row['E'] ?? ''));

            $rowErrors = [];

            // Validate date.
            try {
                if ($date === '') {
                    throw new \Exception();
                }

                if (is_numeric($date)) {
                    $parsedDate = Carbon::createFromDate(
                        1899,
                        12,
                        30
                    )->addDays((int) $date);
                } else {
                    $parsedDate = Carbon::parse($date);
                }

                $date = $parsedDate->format('Y-m-d');
            } catch (\Throwable $e) {
                $rowErrors[] = 'Tanggal tidak valid.';
            }

            // Validate transaction type.
            if (!in_array($type, ['income', 'expense'], true)) {
                $rowErrors[] = 'Jenis harus income atau expense.';
            }

            // Validate amount.
            if (
                !is_numeric($amount) ||
                (float) $amount <= 0
            ) {
                $rowErrors[] = 'Jumlah harus berupa angka positif.';
            }

            // Validate category.
            $category = null;

            if (
                in_array($type, ['income', 'expense'], true) &&
                $categoryName !== ''
            ) {
                $categoryKey = strtolower(
                    $type . '|' . $categoryName
                );

                $category = $categories->get($categoryKey);

                if (!$category) {
                    $rowErrors[] =
                        'Kategori tidak ditemukan untuk jenis ' .
                        $type . ': ' . $categoryName;
                }
            } else {
                $rowErrors[] = 'Kategori wajib diisi.';
            }

            if (strlen($description) > 1000) {
                $rowErrors[] =
                    'Deskripsi maksimal 1000 karakter.';
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'messages' => $rowErrors,
                ];

                continue;
            }

            $validated[] = [
                'date' => $date,
                'type' => $type,
                'category_id' => $category->id,
                'amount' => $amount,
                'description' => $description ?: null,
            ];
        }

        if (empty($validated) && !empty($errors)) {
            return [
                'imported' => 0,
                'errors' => $errors,
            ];
        }

        // Avoid partially imported data if a database operation fails.
        \Illuminate\Support\Facades\DB::transaction(
            function () use ($user, $validated) {
                foreach ($validated as $transaction) {
                    $user->transactions()->create($transaction);
                }
            }
        );

        return [
            'imported' => count($validated),
            'errors' => $errors,
        ];
    }

    private function setHeaders(Worksheet $sheet): void
    {
        foreach ($this->headers as $index => $header) {
            $column = chr(65 + $index);
            $sheet->setCellValue($column . '1', $header);
        }
    }

    private function formatSheet(
        Worksheet $sheet,
        int $lastRow
    ): void {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '536DFE',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A1:E{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'E8EAF0',
                        ],
                    ],
                ],
            ]);

        foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
            $sheet->getColumnDimension($column)
                ->setAutoSize(true);
        }

        $sheet->getColumnDimension('E')->setWidth(40);

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:E{$lastRow}");
    }
}

