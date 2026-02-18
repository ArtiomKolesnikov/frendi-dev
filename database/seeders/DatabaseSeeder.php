<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MassPetPostsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure default admin exists
        $email = 'frendiPERFECTOadmin@gmail.com';
        if (!Admin::where('email', $email)->exists()) {
            Admin::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make('DjdsdjJkjdlSasfd234356'),
            ]);
        }

        // Optional: run large pet posts seeder explicitly when needed
        // $this->call(MassPetPostsSeeder::class);
    }
}
