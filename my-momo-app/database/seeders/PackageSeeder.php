<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run()
    {
        Package::create([
            'name' => 'Gói 10',
            'price' => 10.00,
        ]);

        Package::create([
            'name' => 'Gói 20',
            'price' => 20.00,
        ]);

        Package::create([
            'name' => 'Gói 30',
            'price' => 30.00,
        ]);
    }
}
