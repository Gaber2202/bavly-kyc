<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'kyc_records_status_created_at_index');
            $table->index(['service_type', 'created_at'], 'kyc_records_service_created_at_index');
            $table->index(['created_at', 'created_by'], 'kyc_records_created_at_user_index');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_records', function (Blueprint $table) {
            $table->dropIndex('kyc_records_status_created_at_index');
            $table->dropIndex('kyc_records_service_created_at_index');
            $table->dropIndex('kyc_records_created_at_user_index');
        });
    }
};
