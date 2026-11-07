<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountGroup;
use App\Models\CoaCategory;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class AccountingMasterSeeder extends Seeder
{
   public function run(): void
    {
        DB::transaction(function () {

            // 1. MASTER ACCOUNT GROUPS
            $groups = [
                ['code' => '4', 'name' => 'Pendapatan'],
                ['code' => '6', 'name' => 'Beban'],
            ];

            foreach ($groups as $group) {
                AccountGroup::updateOrCreate(
                    ['code' => $group['code']],
                    ['name' => $group['name']]
                );
            }

            // 2. MASTER KATEGORI COA
            $categories = [
                ['name' => 'Salary', 'group' => '4'],
                ['name' => 'Other Income', 'group' => '4'],
                ['name' => 'Family Expense', 'group' => '6'],
                ['name' => 'Transport Expense', 'group' => '6'],
                ['name' => 'Meal Expense', 'group' => '6'],
            ];

            foreach ($categories as $category) {
                $group = AccountGroup::where(
                    'code',
                    $category['group']
                )->firstOrFail();

                CoaCategory::updateOrCreate(
                    [
                        'account_group_id' => $group->id,
                        'name' => $category['name'],
                    ],
                    []
                );
            }

            // 3. MASTER CHART OF ACCOUNT
            $accounts = [
                ['code' => '401', 'name' => 'Gaji Karyawan', 'category' => 'Salary'],
                ['code' => '402', 'name' => 'Gaji Ketua MPR', 'category' => 'Salary'],
                ['code' => '403', 'name' => 'Profit Trading', 'category' => 'Other Income'],
                ['code' => '601', 'name' => 'Biaya Sekolah', 'category' => 'Family Expense'],
                ['code' => '602', 'name' => 'Bensin', 'category' => 'Transport Expense'],
                ['code' => '603', 'name' => 'Parkir', 'category' => 'Transport Expense'],
            ];

            foreach ($accounts as $account) {
                $category = CoaCategory::where(
                    'name',
                    $account['category']
                )->firstOrFail();

                ChartOfAccount::updateOrCreate(
                    ['code' => $account['code']],
                    [
                        'name' => $account['name'],
                        'category_id' => $category->id,
                        'status' => true,
                    ]
                );
            }

        });
    }
}
