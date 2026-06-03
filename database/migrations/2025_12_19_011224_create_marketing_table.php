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
        Schema::create('inventory_marketing_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('inventory_marketing_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('item_running_number')->unique();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->foreignId('category_id')->constrained('inventory_marketing_category')->cascadeOnDelete();
            $table->unsignedInteger('stable_unit')->default(0);
            $table->decimal('purchase_cost', 12, 4)->nullable();
            $table->decimal('sales_cost', 12, 4)->nullable();
            $table->string('unit_measure');
            $table->string('image')->nullable();
            $table->text('remark')->nullable();
            $table->json('log')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_marketing_item_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_marketing_items')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('rack_no')->nullable();
            $table->unique(['item_id', 'branch_id']);
            $table->foreignId('location_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->unsignedInteger('current_unit')->default(0);
            $table->json('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_marketing_item_values');
        Schema::dropIfExists('inventory_marketing_items');
        Schema::dropIfExists('inventory_marketing_category');
    }
};
