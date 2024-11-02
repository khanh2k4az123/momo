<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run()
    {
        Package::create([
            'name' => 'Gói 100000 VNĐ',
            'price' => 100000, 
            'duration' => 1,    
        ]);

        Package::create([
            'name' => 'Gói 200000 VNĐ',
            'price' => 200000,
            'duration' => 2,   
        ]);

        Package::create([
            'name' => 'Gói 300000 VNĐ',
            'price' => 300000,
            'duration' => 3,  
        ]);
    }
}
