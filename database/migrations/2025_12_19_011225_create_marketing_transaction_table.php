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
        Schema::create('inventory_marketing_transaction_purpose', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_purpose_name');
            $table->timestamps();
        });

        Schema::create('inventory_marketing_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('running_number')->unique();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();

            $table->enum('transaction_type', ['STOCK IN', 'STOCK OUT', 'STOCK TRANSFER']);
            $table->string('recipient_name')->nullable();
            $table->foreignId('shipping_option_id')->nullable()->constrained('shipping_option')->nullOnDelete();

            // STATUS only relevant for ASSET TRANSFER
            $table->enum('transaction_status', ['REQUESTED', 'REJECTED', 'APPROVED', 'IN-TRANSIT', 'RECEIVED', 'IN PROGRESS', 'COMPLETED'])->nullable();

            // PURPOSE: allow multiple purposes (JSON)
            $table->foreignId('transaction_purpose_id')->nullable()->constrained('assets_transaction_purpose')->nullOnDelete();

            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->text('transaction_remark')->nullable();
            $table->json('transaction_log')->nullable();
            $table->decimal('transaction_total_cost', 12, 2)->nullable();

            // Trackers
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
        });

        Schema::create('inventory_marketing_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('running_number')->unique();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();

            $table->enum('transaction_type', ['STOCK IN', 'STOCK OUT', 'STOCK TRANSFER']);
            $table->string('recipient_name')->nullable();
            $table->foreignId('shipping_option_id')->nullable()->constrained('shipping_option')->nullOnDelete();

            // STATUS only relevant for ASSET TRANSFER
            $table->enum('transaction_status', ['REQUESTED', 'REJECTED', 'APPROVED', 'IN-TRANSIT', 'RECEIVED', 'IN PROGRESS', 'COMPLETED'])->nullable();

            // PURPOSE: allow multiple purposes (JSON)
            $table->foreignId('transaction_purpose_id')->nullable()->constrained('assets_transaction_purpose')->nullOnDelete();

            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->text('transaction_remark')->nullable();
            $table->json('transaction_log')->nullable();
            $table->decimal('transaction_total_cost', 12, 2)->nullable();

            // Trackers
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
        });

        Schema::create('inventory_marketing_transaction_item_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained('inventory_marketing_transactions')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_order')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('status', ['ON HOLD', 'DELIVERED', 'FROZEN', 'RECEIVED', 'RETURNED', 'DISPOSED'])->nullable();
            $table->integer('asset_unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
