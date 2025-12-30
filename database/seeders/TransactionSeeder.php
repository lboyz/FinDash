<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Platform;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user (or create one for testing)
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $platforms = Platform::all();
        $types = ['QRIS', 'Transfer', 'Tunai', 'E-Wallet', 'Debit Card', 'Credit Card'];
        
        $incomeDescriptions = [
            'Gaji Bulanan',
            'Bonus Kinerja',
            'Freelance Project',
            'Penjualan Online',
            'Investasi Dividen',
            'Komisi Penjualan',
            'Hadiah',
            'Cashback',
        ];

        $expenseDescriptions = [
            'Belanja Bulanan',
            'Makan Siang',
            'Transport',
            'Pulsa & Internet',
            'Listrik',
            'Air',
            'Subscription Netflix',
            'Subscription Spotify',
            'Kopi',
            'Bensin',
            'Parkir',
            'Belanja Online',
            'Skincare',
            'Obat',
            'Gym Membership',
            'Donasi',
        ];

        // Generate 100 random transactions over the last 3 months
        for ($i = 0; $i < 100; $i++) {
            $isIncome = rand(0, 100) < 30; // 30% chance of income
            $category = $isIncome ? 'income' : 'expense';
            
            Transaction::create([
                'user_id' => $user->id,
                'date' => Carbon::now()->subDays(rand(0, 90)),
                'category' => $category,
                'platform_id' => $platforms->random()->id,
                'type' => $types[array_rand($types)],
                'description' => $isIncome 
                    ? $incomeDescriptions[array_rand($incomeDescriptions)]
                    : $expenseDescriptions[array_rand($expenseDescriptions)],
                'amount' => $isIncome 
                    ? rand(500000, 10000000) // Income: 500k - 10M
                    : rand(10000, 500000),   // Expense: 10k - 500k
                'attachment' => null,
            ]);
        }
    }
}
