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
         Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            $table->string('application_code', 50)->unique();

            $table->foreignId('job_opening_id')
                ->constrained('job_openings')
                ->restrictOnDelete();

            // Applicant details
            $table->string('first_name');
            $table->string('last_name');

            $table->string('email')->index();
            $table->string('phone', 30)->index();

            $table->string('current_location')->nullable();

            // Education and experience
            $table->string('highest_qualification')->nullable();

            $table->decimal('total_experience_years', 5, 2)
                ->nullable();

            $table->decimal('current_ctc', 12, 2)->nullable();
            $table->decimal('expected_ctc', 12, 2)->nullable();

            // Resume reference
            $table->string('resume_path')->nullable();

            // Application screening
            $table->string('status', 30)->default('submitted');

            $table->text('screening_remarks')->nullable();

            $table->timestamp('submitted_at')->nullable();

            // CRM transfer tracking
            $table->string('crm_transfer_status', 30)
                ->default('pending');

            $table->unsignedBigInteger('crm_application_id')->nullable();

            $table->timestamp('transferred_at')->nullable();

            $table->text('crm_transfer_error')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['job_opening_id', 'status']);
            $table->index(['crm_transfer_status', 'created_at']);
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
