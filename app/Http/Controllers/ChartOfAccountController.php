<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\CoaCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    
    public function index()
    {
        $groups = \App\Models\AccountGroup::orderBy('id')->get();

        $categories = \App\Models\CoaCategory::with('accountGroup')
            ->orderBy('name')
            ->get();

        $accounts = \App\Models\ChartOfAccount::with([
            'category.accountGroup'
        ])
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.index', compact(
            'groups',
            'categories',
            'accounts'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:coa_categories,id',
            'status' => 'required|boolean',
        ]);

        ChartOfAccount::create($validated);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('chart_of_accounts', 'code')
                    ->ignore($chartOfAccount->id),
            ],
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:coa_categories,id',
            'status' => 'required|boolean',
        ]);

        $chartOfAccount->update($validated);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}

