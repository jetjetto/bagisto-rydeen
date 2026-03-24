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
        Schema::create('rydeen_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percentage', 'threshold', 'timing', 'sku_level']);
            $table->decimal('value', 12, 4)->comment('Discount % or override price');
            $table->unsignedInteger('min_qty')->nullable()->comment('For threshold type');
            $table->timestamp('starts_at')->nullable()->comment('For timing type');
            $table->timestamp('ends_at')->nullable()->comment('For timing type');
            $table->enum('scope', ['all', 'category', 'customer_group', 'sku']);
            $table->unsignedInteger('scope_id')->nullable()->comment('FK to category/group/product depending on scope');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rydeen_promotions');
    }
};
