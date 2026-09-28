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
            // Drop the existing column and recreate it as text
            // This is more compatible with shared hosting than using ->change()
            Schema::table('document_items', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        
            Schema::table('document_items', function (Blueprint $table) {
                $table->text('description')->default('');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('document_items', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        
            Schema::table('document_items', function (Blueprint $table) {
                $table->string('description');
            });
    }
};
