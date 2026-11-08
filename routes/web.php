
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\CoaCategoryController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\NeracaSaldoController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\DashboardController;


// ==================================================
// DAFTAR AKUN / CHART OF ACCOUNTS
// ==================================================

Route::get('/chart-of-accounts', [
    ChartOfAccountController::class,
    'index'
])->name('chart-of-accounts.index');

Route::post('/chart-of-accounts', [
    ChartOfAccountController::class,
    'store'
])->name('chart-of-accounts.store');

Route::put('/chart-of-accounts/{chartOfAccount}', [
    ChartOfAccountController::class,
    'update'
])->name('chart-of-accounts.update');

Route::delete('/chart-of-accounts/{chartOfAccount}', [
    ChartOfAccountController::class,
    'destroy'
])->name('chart-of-accounts.destroy');


// ==================================================
// MASTER KELOMPOK AKUN
// ==================================================

Route::post('/account-groups', [
    AccountGroupController::class,
    'store'
])->name('account-groups.store');

Route::put('/account-groups/{accountGroup}', [
    AccountGroupController::class,
    'update'
])->name('account-groups.update');

Route::delete('/account-groups/{accountGroup}', [
    AccountGroupController::class,
    'destroy'
])->name('account-groups.destroy');


// ==================================================
// MASTER KATEGORI COA
// ==================================================

Route::post('/coa-categories', [
    CoaCategoryController::class,
    'store'
])->name('coa-categories.store');

Route::put('/coa-categories/{coaCategory}', [
    CoaCategoryController::class,
    'update'
])->name('coa-categories.update');

Route::delete('/coa-categories/{coaCategory}', [
    CoaCategoryController::class,
    'destroy'
])->name('coa-categories.destroy');


Route::get('/jurnal-umum', [JournalController::class, 'index'])
    ->name('journals.index');

Route::post('/jurnal-umum', [JournalController::class, 'store'])
    ->name('journals.store');

Route::delete('/jurnal-umum/{id}', [JournalController::class, 'destroy'])
    ->name('journals.destroy');

Route::get('/buku-besar', [GeneralLedgerController::class, 'index'])
    ->name('ledger.index');

Route::get('/neraca-saldo', [NeracaSaldoController::class, 'index'])
    ->name('neraca-saldo.index');

Route::get('/laporan-keuangan', [FinancialReportController::class, 'index'])
    ->name('reports.index');

Route::get('/laporan-keuangan/export-excel', [FinancialReportController::class, 'exportExcel'])
    ->name('reports.export-excel');

// ==================================================
// HOME & DASHBOARD
// ==================================================

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

