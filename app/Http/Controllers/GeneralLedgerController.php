<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeneralLedgerController extends Controller
{
    public function index(Request $request)
    {
        // Ambil daftar tahun yang benar-benar memiliki jurnal
        $years = DB::table('journal_entries')
            ->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Jika belum ada jurnal, gunakan tahun sekarang
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        // Default menggunakan tahun transaksi terbaru
        $year = (int) $request->input('year', $years->first());

        // Ambil akun aktif
        $accounts = DB::table('chart_of_accounts')
            ->where('status', 1)
            ->orderBy('code')
            ->get();

        // Ambil seluruh transaksi jurnal pada tahun terpilih
        $transactions = DB::table('transactions')
            ->join(
                'journal_entries',
                'transactions.journal_entry_id',
                '=',
                'journal_entries.id'
            )
            ->join(
                'chart_of_accounts',
                'transactions.coa_id',
                '=',
                'chart_of_accounts.id'
            )
            ->whereYear('journal_entries.transaction_date', $year)
            ->select(
                'transactions.*',
                'journal_entries.transaction_date',
                'journal_entries.journal_number',
                'journal_entries.description as journal_description',
                'chart_of_accounts.code',
                'chart_of_accounts.name as account_name'
            )
            ->orderBy('journal_entries.transaction_date')
            ->orderBy('journal_entries.id')
            ->orderBy('transactions.id')
            ->get();

        // Hitung saldo seluruh akun sebelum tahun terpilih
        $openingBalances = DB::table('transactions')
            ->join(
                'journal_entries',
                'transactions.journal_entry_id',
                '=',
                'journal_entries.id'
            )
            ->whereDate(
                'journal_entries.transaction_date',
                '<',
                $year . '-01-01'
            )
            ->select(
                'transactions.coa_id',
                DB::raw('SUM(transactions.debit) as debit'),
                DB::raw('SUM(transactions.credit) as credit')
            )
            ->groupBy('transactions.coa_id')
            ->get()
            ->keyBy('coa_id');

        // Saldo berjalan setiap akun
        $runningBalances = [];

        foreach ($openingBalances as $coaId => $balance) {
            $runningBalances[$coaId] =
                (float) $balance->debit - (float) $balance->credit;
        }

        $monthlyLedger = [];

        // Kelompokkan transaksi berdasarkan bulan dan akun
        foreach ($transactions as $transaction) {

            $monthKey = Carbon::parse(
                $transaction->transaction_date
            )->format('Y-m');

            $coaId = $transaction->coa_id;

            // Inisialisasi akun pada bulan tersebut
            if (!isset($monthlyLedger[$monthKey][$coaId])) {

                $openingBalance = $runningBalances[$coaId] ?? 0;

                $monthlyLedger[$monthKey][$coaId] = [
                    'account_code' => $transaction->code,
                    'account_name' => $transaction->account_name,
                    'opening_balance' => $openingBalance,
                    'debit' => 0,
                    'credit' => 0,
                    'closing_balance' => $openingBalance,
                    'transactions' => [],
                ];
            }

            $account = &$monthlyLedger[$monthKey][$coaId];

            $debit = (float) $transaction->debit;
            $credit = (float) $transaction->credit;

            // Simpan transaksi
            $account['transactions'][] = $transaction;

            // Hitung total mutasi
            $account['debit'] += $debit;
            $account['credit'] += $credit;

            // Update saldo berjalan
            $runningBalances[$coaId] =
                ($runningBalances[$coaId] ?? 0)
                + $debit
                - $credit;

            $account['closing_balance'] =
                $runningBalances[$coaId];

            unset($account);
        }

        return view('ledger.index', compact(
            'year',
            'years',
            'accounts',
            'monthlyLedger'
        ));
    }
}