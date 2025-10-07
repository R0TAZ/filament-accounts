<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FilamentAccountsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->withPersonalAccount()->create([
            'email' => config('app.default_user.email'),
            'password' => Hash::make(config('app.default_user.password')),
            'name' => config('app.default_user.name'),
        ]);
    }
}
