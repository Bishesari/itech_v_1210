<?php

namespace Database\Seeders;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            ['mobile_nu' => '09177755924', 'verified' => true],
            ['mobile_nu' => '09034336111', 'verified' => true],
        ];

        foreach ($contacts as $data) {
            Contact::create($data);
        }
    }
}
