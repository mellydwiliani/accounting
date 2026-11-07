<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coa_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_group_id')
                ->constrained('account_groups')
                ->restrictOnDelete();

            $table->string('name');
            $table->timestamps();

            $table->unique(['account_group_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_categories');
    }
};
