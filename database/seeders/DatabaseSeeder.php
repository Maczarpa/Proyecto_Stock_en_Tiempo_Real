<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Laptop Dell XPS',
            'description' => 'Laptop de alta gama para desarrollo',
            'price' => 1299.99,
            'stock' => 15,
            'category' => 'Tecnología'
        ]);

        Product::create([
            'name' => 'Mouse Inalámbrico',
            'description' => 'Mouse ergonómico inalámbrico',
            'price' => 29.99,
            'stock' => 45,
            'category' => 'Accesorios'
        ]);

        Product::create([
            'name' => 'Monitor 24"',
            'description' => 'Monitor Full HD para oficina',
            'price' => 199.99,
            'stock' => 8,
            'category' => 'Monitores'
        ]);

        Product::create([
            'name' => 'Teclado Mecánico',
            'description' => 'Teclado mecánico para gaming',
            'price' => 89.99,
            'stock' => 3,
            'category' => 'Accesorios'
        ]);

        $this->command->info('✅ Productos de prueba creados exitosamente!');
    }
}