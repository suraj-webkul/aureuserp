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
        Schema::table('employees_job_positions', function (Blueprint $table) {
            $table->dropColumn('is_active');

            $table->boolean('status')->default(false)->nullable()->comment('Status')->after('requirements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees_job_positions', function (Blueprint $table) {
            $table->dropColumn('status');

            $table->boolean('is_active')->default(false)->nullable()->comment('Is Active');
        });
    }
};
