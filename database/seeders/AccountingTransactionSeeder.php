<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingTransactionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. AMBIL COA
            |--------------------------------------------------------------------------
            */

            $accounts = DB::table('chart_of_accounts')
                ->whereIn('code', [
                    '101', '401', '402', '403',
                    '601', '602', '603', '604'
                ])
                ->get()
                ->keyBy('code');

            $requiredCodes = [
                '101', '401', '402', '403',
                '601', '602', '603', '604'
            ];

            foreach ($requiredCodes as $code) {
                if (!isset($accounts[$code])) {
                    throw new \RuntimeException(
                        "COA kode {$code} belum tersedia. Jalankan AccountingMasterSeeder terlebih dahulu."
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. HELPER UNTUK MEMBUAT / MEMPERBARUI JURNAL DEMO
            |--------------------------------------------------------------------------
            */

            $createJournal = function (
                string $number,
                string $date,
                string $description,
                string $debitCode,
                string $creditCode,
                float $amount
            ) use ($accounts) {

                $journal = DB::table('journal_entries')
                    ->where('journal_number', $number)
                    ->first();

                $journalData = [
                    'transaction_date' => $date,
                    'description' => $description,
                    'total_debit' => $amount,
                    'total_credit' => $amount,
                    'status' => 'posted',
                    'updated_at' => now(),
                ];

                if ($journal) {
                    DB::table('journal_entries')
                        ->where('id', $journal->id)
                        ->update($journalData);

                    $journalId = $journal->id;

                    // Perbarui detail jurnal demo agar tidak duplikat
                    DB::table('transactions')
                        ->where('journal_entry_id', $journalId)
                        ->delete();
                } else {
                    $journalId = DB::table('journal_entries')
                        ->insertGetId(array_merge($journalData, [
                            'journal_number' => $number,
                            'created_at' => now(),
                        ]));
                }

                // Detail Debit
                DB::table('transactions')->insert([
                    'journal_entry_id' => $journalId,
                    'coa_id' => $accounts[$debitCode]->id,
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Detail Kredit
                DB::table('transactions')->insert([
                    'journal_entry_id' => $journalId,
                    'coa_id' => $accounts[$creditCode]->id,
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            };

            /*
            |--------------------------------------------------------------------------
            | 3. TRANSAKSI AWAL SESUAI CONTOH
            |--------------------------------------------------------------------------
            */

            // 1 Januari 2022 - Gaji Perusahaan A
            $createJournal(
                'DEMO-2022-01-GAJI-A',
                '2022-01-01',
                'Gaji Di Perusahaan A',
                '101',
                '401',
                5000000
            );

            // 1 Februari 2022 - Gaji Ketua MPR
            $createJournal(
                'DEMO-2022-02-GAJI-MPR',
                '2022-02-01',
                'Gaji Ketum',
                '101',
                '402',
                7000000
            );

            // 1 Oktober 2022 - Bensin Anak
            $createJournal(
                'DEMO-2022-10-BENSIN-ANAK',
                '2022-10-01',
                'Bensin Anak',
                '602',
                '101',
                25000
            );

            /*
            |--------------------------------------------------------------------------
            | 4. TRANSAKSI TAMBAHAN UNTUK PROFIT & LOSS JANUARI - MARET
            |--------------------------------------------------------------------------
            */

            $monthlyData = [
                '2022-01' => [
                    'salary' => 7000000,
                    'income' => 5500000,
                    'family' => 500000,
                    'transport' => 200000,
                    'meal' => 150000,
                ],

                '2022-02' => [
                    'salary' => 5000000,
                    'income' => 6000000,
                    'family' => 3500000,
                    'transport' => 250000,
                    'meal' => 300000,
                ],

                '2022-03' => [
                    'salary' => 12000000,
                    'income' => 3500000,
                    'family' => 4500000,
                    'transport' => 225000,
                    'meal' => 175000,
                ],
            ];

            $types = [
                'salary' => [
                    'description' => 'Gaji Karyawan',
                    'debit' => '101',
                    'credit' => '401',
                ],

                'income' => [
                    'description' => 'Pendapatan Lainnya',
                    'debit' => '101',
                    'credit' => '403',
                ],

                'family' => [
                    'description' => 'Biaya Sekolah',
                    'debit' => '601',
                    'credit' => '101',
                ],

                'transport' => [
                    'description' => 'Biaya Transportasi',
                    'debit' => '602',
                    'credit' => '101',
                ],

                'meal' => [
                    'description' => 'Biaya Makan',
                    'debit' => '604',
                    'credit' => '101',
                ],
            ];

            foreach ($monthlyData as $month => $data) {

                foreach ($types as $type => $config) {

                    $amount = $data[$type];

                    if ($amount <= 0) {
                        continue;
                    }

                    $createJournal(
                        "DEMO-{$month}-" . strtoupper($type),
                        Carbon::parse($month . '-01')->format('Y-m-d'),
                        $config['description'],
                        $config['debit'],
                        $config['credit'],
                        $amount
                    );
                }
            }

        });
    }
}
