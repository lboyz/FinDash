<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Get date range based on period string
     */
    protected function getDateRangeFromPeriod(string $period): array
    {
        $now = Carbon::now();
        
        return match ($period) {
            '7d' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay()
            ],
            '30d' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay()
            ],
            '90d' => [
                $now->copy()->subDays(89)->startOfDay(),
                $now->copy()->endOfDay()
            ],
            'current_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth()
            ],
            'current_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear()
            ],
            '1y' => [
                $now->copy()->subYear()->startOfDay(),
                $now->copy()->endOfDay()
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ],
        };
    }

    /**
     * Get filtered transactions query based on request parameters.
     * PERBAIKAN: Return juga dateRange
     */
    protected function getFilteredQuery(Request $request): array
    {
        $user = Auth::user();
        $query = Transaction::where('user_id', $user->id)->with('platform');
        
        $dateRange = null;

        // Apply date filtering with priority logic
        // Priority 1: Jika ada period (7d, 30d, 90d, current_month, etc.)
        if ($request->filled('period')) {
            [$startDate, $endDate] = $this->getDateRangeFromPeriod($request->period);
            $query->whereBetween('date', [$startDate, $endDate]);
            
            $dateRange = [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ];
        }
        // Priority 2: Jika ada month dan year (untuk specific month)
        elseif ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::create($request->year, $request->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);
            
            $dateRange = [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ];
        }
        // Priority 3: Jika ada start_date dan end_date secara manual
        elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
            
            $dateRange = [
                'start' => $request->start_date,
                'end' => $request->end_date
            ];
        }
        // Default: current month
        else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);
            
            $dateRange = [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ];
        }

        // Apply other filters
        $filters = [
            'category' => function () use ($query, $request) {
                if ($request->filled('category')) {
                    $query->where('category', $request->category);
                }
            },
            'platform_id' => function () use ($query, $request) {
                if ($request->filled('platform_id')) {
                    $query->byPlatform($request->platform_id);
                }
            },
            'type' => function () use ($query, $request) {
                if ($request->filled('type')) {
                    $query->byType($request->type);
                }
            },
            'description' => function () use ($query, $request) {
                if ($request->filled('description')) {
                    $query->searchDescription($request->description);
                }
            }
        ];

        // Apply each filter
        foreach ($filters as $filter) {
            $filter();
        }

        // PERBAIKAN: Return array dengan query dan dateRange
        return [
            'query' => $query,
            'dateRange' => $dateRange
        ];
    }

    /**
     * Export transactions to CSV.
     */
    public function csv(Request $request)
    {
        // PERBAIKAN: Get hasil dari getFilteredQuery
        $filterResult = $this->getFilteredQuery($request);
        $query = $filterResult['query'];
        $dateRange = $filterResult['dateRange'];
        
        $transactions = $query->orderBy('date', 'desc')->get();

        // Generate CSV content
        $csvContent = $this->generateCsvContent($transactions);
        
        $filename = 'transactions_' . date('Y-m-d_His') . '.csv';

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate CSV content from transactions.
     */
    protected function generateCsvContent($transactions)
    {
        // Add BOM for UTF-8 encoding
        $csv = "\xEF\xBB\xBF";
        $csv .= "Date,Category,Platform,Type,Description,Amount\n";
        
        foreach ($transactions as $transaction) {
            $csv .= sprintf(
                "%s,%s,%s,%s,\"%s\",%s\n",
                $transaction->date->format('Y-m-d'),
                ucfirst($transaction->category),
                $transaction->platform->name ?? 'N/A',
                $transaction->type,
                str_replace('"', '""', $transaction->description ?? ''),
                number_format((float) $transaction->amount, 2, '.', '')
            );
        }

        return $csv;
    }

    /**
     * Export transactions to PDF.
     * PERBAIKAN: Get dateRange dari filter result
     */
    public function pdf(Request $request)
    {
        $filterResult = $this->getFilteredQuery($request);
        $query = $filterResult['query'];
        $dateRange = $filterResult['dateRange'];
        
        $transactions = $query->orderBy('date', 'desc')->get();

        // Calculate totals
        $totalIncome = $transactions->where('category', 'income')->sum('amount');
        $totalExpense = $transactions->where('category', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return view('exports.transactions-pdf', compact(
            'transactions', 
            'totalIncome', 
            'totalExpense', 
            'balance', 
            'dateRange'
        ));
    }

    /**
     * Export transactions to JSON.
     */
    public function json(Request $request)
    {
        // PERBAIKAN: Get hasil dari getFilteredQuery
        $filterResult = $this->getFilteredQuery($request);
        $query = $filterResult['query'];
        
        $transactions = $query->orderBy('date', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'date' => $transaction->date->format('Y-m-d'),
                    'category' => $transaction->category,
                    'platform' => $transaction->platform->name ?? null,
                    'type' => $transaction->type,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $transaction->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        $filename = 'transactions_' . date('Y-m-d_His') . '.json';

        return Response::json($transactions, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Type' => 'application/json',
        ]);
    }
}