<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
      
        Rol::create(['nombre' => 'administrativo', 'descripcion' => 'Acceso limitado sin CRUD', 'color' => 'naranja']);
        Rol::create(['nombre' => 'controlador', 'descripcion' => 'Acceso completo al CRUD', 'color' => 'verde']);
    }
}
