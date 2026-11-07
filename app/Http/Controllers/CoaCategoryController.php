<?php

namespace App\Http\Controllers;

use App\Models\CoaCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoaCategoryController extends Controller
{
    // TAMBAH KATEGORI
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_group_id' => 'required|exists:account_groups,id',
        ]);

        CoaCategory::create($validated);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Kategori COA berhasil ditambahkan.');
    }

    // EDIT KATEGORI
    public function update(Request $request, CoaCategory $coaCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_group_id' => 'required|exists:account_groups,id',
        ]);

        $coaCategory->update($validated);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Kategori COA berhasil diperbarui.');
    }

    // HAPUS KATEGORI
    public function destroy(CoaCategory $coaCategory)
    {
        $hasAccounts = DB::table('chart_of_accounts')
            ->where('category_id', $coaCategory->id)
            ->exists();

        if ($hasAccounts) {
            return redirect()
                ->route('chart-of-accounts.index')
                ->with('error', 'Kategori masih digunakan oleh akun.');
        }

        $coaCategory->delete();

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Kategori COA berhasil dihapus.');
    }
}
