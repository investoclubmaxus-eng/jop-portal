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
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained('job_applications')
                ->cascadeOnDelete();

            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);

            $table->text('remarks')->nullable();

            $table->foreignId('changed_by')
                ->nullable()
               ->constrained('users') 
                ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['application_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_history');
    }
};
