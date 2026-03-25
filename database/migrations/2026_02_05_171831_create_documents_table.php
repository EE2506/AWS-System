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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // soa, purchase_order, quotation, delivery_receipt
            $table->string('control_number')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_address')->nullable();
            $table->date('document_date');
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('draft'); // draft, finalized
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
            $table->index('control_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
