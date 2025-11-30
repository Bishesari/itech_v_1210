<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name_en' => 'Newbie', 'name_fa' => 'تازه وارد', 'assignable_by_founder' => false],
            ['name_en' => 'SuperAdmin', 'name_fa' => 'سوپر ادمین', 'assignable_by_founder' => false],
            ['name_en' => 'Founder', 'name_fa' => 'موسس', 'assignable_by_founder' => false],
            ['name_en' => 'Manager', 'name_fa' => 'مدیر آموزشگاه', 'assignable_by_founder' => true],
            ['name_en' => 'Assistant', 'name_fa' => 'مسئول اداری', 'assignable_by_founder' => true],
            ['name_en' => 'Accountant', 'name_fa' => 'حسابدار', 'assignable_by_founder' => true],
            ['name_en' => 'Teacher', 'name_fa' => 'مربی', 'assignable_by_founder' => true],
            ['name_en' => 'Student', 'name_fa' => 'کارآموز', 'assignable_by_founder' => true],
            ['name_en' => 'QuestionMaker', 'name_fa' => 'طراح سوال', 'assignable_by_founder' => false],
            ['name_en' => 'QuestionAuditor', 'name_fa' => 'ممیز سوال', 'assignable_by_founder' => false],
            ['name_en' => 'Examiner', 'name_fa' => 'آزمونگر', 'assignable_by_founder' => false],
            ['name_en' => 'Marketer', 'name_fa' => 'بازاریاب', 'assignable_by_founder' => false],
            ['name_en' => 'JobSeeker', 'name_fa' => 'کارجو', 'assignable_by_founder' => false],
            ['name_en' => 'Examinee', 'name_fa' => 'آزمون دهنده', 'assignable_by_founder' => false],
            ['name_en' => 'Employer', 'name_fa' => 'کارفرما', 'assignable_by_founder' => false],
        ];

        foreach ($roles as $data) {
            Role::create($data);
        }
    }
}
