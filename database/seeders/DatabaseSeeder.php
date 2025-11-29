<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(InstituteSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(InstituteRoleUserSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(ContactUserSeeder::class);
        $this->call(ProfileSeeder::class);
    }
}
