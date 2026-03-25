<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'forecast_level')) {
                $table->string('forecast_level')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('customers', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }

            if (! Schema::hasColumn('customers', 'assigned_rep_id')) {
                $table->unsignedInteger('assigned_rep_id')->nullable();
                $table->foreign('assigned_rep_id')
                    ->references('id')
                    ->on('admins')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'assigned_rep_id')) {
                $table->dropForeign(['assigned_rep_id']);
                $table->dropColumn('assigned_rep_id');
            }

            if (Schema::hasColumn('customers', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('customers', 'forecast_level')) {
                $table->dropColumn('forecast_level');
            }
        });
    }
};
