<?php

namespace Tests\Feature;

use App\Models\Platform;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_transactions_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('transactions.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_income_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $platform = Platform::factory()->create();

        $data = [
            'date' => now()->format('Y-m-d'),
            'category' => 'income',
            'platform_id' => $platform->id,
            'type' => 'Salary',
            'description' => 'Monthly Salary',
            'amount' => 5000000,
        ];

        $response = $this->post(route('transactions.store'), $data);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 5000000,
            'category' => 'income'
        ]);
    }

    public function test_user_can_create_expense_transaction_with_decimal()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $platform = Platform::factory()->create();

        $data = [
            'date' => now()->format('Y-m-d'),
            'category' => 'expense',
            'platform_id' => $platform->id,
            'type' => 'Groceries',
            'description' => 'Weekly Groceries',
            'amount' => 150000.50, // Decimal amount
        ];

        $response = $this->post(route('transactions.store'), $data);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 150000.50, // Check decimal storage
            'category' => 'expense'
        ]);
    }

    public function test_user_can_update_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $platform = Platform::factory()->create();
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'date' => now(),
            'category' => 'income',
            'platform_id' => $platform->id,
            'type' => 'Initial',
            'description' => 'Initial description',
            'amount' => 100000,
        ]);

        $updatedData = [
            'date' => now()->format('Y-m-d'),
            'category' => 'income',
            'platform_id' => $platform->id,
            'type' => 'Updated',
            'description' => 'Updated description',
            'amount' => 200000,
        ];

        $response = $this->put(route('transactions.update', $transaction), $updatedData);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 200000,
            'description' => 'Updated description'
        ]);
    }

    public function test_user_can_delete_transaction()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $platform = Platform::factory()->create();
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'date' => now(),
            'category' => 'income',
            'platform_id' => $platform->id,
            'type' => 'ToDelete',
            'amount' => 100000,
        ]);

        $response = $this->delete(route('transactions.destroy', $transaction));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_access_other_users_transactions()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $platform = Platform::factory()->create();

        $transaction = Transaction::create([
            'user_id' => $user1->id,
            'date' => now(),
            'category' => 'income',
            'platform_id' => $platform->id,
            'type' => 'User1 Trans',
            'amount' => 100000,
        ]);

        $this->actingAs($user2);

        // Try to update
        $response = $this->put(route('transactions.update', $transaction), [
            'date' => now()->format('Y-m-d'),
            'category' => 'income',
            'platform_id' => $platform->id,
            'type' => 'Hacked',
            'amount' => 999999,
        ]);
        $response->assertStatus(403);

        // Try to delete
        $response = $this->delete(route('transactions.destroy', $transaction));
        $response->assertStatus(403);
    }
}
