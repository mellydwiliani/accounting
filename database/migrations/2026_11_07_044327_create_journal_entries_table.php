<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            // Nomor jurnal
            $table->string('journal_number')->unique();

            // Tanggal transaksi
            $table->date('transaction_date');

            // Deskripsi umum jurnal
            $table->text('description')->nullable();

            // Total debit dan kredit
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            // draft atau posted
            $table->enum('status', ['draft', 'posted'])
                ->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
