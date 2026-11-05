<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color_code', 16)->default('#6366f1');
            $table->timestamps();
        });

        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->enum('priority', ['primary', 'secondary', 'tertiary'])->default('secondary');
            $table->timestamp('completed_at', 3)->nullable();
            $table->timestamps();
        });

        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date')->index();
            $table->timestamp('start_time', 3)->nullable();
            $table->timestamp('end_time', 3)->nullable();
            $table->string('activity');
            $table->enum('type', ['WORK', 'ACTIVITY'])->default('ACTIVITY');
            $table->text('notes')->nullable();
            $table->boolean('is_manually_edited')->default(false);
            $table->timestamps();
        });

        Schema::create('daily_notes', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->text('focus_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_notes');
        Schema::dropIfExists('work_logs');
        Schema::dropIfExists('todos');
        Schema::dropIfExists('projects');
    }
};
