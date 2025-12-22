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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();

            $table->string('page_key')->index();          // شناسه پایدار صفحه / مدل
            $table->string('fingerprint', 64)->nullable()->index();

            $table->enum('visitor_type', ['human', 'bot'])->index();

            $table->string('ip', 50)->nullable();
            $table->string('user_agent')->nullable();

            $table->date('visit_date')->index();
            $table->timestamps();

            // یکتایی بازدید روزانه برای کاربران واقعی
            $table->unique(
                ['page_key', 'fingerprint', 'visit_date'],
                'unique_human_daily_visit'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
