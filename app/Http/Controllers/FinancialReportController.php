<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialReportController extends Controller
{
    private $monthNames = [
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

    public function index(Request $request)
    {
        $latestDate = DB::table('journal_entries')->max('transaction_date');
        $defaultDate = $latestDate ? Carbon::parse($latestDate) : now();

        $years = DB::table('journal_entries')
            ->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        $month = (int) $request->input('month', $defaultDate->month);
        $year = (int) $request->input('year', $defaultDate->year);

        $request->validate([
            'month' => 'sometimes|integer|min:1|max:12',
            'year' => 'sometimes|integer|min:1900|max:2100',
        ]);

        $reportData = $this->calculateReportData($year, $month);
        $period = $this->monthNames[$month] . ' ' . $year;

        return view('reports.index', array_merge($reportData, [
            'year' => $year,
            'month' => $month,
            'years' => $years,
            'period' => $period,
        ]));
    }

    /**
     * Hitung pendapatan, beban, dan laba rugi untuk periode tertentu
     */
    private function calculateReportData(int $year, ?int $month = null): array
    {
        if ($month) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
        } else {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
        }

        $transactions = DB::table('transactions')
            ->join('journal_entries', 'transactions.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'transactions.coa_id', '=', 'chart_of_accounts.id')
            ->join('coa_categories', 'chart_of_accounts.category_id', '=', 'coa_categories.id')
            ->join('account_groups', 'coa_categories.account_group_id', '=', 'account_groups.id')
            ->whereBetween('journal_entries.transaction_date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('account_groups.code', 'like', '4%')
                    ->orWhere('account_groups.name', 'like', '%Pendapatan%')
                    ->orWhere('account_groups.name', 'like', '%Revenue%')
                    ->orWhere('account_groups.name', 'like', '%Income%')
                    ->orWhere('account_groups.code', 'like', '5%')
                    ->orWhere('account_groups.code', 'like', '6%')
                    ->orWhere('account_groups.name', 'like', '%Beban%')
                    ->orWhere('account_groups.name', 'like', '%Biaya%')
                    ->orWhere('account_groups.name', 'like', '%Expense%');
            })
            ->select(
                'chart_of_accounts.id as coa_id',
                'chart_of_accounts.code as account_code',
                'chart_of_accounts.name as account_name',
                'coa_categories.name as category_name',
                'account_groups.code as group_code',
                'account_groups.name as group_name',
                DB::raw('SUM(transactions.debit) as total_debit'),
                DB::raw('SUM(transactions.credit) as total_credit')
            )
            ->groupBy(
                'chart_of_accounts.id',
                'chart_of_accounts.code',
                'chart_of_accounts.name',
                'coa_categories.name',
                'account_groups.code',
                'account_groups.name'
            )
            ->orderBy('chart_of_accounts.code')
            ->get();

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($transactions as $item) {
            $isRevenue = str_starts_with($item->group_code, '4') 
                || stripos($item->group_name, 'Pendapatan') !== false 
                || stripos($item->group_name, 'Revenue') !== false 
                || stripos($item->group_name, 'Income') !== false;

            if ($isRevenue) {
                $amount = (float) $item->total_credit - (float) $item->total_debit;
                if ($amount != 0) {
                    $revenues[] = [
                        'code' => $item->account_code,
                        'name' => $item->account_name,
                        'category' => $item->category_name,
                        'amount' => $amount,
                    ];
                    $totalRevenue += $amount;
                }
            } else {
                $amount = (float) $item->total_debit - (float) $item->total_credit;
                if ($amount != 0) {
                    $expenses[] = [
                        'code' => $item->account_code,
                        'name' => $item->account_name,
                        'category' => $item->category_name,
                        'amount' => $amount,
                    ];
                    $totalExpense += $amount;
                }
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netIncome' => $netIncome,
        ];
    }

    /**
     * Export Excel Laba Rugi
     * mode = 'single' (1 bulan yang dipilih)
     * mode = 'all' (semua bulan per sheet + sheet rangkuman tahunan)
     */
    public function exportExcel(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $mode = $request->input('mode', 'single');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Hapus sheet default kosong

        if ($mode === 'all') {
            // Sheet 1: Rangkuman Tahunan
            $annualSheet = $spreadsheet->createSheet();
            $annualSheet->setTitle("Tahunan $year");
            $annualData = $this->calculateReportData($year, null);
            $this->buildIncomeStatementSheet($annualSheet, "Semua Bulan (Tahun $year)", $annualData);

            // Sheet 2 dst: Setiap Bulan (1 - 12) yang memiliki transaksi atau per bulan
            for ($m = 1; $m <= 12; $m++) {
                $monthName = $this->monthNames[$m];
                $data = $this->calculateReportData($year, $m);
                
                // Tambahkan sheet per bulan
                $sheet = $spreadsheet->createSheet();
                $sheetTitle = substr($monthName, 0, 15); // Sheet name max 31 chars
                $sheet->setTitle($sheetTitle);
                $this->buildIncomeStatementSheet($sheet, "$monthName $year", $data);
            }

            $filename = "Laporan_Laba_Rugi_Semua_Bulan_{$year}.xlsx";
        } else {
            // Single Month
            $periodName = $this->monthNames[$month] . " " . $year;
            $data = $this->calculateReportData($year, $month);

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(substr($this->monthNames[$month], 0, 20));
            $this->buildIncomeStatementSheet($sheet, $periodName, $data);

            $filename = "Laporan_Laba_Rugi_{$this->monthNames[$month]}_{$year}.xlsx";
        }

        // Set active sheet ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        // Download Response (simpan ke file temporary agar stream bersih dari buffer)
        $tempFile = tempnam(sys_get_temp_dir(), 'lr_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        // Bersihkan output buffer agar tidak ada karakter tambahan
        // sebelum isi file Excel.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Membangun format tabel Laba Rugi pada Worksheet
     */
    private function buildIncomeStatementSheet($sheet, string $periodLabel, array $data)
    {
        $sheet->setShowGridLines(true);
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(24);

        // Header Perusahaan & Judul
        $sheet->setCellValue('A1', 'SISTEM AKUNTANSI');
        $sheet->setCellValue('A2', 'LAPORAN LABA RUGI / INCOME STATEMENT');
        $sheet->setCellValue('A3', 'Periode: ' . $periodLabel);

        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('334155'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $r = 5;

        // Table Header
        $sheet->setCellValue("A$r", 'Kode Akun');
        $sheet->setCellValue("B$r", 'Nama Akun');
        $sheet->setCellValue("C$r", 'Kategori');
        $sheet->setCellValue("D$r", 'Jumlah (Rp)');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B']
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A$r:D$r")->applyFromArray($headerStyle);
        $sheet->getStyle("D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($r)->setRowHeight(24);
        $r++;

        $numFmt = '#,##0;(#,##0);"-"';

        // ── PENDAPATAN ──
        $sheet->setCellValue("A$r", 'PENDAPATAN');
        $sheet->mergeCells("A$r:D$r");
        $sheet->getStyle("A$r:D$r")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(20);
        $r++;

        if (empty($data['revenues'])) {
            $sheet->setCellValue("A$r", '- Tidak ada pendapatan pada periode ini -');
            $sheet->mergeCells("A$r:D$r");
            $sheet->getStyle("A$r:D$r")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('94A3B8'));
            $sheet->getStyle("A$r:D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        } else {
            foreach ($data['revenues'] as $rev) {
                $sheet->setCellValue("A$r", $rev['code']);
                $sheet->setCellValue("B$r", $rev['name']);
                $sheet->setCellValue("C$r", $rev['category']);
                $sheet->setCellValue("D$r", $rev['amount']);

                $sheet->getStyle("D$r")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A$r:D$r")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
                $sheet->getRowDimension($r)->setRowHeight(18);
                $r++;
            }
        }

        // Subtotal Pendapatan
        $sheet->setCellValue("A$r", 'TOTAL PENDAPATAN');
        $sheet->mergeCells("A$r:C$r");
        $sheet->setCellValue("D$r", $data['totalRevenue']);
        $sheet->getStyle("A$r:D$r")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECFDF5']],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '10B981']],
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '10B981']],
            ]
        ]);
        $sheet->getStyle("A$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D$r")->getNumberFormat()->setFormatCode($numFmt);
        $sheet->getStyle("D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($r)->setRowHeight(22);
        $r += 2;

        // ── BEBAN ──
        $sheet->setCellValue("A$r", 'BEBAN USAHA / PENGELUARAN');
        $sheet->mergeCells("A$r:D$r");
        $sheet->getStyle("A$r:D$r")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(20);
        $r++;

        if (empty($data['expenses'])) {
            $sheet->setCellValue("A$r", '- Tidak ada beban pada periode ini -');
            $sheet->mergeCells("A$r:D$r");
            $sheet->getStyle("A$r:D$r")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('94A3B8'));
            $sheet->getStyle("A$r:D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        } else {
            foreach ($data['expenses'] as $exp) {
                $sheet->setCellValue("A$r", $exp['code']);
                $sheet->setCellValue("B$r", $exp['name']);
                $sheet->setCellValue("C$r", $exp['category']);
                $sheet->setCellValue("D$r", $exp['amount']);

                $sheet->getStyle("D$r")->getNumberFormat()->setFormatCode($numFmt);
                $sheet->getStyle("D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A$r:D$r")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
                $sheet->getRowDimension($r)->setRowHeight(18);
                $r++;
            }
        }

        // Subtotal Beban
        $sheet->setCellValue("A$r", 'TOTAL BEBAN');
        $sheet->mergeCells("A$r:C$r");
        $sheet->setCellValue("D$r", $data['totalExpense']);
        $sheet->getStyle("A$r:D$r")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '991B1B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'EF4444']],
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'EF4444']],
            ]
        ]);
        $sheet->getStyle("A$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D$r")->getNumberFormat()->setFormatCode($numFmt);
        $sheet->getStyle("D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($r)->setRowHeight(22);
        $r += 2;

        // ── LABA / RUGI BERSIH ──
        $isProfit = $data['netIncome'] >= 0;
        $labelLaba = $isProfit ? 'LABA BERSIH (SURPLUS)' : 'RUGI BERSIH (DEFISIT)';

        $sheet->setCellValue("A$r", $labelLaba);
        $sheet->mergeCells("A$r:C$r");
        $sheet->setCellValue("D$r", $data['netIncome']);
        $sheet->getStyle("A$r:D$r")->applyFromArray([
            'font' => [
                'bold' => true, 
                'size' => 11,
                'color' => ['rgb' => $isProfit ? '065F46' : '991B1B']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => $isProfit ? 'D1FAE5' : 'FEE2E2']
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F172A']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '0F172A']],
            ]
        ]);
        $sheet->getStyle("A$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D$r")->getNumberFormat()->setFormatCode($numFmt);
        $sheet->getStyle("D$r")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($r)->setRowHeight(26);
    }
}
