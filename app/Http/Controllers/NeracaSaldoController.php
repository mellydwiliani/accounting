<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NeracaSaldoController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tanggal transaksi terbaru
        $latestDate = DB::table('journal_entries')
            ->max('transaction_date');

        // Jika belum ada transaksi
        $defaultDate = $latestDate
            ? Carbon::parse($latestDate)
            : now();

        // Daftar tahun yang tersedia
        $years = DB::table('journal_entries')
            ->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        // Filter bulan dan tahun
        $month = (int) $request->input(
            'month',
            $defaultDate->month
        );

        $year = (int) $request->input(
            'year',
            $defaultDate->year
        );

        // Validasi filter
        $request->validate([
            'month' => 'sometimes|integer|min:1|max:12',
            'year' => 'sometimes|integer|min:1900|max:2100',
        ]);

        // Batas akhir periode yang dipilih
        $endDate = Carbon::createFromDate(
            $year,
            $month,
            1
        )->endOfMonth()->toDateString();

        // Ambil semua akun aktif
        $accounts = DB::table('chart_of_accounts')
            ->where('status', 1)
            ->orderBy('code')
            ->get();

        // Hitung saldo akun berdasarkan seluruh jurnal
        // sampai akhir bulan yang dipilih
        $balances = DB::table('transactions')
            ->join(
                'journal_entries',
                'transactions.journal_entry_id',
                '=',
                'journal_entries.id'
            )
            ->whereDate(
                'journal_entries.transaction_date',
                '<=',
                $endDate
            )
            ->select(
                'transactions.coa_id',
                DB::raw('SUM(transactions.debit) as total_debit'),
                DB::raw('SUM(transactions.credit) as total_credit')
            )
            ->groupBy('transactions.coa_id')
            ->get()
            ->keyBy('coa_id');

        $neracaSaldo = [];

        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($accounts as $account) {

            $balance = $balances[$account->id] ?? null;

            $debit = (float) ($balance->total_debit ?? 0);
            $credit = (float) ($balance->total_credit ?? 0);

            // Saldo normal berdasarkan selisih debit dan kredit
            $netBalance = $debit - $credit;

            $saldoDebit = max($netBalance, 0);
            $saldoKredit = max(-$netBalance, 0);

            // Tidak menampilkan akun yang belum memiliki saldo
            if ($saldoDebit == 0 && $saldoKredit == 0) {
                continue;
            }

            $neracaSaldo[] = [
                'kode' => $account->code,
                'akun' => $account->name,
                'debit' => $saldoDebit,
                'kredit' => $saldoKredit,
            ];

            $totalDebit += $saldoDebit;
            $totalKredit += $saldoKredit;
        }

        // Status keseimbangan
        $isBalance = abs($totalDebit - $totalKredit) < 0.01;

        // Nama bulan Indonesia
        $monthNames = [
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

        $period = $monthNames[$month] . ' ' . $year;

        return view('neraca-saldo.index', compact(
            'year',
            'month',
            'years',
            'period',
            'neracaSaldo',
            'totalDebit',
            'totalKredit',
            'isBalance'
        ));
    }
}
