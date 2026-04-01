<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_records', function (Blueprint $table) {
            $table->id();

            $table->string('employee_name');
            $table->string('client_full_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('passport_job_title')->nullable();
            $table->string('other_job_title')->nullable();
            $table->string('service_type', 64)->index();
            $table->string('assigned_to')->nullable();

            $table->string('has_bank_statement', 8);
            $table->decimal('available_balance', 15, 2)->nullable();
            $table->decimal('expected_balance', 15, 2)->nullable();

            $table->string('marital_status', 32);
            $table->unsignedSmallInteger('children_count')->nullable();

            $table->string('has_relatives_abroad', 8);
            $table->string('nationality_type', 32);
            $table->string('nationality')->nullable();
            $table->string('residency_status')->nullable();
            $table->string('governorate')->nullable();

            $table->string('consultation_method', 32);
            $table->string('email')->nullable();
            $table->string('phone_number', 32)->index();
            $table->string('whatsapp_number', 32)->nullable();

            $table->string('previous_rejected', 8);
            $table->string('rejection_numbers')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('rejection_country')->nullable();

            $table->string('has_previous_visas', 8);
            $table->text('previous_visa_countries')->nullable();

            $table->text('recommendation')->nullable();
            $table->string('status', 64)->index();

            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_full_name', 'created_at']);
            $table->index(['created_by', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_records');
    }
};
