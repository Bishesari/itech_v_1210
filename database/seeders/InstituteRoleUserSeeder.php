<?php

namespace Database\Seeders;

use App\Models\InstituteRoleUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstituteRoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InstituteRoleUser::create([
            'user_id' => 1,
            'role_id' => 2,
            'assigned_by' => 1,
        ]);
        InstituteRoleUser::create([
            'institute_id' => 2,
            'user_id' => 1,
            'role_id' => 3,
            'assigned_by' => 1,
        ]);

        InstituteRoleUser::create([
            'institute_id' => 1,
            'user_id' => 2,
            'role_id' => 1,
            'assigned_by' => 2,
        ]);
    }
}
