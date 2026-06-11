<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AlmacenUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['user' => 'almacen'],
            [
                'name' => 'Usuario Almacén',
                'password' => bcrypt('almacen123')
            ]
        );
    }
}
