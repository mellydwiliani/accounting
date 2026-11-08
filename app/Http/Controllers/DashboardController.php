<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Total akun aktif
        $totalAccounts = DB::table('chart_of_accounts')->where('status', 1)->count();

        // Total transaksi jurnal bulan ini
        $totalJournalsMonth = DB::table('journal_entries')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->count();

        // Total mutasi perputaran keuangan bulan ini
        $totalTurnoverMonth = DB::table('journal_entries')
            ->whereMonth('transaction_date', $currentMonth)
            ->whereYear('transaction_date', $currentYear)
            ->sum('total_debit');

        // Ringkasan per Kelompok Akun (Aset, Kewajiban, Ekuitas, Pendapatan, Beban)
        $groupStats = DB::table('account_groups')
            ->leftJoin('coa_categories', 'account_groups.id', '=', 'coa_categories.account_group_id')
            ->leftJoin('chart_of_accounts', 'coa_categories.id', '=', 'chart_of_accounts.category_id')
            ->leftJoin('transactions', function ($join) use ($currentMonth, $currentYear) {
                $join->on('chart_of_accounts.id', '=', 'transactions.coa_id')
                     ->whereIn('transactions.journal_entry_id', function ($query) use ($currentMonth, $currentYear) {
                         $query->select('id')
                               ->from('journal_entries')
                               ->whereMonth('transaction_date', $currentMonth)
                               ->whereYear('transaction_date', $currentYear);
                     });
            })
            ->select(
                'account_groups.code as group_code',
                'account_groups.name as group_name',
                DB::raw('COUNT(DISTINCT chart_of_accounts.id) as total_coa'),
                DB::raw('COALESCE(SUM(transactions.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(transactions.credit), 0) as total_credit')
            )
            ->groupBy('account_groups.code', 'account_groups.name')
            ->orderBy('account_groups.code')
            ->get()
            ->map(function ($g) {
                // Tentukan saldo bersih berdasarkan karakter normal akun
                $firstDigit = substr($g->group_code, 0, 1);
                $isDebitNormal = in_array($firstDigit, ['1', '5', '6']); // Aset & Beban normalnya Debit
                $netBalance = $isDebitNormal 
                    ? ($g->total_debit - $g->total_credit) 
                    : ($g->total_credit - $g->total_debit);

                $g->net_balance = $netBalance;
                $g->normal_pos = $isDebitNormal ? 'Debit' : 'Kredit';
                return $g;
            });

        // Hitung Pendapatan & Beban bulan ini
        $monthlyRevenue = 0;
        $monthlyExpense = 0;

        foreach ($groupStats as $g) {
            $isRevenue = str_starts_with($g->group_code, '4') 
                || stripos($g->group_name, 'Pendapatan') !== false 
                || stripos($g->group_name, 'Revenue') !== false;

            $isExpense = str_starts_with($g->group_code, '5')
                || str_starts_with($g->group_code, '6')
                || stripos($g->group_name, 'Beban') !== false 
                || stripos($g->group_name, 'Biaya') !== false;

            if ($isRevenue) {
                $monthlyRevenue += $g->net_balance;
            } elseif ($isExpense) {
                $monthlyExpense += $g->net_balance;
            }
        }

        $monthlyNetIncome = $monthlyRevenue - $monthlyExpense;

        // 5 Jurnal transaksi terbaru
        $recentJournals = DB::table('journal_entries')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Top 5 Pengeluaran Beban Terbesar Bulan Ini
        $topExpenses = DB::table('transactions')
            ->join('journal_entries', 'transactions.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'transactions.coa_id', '=', 'chart_of_accounts.id')
            ->join('coa_categories', 'chart_of_accounts.category_id', '=', 'coa_categories.id')
            ->join('account_groups', 'coa_categories.account_group_id', '=', 'account_groups.id')
            ->whereMonth('journal_entries.transaction_date', $currentMonth)
            ->whereYear('journal_entries.transaction_date', $currentYear)
            ->where(function ($q) {
                $q->where('account_groups.code', 'like', '5%')
                  ->orWhere('account_groups.code', 'like', '6%')
                  ->orWhere('account_groups.name', 'like', '%Beban%')
                  ->orWhere('account_groups.name', 'like', '%Biaya%');
            })
            ->select(
                'chart_of_accounts.code',
                'chart_of_accounts.name',
                DB::raw('(SUM(transactions.debit) - SUM(transactions.credit)) as total_amount')
            )
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name')
            ->having('total_amount', '>', 0)
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Nama bulan Indonesia
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentPeriod = $monthNames[$currentMonth] . ' ' . $currentYear;

        return view('dashboard.index', compact(
            'totalAccounts',
            'totalJournalsMonth',
            'totalTurnoverMonth',
            'monthlyRevenue',
            'monthlyExpense',
            'monthlyNetIncome',
            'groupStats',
            'recentJournals',
            'topExpenses',
            'currentPeriod'
        ));
    }
}
