<?php

namespace App\Http\Controllers;

use App\Models\AccountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountGroupController extends Controller
{
    // TAMBAH KELOMPOK
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:account_groups,name',
            'code' => 'nullable|string|max:20',
        ]);

        AccountGroup::create($validated);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Kelompok akun berhasil ditambahkan.');
    }

    // EDIT KELOMPOK
    public function update(Request $request, AccountGroup $accountGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:account_groups,name,' . $accountGroup->id,
            'code' => 'nullable|string|max:20',
        ]);

        $accountGroup->update($validated);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Kelompok akun berhasil diperbarui.');
    }

    // HAPUS KELOMPOK
    public function destroy(AccountGroup $accountGroup)
    {
        $hasCategories = DB::table('coa_categories')
            ->where('account_group_id', $accountGroup->id)
            ->exists();

        if ($hasCategories) {
            return redirect()
                ->route('chart-of-accounts.index')
                ->with('error', 'Kelompok masih digunakan oleh kategori COA.');
        }

        $accountGroup->delete();

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Kelompok akun berhasil dihapus.');
    }
}
