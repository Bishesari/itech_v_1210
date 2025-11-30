<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            // رکورد شرکت کاربر در آزمون
            $table->foreignId('exam_user_id')->constrained('exam_user')->cascadeOnDelete();
            // سوال پاسخ داده شده
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            // گزینه انتخابی
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            // درست/نادرست بودن پاسخ
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
            // هر سوال فقط یکبار برای هر کاربر ثبت شود
            $table->unique(['exam_user_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
