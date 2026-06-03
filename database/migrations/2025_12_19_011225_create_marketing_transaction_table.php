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

        // Purchase Orders — procurement from suppliers
        Schema::create('inventory_marketing_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('running_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('shipping_option_id')->nullable()->constrained('shipping_options')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();

            $table->enum('order_status', ['DRAFT', 'ISSUED', 'PARTIALLY RECEIVED', 'RECEIVED', 'CANCELLED'])->default('DRAFT');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('order_total_cost', 12, 2)->nullable();
            $table->text('remark')->nullable();
            $table->json('log')->nullable();
            $table->string('attachment')->nullable();

            // Trackers
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });

        // PO line items — what was ordered and what has been received against it
        Schema::create('inventory_marketing_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('inventory_marketing_purchase_orders', 'id', 'mkt_po_items_po_id_fk')
                ->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_marketing_items')->cascadeOnDelete();
            $table->integer('qty_ordered');
            $table->integer('qty_received')->default(0);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('line_total', 12, 2)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        // Operational transactions — GRN / TRANSFER / STOCK OUT
        Schema::create('inventory_marketing_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('running_number')->unique();
            $table->enum('transaction_type', ['GRN', 'TRANSFER', 'STOCK OUT']);

            // GRN — optionally linked to a PO; supplier may exist if direct/no-PO
            $table->foreignId('purchase_order_id')->nullable()->constrained('inventory_marketing_purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // STOCK OUT — who's receiving
            $table->string('recipient_name')->nullable();

            // TRANSFER
            $table->foreignId('shipping_option_id')->nullable()->constrained('shipping_options')->nullOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->cascadeOnDelete();

            $table->foreignId('transaction_purpose_id')->nullable()->constrained('inventory_marketing_transaction_purpose')->nullOnDelete();
            $table->enum('transaction_status', ['REQUESTED', 'REJECTED', 'APPROVED', 'IN-TRANSIT', 'RECEIVED', 'COMPLETED'])->nullable();

            $table->text('transaction_remark')->nullable();
            $table->json('transaction_log')->nullable();
            $table->decimal('transaction_total_cost', 12, 2)->nullable();
            $table->string('attachment')->nullable();

            // Trackers
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('received_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->timestamps();
        });

        // Transaction line items — GRN/Transfer/Stock Out lines.
        // purchase_order_item_id is set on GRN lines to trace back to the originating PO line.
        Schema::create('inventory_marketing_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('inventory_marketing_transactions')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_marketing_items')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->constrained('inventory_marketing_purchase_order_items', 'id', 'mkt_txn_items_po_item_id_fk')
                ->nullOnDelete();
            $table->integer('item_unit');
            $table->enum('status', ['ON HOLD', 'DELIVERED', 'FROZEN', 'RECEIVED', 'RETURNED', 'DISPOSED'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_marketing_transaction_items');
        Schema::dropIfExists('inventory_marketing_transactions');
        Schema::dropIfExists('inventory_marketing_purchase_order_items');
        Schema::dropIfExists('inventory_marketing_purchase_orders');
        Schema::dropIfExists('inventory_marketing_transaction_purpose');
    }
};
