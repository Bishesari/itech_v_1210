<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstituteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Institute::create([
            'short_name' => 'آی تک',
            'full_name' => 'آموزشگاه آی تک',
            'abb' => 'ITC',
            'remain_credit' => '9999',
        ]);

        Institute::create([
            'short_name' => 'آی کد',
            'full_name' => 'آموزشگاه آی کد',
            'abb' => 'ICC',
            'remain_credit' => '9999',
        ]);
    }
}
