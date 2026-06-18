<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Métodos deseados
        $methods = ['Efectivo', 'Yape', 'Plin', 'BCP'];

        foreach ($methods as $method) {
            \App\Models\PaymentMethod::firstOrCreate([
                'name' => $method
            ]);
        }

        // Opcional: Ocultar o renombrar otros métodos si no se desean usar más
        // y para evitar problemas de Foreign Key Constraints
        \App\Models\PaymentMethod::whereNotIn('name', $methods)
            ->update(['name' => \DB::raw("CONCAT(name, ' (Inactivo)')")]);
    }
}
