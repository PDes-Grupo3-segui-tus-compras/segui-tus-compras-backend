<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder {
    public function run(): void {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
            'user_type' => 'admin',
        ]);
        User::create([
            'name' => 'Normal User',
            'email' => 'user@user.com',
            'password' => bcrypt('user'),
            'user_type' => 'user',
        ]);
    }
}
