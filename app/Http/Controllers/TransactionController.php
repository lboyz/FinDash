<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Platform;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Static transaction types for PostgreSQL (no enum at DB layer).
     */
    private function getTransactionTypeOptions(): array
    {
        return [
            'income' => [
                'Salary',
                'Bonus',
                'Freelance',
                'Investment',
                'Other Income',
            ],
            'expense' => [
                'Dining Out',
                'Food',
                'Groceries',
                'Transport',
                'Rent',
                'Bills',
                'Healthcare',
                'Shopping',
                'Entertainment',
                'Education',
                'Subscriptions',
                'Personal Care',
                'Gifts',
                'Donation',
                'Top-Up',
                'Travel',
                'Transfer',
                'Other',
            ],
        ];
    }

    private function getAllTransactionTypes(): array
    {
        $options = $this->getTransactionTypeOptions();

        return array_values(array_unique(array_merge(
            $options['income'],
            $options['expense']
        )));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Query untuk statistik total (tanpa pagination)
        $totalQuery = Transaction::where('user_id', $user->id);

        // Query untuk list transaksi (dengan pagination)
        $listQuery = Transaction::where('user_id', $user->id)
            ->with(['platform']);

        // Apply filters ke kedua query
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'category' => $request->category,
            'platform_id' => $request->platform_id,
            'type' => $request->type,
            'description' => $request->description,
        ];

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case 'start_date':
                        if ($request->filled('end_date')) {
                            $totalQuery->whereBetween('date', [$request->start_date, $request->end_date]);
                            $listQuery->whereBetween('date', [$request->start_date, $request->end_date]);
                        }
                        break;
                    case 'end_date':
                        // Already handled in start_date case
                        break;
                    case 'category':
                        $totalQuery->where('category', $value);
                        $listQuery->where('category', $value);
                        break;
                    case 'platform_id':
                        $totalQuery->where('platform_id', $value);
                        $listQuery->where('platform_id', $value);
                        break;
                    case 'type':
                        $totalQuery->where('type', $value);
                        $listQuery->where('type', $value);
                        break;
                    case 'description':
                        $totalQuery->where('description', 'like', '%' . $value . '%');
                        $listQuery->where('description', 'like', '%' . $value . '%');
                        break;
                }
            }
        }

        // Hitung statistik total
        $totalIncome = (float) (clone $totalQuery)->where('category', 'income')->sum('amount');
        $totalExpenses = (float) (clone $totalQuery)->where('category', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpenses;

        // Get transactions dengan pagination (16 per page)
        $transactions = $listQuery->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(16) // Ubah dari 15 ke 16
            ->withQueryString();

        // Format amount untuk setiap transaction
        $transactions->getCollection()->transform(function ($transaction) {
            $transaction->formatted_amount = 'Rp ' . number_format((float) $transaction->amount, 2, ',', '.');
            return $transaction;
        });

        // Get unique transaction types for filter dropdown (from actual transactions)
        $types = Transaction::where('user_id', $user->id)
            ->distinct()
            ->pluck('type')
            ->sort()
            ->values()
            ->toArray();

        $transactionTypes = $this->getTransactionTypeOptions();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'platforms' => Platform::orderBy('name')->get(),
            'filters' => $request->only(['start_date', 'end_date', 'category', 'platform_id', 'type', 'description']),
            'types' => $types, // Used for filter dropdown (existing data)
            'transaction_types' => $transactionTypes, // Used for form dropdown (available options)
            'statistics' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_balance' => $netBalance,
                'formatted_total_income' => 'Rp ' . number_format($totalIncome, 2, ',', '.'),
                'formatted_total_expenses' => 'Rp ' . number_format($totalExpenses, 2, ',', '.'),
                'formatted_net_balance' => 'Rp ' . number_format($netBalance, 2, ',', '.'),
            ]
        ]);
    }

    /**
     * Store a new transaction.
     */
    public function store(Request $request)
    {
        $allTypes = $this->getAllTransactionTypes();

        $rules = [
            'date' => ['required', 'date'],
            'category' => ['required', Rule::in(['income', 'expense'])],
            'platform_id' => ['required', 'exists:platforms,id'],
            'type' => ['required', 'string', Rule::in($allTypes)],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];

        // Add attachment validation for expenses
        if ($request->category === 'expense') {
            $rules['attachment'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        }

        $validated = $request->validate($rules);

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        // Create transaction
        Transaction::create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'category' => $validated['category'],
            'platform_id' => $validated['platform_id'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'attachment' => $attachmentPath,
        ]);

        return redirect()->back()->with('success', 'Transaction added successfully!');
    }

    /**
     * Update an existing transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $allTypes = $this->getAllTransactionTypes();

        $rules = [
            'date' => ['required', 'date'],
            'category' => ['required', Rule::in(['income', 'expense'])],
            'platform_id' => ['required', 'exists:platforms,id'],
            'type' => ['required', 'string', Rule::in($allTypes)],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];

        // Add attachment validation for expenses
        if ($request->category === 'expense') {
            $rules['attachment'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        }

        $validated = $request->validate($rules);


        // Handle file removal
        if ($request->boolean('remove_attachment')) {
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
                $transaction->attachment = null;
            }
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        // Update transaction
        $transaction->fill($validated);
        if ($request->boolean('remove_attachment')) {
            $transaction->attachment = null;
        }
        $transaction->save();

        return redirect()->back()->with('success', 'Transaction updated successfully!');
    }

    /**
     * Delete a transaction.
     */
    public function destroy(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete attachment if exists
        if ($transaction->attachment) {
            Storage::disk('public')->delete($transaction->attachment);
        }

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction deleted successfully!');
    }
}
