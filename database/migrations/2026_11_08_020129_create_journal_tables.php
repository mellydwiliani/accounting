<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HEADER JURNAL
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            $table->string('journal_number')->unique();
            $table->date('transaction_date');
            $table->text('description')->nullable();

            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->enum('status', ['draft', 'posted'])
                  ->default('draft');

            $table->timestamps();
        });

        // DETAIL TRANSAKSI
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('journal_entry_id')
                  ->constrained('journal_entries')
                  ->cascadeOnDelete();

            $table->foreignId('coa_id')
                  ->constrained('chart_of_accounts')
                  ->restrictOnDelete();

            $table->text('description')->nullable();

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('journal_entries');
    }
};
