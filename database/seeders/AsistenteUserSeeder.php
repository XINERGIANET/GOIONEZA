<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AsistenteUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            ['user' => 'asistente'],
            [
                'name' => 'Asistente',
                'password' => Hash::make('asistente'),
                'role' => 'asistente'
            ]
        );
    }
}
