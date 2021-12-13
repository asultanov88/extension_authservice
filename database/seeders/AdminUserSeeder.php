<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedAdminUser();
    }

    private function seedAdminUser(){

        User::truncate();

        $users = [
            'name' => 'admin_1',
            'email' => 'admin_1@extension.com',
            'password' => '$2y$10$Li21Rc8Olwv2mF43ju5weOLd3aIHMH3lFynxU66FF16Lfo8v82F.e'
            // decrypted password: password
        ];

        User::insert($users);

    }
}
