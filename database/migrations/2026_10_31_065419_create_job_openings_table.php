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
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();

            $table->string('job_code', 50)->unique();

            $table->string('job_title');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();

            $table->unsignedInteger('vacancies')->default(1);

            $table->string('employment_type', 50)->nullable();

            $table->unsignedTinyInteger('experience_min')->nullable();
            $table->unsignedTinyInteger('experience_max')->nullable();

            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();

            $table->longText('job_description')->nullable();
            $table->text('required_qualifications')->nullable();
            $table->json('required_skills')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('application_deadline')->nullable();

            $table->string('status', 30)->default('draft');

            $table->foreignId('created_by')
                ->nullable()
               ->constrained('users') 
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'application_deadline']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
