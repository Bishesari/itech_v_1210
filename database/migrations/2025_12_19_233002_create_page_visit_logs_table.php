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
        Schema::create('page_visit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('page');          // مسیر یا نام صفحه
            $table->string('ip')->nullable(); // IP کاربر
            $table->string('user_agent')->nullable(); // مرورگر و دستگاه
            $table->date('visit_date');      // تاریخ بازدید (بدون زمان دقیق)
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visit_logs');
    }
};
