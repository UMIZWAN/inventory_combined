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
        Schema::create('inventory_asset_access_level', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('add_edit_user')->default(false);
            // $table->boolean('add_edit_pic')->default(false); //newly added
            $table->boolean('add_edit_branch')->default(false);
            $table->boolean('add_edit_department')->default(false);
            $table->boolean('add_edit_asset_grouping')->default(false);
            $table->boolean('add_edit_supplier')->default(false);
            $table->boolean('add_edit_access')->default(false);
            $table->boolean('add_edit_asset')->default(false);
            $table->boolean('asset_approval')->default(false);
            $table->boolean('add_edit_shipping')->default(false);
            $table->boolean('transfer')->default(false);
            $table->boolean('import_csv')->default(false);
            $table->boolean('dispose_asset')->default(false);
            $table->boolean('view_masterlist')->default(false);
            $table->boolean('approve_disaprove_transfer')->default(false);
            $table->boolean('change_color')->default(false);
            $table->boolean('change_user_dept')->default(false);
            $table->boolean('approve_disaprove_dispose')->default(false);
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_marketing_access_level', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Role
            $table->boolean('add_edit_role')->default(false);
            $table->boolean('view_role')->default(false);
            // User
            $table->boolean('add_edit_user')->default(false);
            $table->boolean('view_user')->default(false);
            // Asset
            $table->boolean('add_edit_asset')->default(false);
            $table->boolean('view_asset')->default(false);
            $table->boolean('view_asset_masterlist')->default(false);
            // Branch
            $table->boolean('add_edit_branch')->default(false);
            $table->boolean('view_branch')->default(false);
            // Transaction
            $table->boolean('add_edit_transaction')->default(false);
            $table->boolean('view_transaction')->default(false);
            $table->boolean('approve_reject_transaction')->default(false);
            $table->boolean('receive_transaction')->default(false);
            // Purchase Order
            $table->boolean('add_edit_purchase_order')->default(false);
            $table->boolean('view_purchase_order')->default(false);
            // Supplier
            $table->boolean('add_edit_supplier')->default(false);
            $table->boolean('view_supplier')->default(false);
            // Tax
            $table->boolean('add_edit_tax')->default(false);
            $table->boolean('view_tax')->default(false);
            // Reports
            $table->boolean('view_reports')->default(false);
            $table->boolean('download_reports')->default(false);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name')->nullable();
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->foreignId('asset_access_level_id')->nullable()->constrained('inventory_asset_access_level')->cascadeOnDelete();
            $table->foreignId('marketing_access_level_id')->nullable()->constrained('inventory_marketing_access_level')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_accessible')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unique(['user_id', 'branch_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('inventory_asset_access_level');
        Schema::dropIfExists('inventory_marketing_access_level');
    }
};
