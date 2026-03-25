<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_items', function (Blueprint $table) {
            $table->string('name')->after('item_number')->nullable(); // nullable for existing records, though we should probably fill it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_items', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
