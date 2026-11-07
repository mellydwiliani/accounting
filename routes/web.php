
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\CoaCategoryController;


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


// ==================================================
// HOME
// ==================================================

Route::get('/', function () {
    return view('welcome');
});
