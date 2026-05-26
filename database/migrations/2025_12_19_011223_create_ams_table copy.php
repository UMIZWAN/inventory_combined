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
        Schema::create('ams_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('descriptions')->nullable();
            $table->string('filepath')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_asset_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('lifespan_from')->nullable();
            $table->integer('lifespan_to')->nullable();
            $table->integer('service_interval_from')->nullable();
            $table->integer('service_interval_to')->nullable();
            $table->string('branc_pic')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('require_asset_no')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->text('logs')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_options', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_no')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->text('logs')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_no');
            $table->string('old_asset_no')->nullable();
            $table->string('asset_image')->nullable();
            $table->string('asset_name')->nullable();
            $table->string('asset_uom')->nullable();
            $table->date('asset_purchase_date')->nullable();
            $table->decimal('asset_cost')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('inventory_asset_groups')->cascadeOnDelete();
            $table->string('asset_lifespan')->nullable();
            $table->string('asset_service_interval')->nullable();
            $table->string('color')->nullable();
            $table->string('venue')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->cascadeOnDelete();
            $table->string('inv_no')->nullable();
            $table->boolean('has_warranty')->default(false);
            $table->string('warranty_period')->nullable();

            // Disposal workflow
            $table->boolean('is_disposed')->default(false);
            $table->boolean('in_transit')->default(false);
            $table->date('dispose_date')->nullable();
            $table->text('dispose_remark')->nullable();
            $table->string('dispose_attachment')->nullable();
            $table->string('dispose_status')->nullable(); // pending, approved, rejected
            $table->text('dispose_approval_remark')->nullable();
            $table->foreignId('dispose_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dispose_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispose_approved_at')->nullable();

            // Asset approval workflow (for users without asset_approval permission)
            $table->string('approval_status')->nullable();
            $table->text('rejection_remark')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();

            $table->boolean('is_deleted')->default(false);
            $table->date('delete_date')->nullable();
            $table->text('delete_remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('asset_log')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_asset_transfer', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_running_no')->nullable();
            $table->string('transfer_purpose')->nullable();
            $table->string('transfer_status')->nullable();
            $table->decimal('transfer_cost')->nullable();
            $table->foreignId('transfer_from')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('transfer_to')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shipping_id')->nullable()->constrained('shipping_options')->cascadeOnDelete();
            $table->string('transfer_attachment')->nullable();
            $table->text('transfer_remark')->nullable();
            $table->text('transfer_log')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('inventory_asset_transfer_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->nullable()->constrained('inventory_asset_transfer')->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('inventory_assets')->cascadeOnDelete();
            $table->string('asset_transfer_status')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_asset_transfer_list');
        Schema::dropIfExists('inventory_asset_transfer');
        Schema::dropIfExists('inventory_assets');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('shipping_options');
        Schema::dropIfExists('inventory_asset_groups');
        Schema::dropIfExists('ams_forms');
    }
};
