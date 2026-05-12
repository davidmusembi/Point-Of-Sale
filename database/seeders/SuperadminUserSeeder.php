<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminUserSeeder extends Seeder
{
    public function run()
    {
        $username = env('SUPERADMIN_USERNAME', 'admin');
        $email    = env('SUPERADMIN_EMAIL', 'admin@example.com');
        $password = env('SUPERADMIN_PASSWORD', 'password');

        \DB::table('users')->insertOrIgnore([
            'surname'    => 'Super',
            'first_name' => 'Admin',
            'last_name'  => null,
            'username'   => $username,
            'email'      => $email,
            'password'   => Hash::make($password),
            'language'   => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ensure ADMINISTRATOR_USERNAMES is set in .env so the superadmin middleware lets this user through.
        if (! env('ADMINISTRATOR_USERNAMES')) {
            $this->command->warn('⚠  ADMINISTRATOR_USERNAMES is not set in .env. Add: ADMINISTRATOR_USERNAMES=' . $username);
        }
    }
}
