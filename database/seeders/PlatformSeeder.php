<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            // E-Wallets
            'Gopay',
            'OVO',
            'Dana',
            'ShopeePay',
            'LinkAja',
            
            // Banks
            'BCA',
            'Mandiri',
            'BRI',
            'BNI',
            'BTN',
            'CIMB Niaga',
            'Permata',
            'Danamon',
            
            // Others
            'Cash',
            'Tunai',
        ];

        foreach ($platforms as $platform) {
            Platform::create(['name' => $platform]);
        }
    }
}
