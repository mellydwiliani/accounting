<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalController extends Controller
{
    public function index()
    {
        $accounts = DB::table('chart_of_accounts')
            ->where('status', true)
            ->orderBy('code')
            ->get();

        $journals = DB::table('journal_entries')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $details = DB::table('transactions')
            ->join('chart_of_accounts', 'transactions.coa_id', '=', 'chart_of_accounts.id')
            ->select(
                'transactions.*',
                'chart_of_accounts.code',
                'chart_of_accounts.name as account_name'
            )
            ->orderBy('transactions.id')
            ->get()
            ->groupBy('journal_entry_id');

        return view('journals.index', compact(
            'accounts',
            'journals',
            'details'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'debit_coa_id' => 'required|array|min:1',
            'debit_coa_id.*' => 'required|exists:chart_of_accounts,id',
            'debit_amount' => 'required|array|min:1',
            'debit_amount.*' => 'required|numeric|gt:0',
            'credit_coa_id' => 'required|array|min:1',
            'credit_coa_id.*' => 'required|exists:chart_of_accounts,id',
            'credit_amount' => 'required|array|min:1',
            'credit_amount.*' => 'required|numeric|gt:0',
        ]);

        $debitTotal = array_sum($request->debit_amount);
        $creditTotal = array_sum($request->credit_amount);

        if (abs($debitTotal - $creditTotal) > 0.01) {
            throw ValidationException::withMessages([
                'balance' => 'Total Debit dan Kredit harus sama.',
            ]);
        }

        DB::transaction(function () use ($request, $debitTotal, $creditTotal) {
            $journalId = DB::table('journal_entries')->insertGetId([
                'journal_number' => 'JU-' . now()->format('YmdHis') . '-' . uniqid(),
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'total_debit' => $debitTotal,
                'total_credit' => $creditTotal,
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->debit_coa_id as $i => $coaId) {
                DB::table('transactions')->insert([
                    'journal_entry_id' => $journalId,
                    'coa_id' => $coaId,
                    'description' => $request->description,
                    'debit' => $request->debit_amount[$i],
                    'credit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($request->credit_coa_id as $i => $coaId) {
                DB::table('transactions')->insert([
                    'journal_entry_id' => $journalId,
                    'coa_id' => $coaId,
                    'description' => $request->description,
                    'debit' => 0,
                    'credit' => $request->credit_amount[$i],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('journals.index')
            ->with('success', 'Jurnal berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        DB::table('journal_entries')
            ->where('id', $id)
            ->delete();

        return redirect()->route('journals.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }
}
