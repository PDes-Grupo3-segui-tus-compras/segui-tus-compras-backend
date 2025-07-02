<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command {
    protected $signature = 'make:admin
                            {email : Email address of the admin user}
                            {password : Password for the admin user}';

    protected $description = 'Creates an admin user with the provided email and password';

    public function handle(): int {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with the email '$email' already exists.");
            return self::FAILURE;
        }

        $user = User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'admin',
        ]);

        $this->info("Admin user created successfully with ID: {$user->id}");
        return self::SUCCESS;
    }
}
