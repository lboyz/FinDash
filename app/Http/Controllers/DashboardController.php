<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Determine date range based on request or default to current month
        $period = $request->period ?? 'current_month';
        
        // PERBAIKAN: Tambahkan validasi untuk start_date dan end_date dari request
        $useCustomDates = false;
        $startDate = null;
        $endDate = null;
        
        // Priority 1: Jika ada start_date dan end_date dari request (untuk export)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $useCustomDates = true;
            $period = 'custom';
        }
        // Priority 2: Jika ada month dan year (untuk navigasi bulan spesifik)
        elseif ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::create($request->year, $request->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $period = 'custom';
        }
        // Priority 3: Gunakan period (7d, 30d, etc.)
        else {
            // PERBAIKAN: Pastikan tanggal range sesuai dengan periode yang dipilih
            [$startDate, $endDate] = $this->getDateRangeFromPeriod($period);
            
            // PERBAIKAN TAMBAHAN: Untuk periode 7d, 30d, 90d, pastikan tidak mengambil data sebelum periode tersebut
            if (in_array($period, ['7d', '30d', '90d', '1y'])) {
                // Tambahkan pengecekan bahwa end_date tidak boleh lebih dari hari ini
                $today = Carbon::now()->endOfDay();
                if ($endDate > $today) {
                    $endDate = $today;
                }
            }
        }

        // Calculate summary - PERBAIKAN: Pastikan query menggunakan date range yang benar
        $summaryQuery = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]);

        $totalIncome = (clone $summaryQuery)->income()->sum('amount');
        $totalExpense = (clone $summaryQuery)->expense()->sum('amount');
        $netCashFlow = $totalIncome - $totalExpense;
        
        // PERBAIKAN: Hitung total_balance dengan benar (balance hingga endDate)
        $totalBalance = Transaction::where('user_id', $user->id)
            ->where('date', '<=', $endDate)
            ->get()
            ->reduce(function ($carry, $transaction) {
                if ($transaction->category === 'income') {
                    return $carry + $transaction->amount;
                } else {
                    return $carry - $transaction->amount;
                }
            }, 0);

        // Get chart data for the selected period - PERBAIKAN: Pastikan hanya data dalam range
        $chartData = $this->getChartData($user->id, $startDate, $endDate);

        // Get spending categories (expense categories with amounts)
        $spendingCategories = $this->getSpendingCategories($user->id, $startDate, $endDate);

        // Get platform usage - ONLY FOR EXPENSES
        $platformUsage = $this->getPlatformUsage($user->id, $startDate, $endDate);

        // Get financial insights
        $insights = $this->getFinancialInsights($user->id, $startDate, $endDate);

        // Get available months for filter dropdown
        $availableMonths = $this->getAvailableMonths($user->id);

        return Inertia::render('Dashboard', [
            'summary' => [
                'total_income' => (float) $totalIncome,
                'total_expense' => (float) $totalExpense,
                'net_cash_flow' => (float) $netCashFlow,
                'total_balance' => (float) $totalBalance,
                'expense_trend' => $this->calculateExpenseTrend($user->id, $startDate, $endDate),
                'transaction_count' => Transaction::where('user_id', $user->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->count(),
                'avg_daily_income' => $this->calculateAverageDaily($user->id, 'income', $startDate, $endDate), // PERBAIKAN: Tambah parameter
                'avg_daily_expense' => $this->calculateAverageDaily($user->id, 'expense', $startDate, $endDate), // PERBAIKAN: Tambah parameter
            ],
            'chart_data' => $chartData,
            'spending_categories' => $spendingCategories,
            'platform_usage' => $platformUsage,
            'insights' => $insights,
            'period' => $period,
            'current_date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'label' => $this->getDateRangeLabel($startDate, $endDate, $period),
            ],
            'available_months' => $availableMonths,
            'filter' => [
                'month' => $request->month ?? $startDate->month,
                'year' => $request->year ?? $startDate->year,
            ],
        ]);
    }

    /**
     * Get date range based on period string
     */
    private function getDateRangeFromPeriod(string $period): array
    {
        $now = Carbon::now();
        
        return match ($period) {
            '7d' => [
                $now->copy()->subDays(6)->startOfDay(), // PERBAIKAN: 7 hari TERAKHIR termasuk hari ini
                $now->copy()->endOfDay()
            ],
            '30d' => [
                $now->copy()->subDays(29)->startOfDay(), // PERBAIKAN: 30 hari TERAKHIR termasuk hari ini
                $now->copy()->endOfDay()
            ],
            '90d' => [
                $now->copy()->subDays(89)->startOfDay(), // PERBAIKAN: 90 hari TERAKHIR termasuk hari ini
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
     * Get date range label for display
     */
    private function getDateRangeLabel(Carbon $startDate, Carbon $endDate, string $period): string
    {
        if ($period === 'current_month') {
            return $startDate->format('F Y');
        }
        
        if ($period === 'last_month') {
            return $startDate->format('F Y');
        }
        
        if ($period === 'custom') {
            if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
                return $startDate->format('F Y');
            }
            return $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        }
        
        // PERBAIKAN: Format label untuk period 7d, 30d, 90d
        if ($period === '7d' || $period === '30d' || $period === '90d') {
            return 'Last ' . substr($period, 0, -1) . ' days';
        }
        
        return $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
    }

    /**
     * Get available months for filter dropdown
     */
    private function getAvailableMonths(int $userId): array
    {
        $months = Transaction::where('user_id', $userId)
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as transaction_count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($record) {
                $date = Carbon::create($record->year, $record->month, 1);
                return [
                    'value' => $date->format('Y-m'),
                    'label' => $date->format('F Y'),
                    'year' => $record->year,
                    'month' => $record->month,
                    'transaction_count' => $record->transaction_count,
                ];
            });

        return $months->toArray();
    }

    /**
     * Get chart data for the given period
     * PERBAIKAN: Pastikan hanya mengambil data dalam range yang diminta
     */
    private function getChartData(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $days = $startDate->diffInDays($endDate);
        $data = [];

        // PERBAIKAN: Untuk semua kasus, pastikan hanya mengambil data dalam range
        // For monthly view (≤ 35 days), show daily data
        if ($days <= 35) {
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $dailyIncome = Transaction::where('user_id', $userId)
                    ->where('category', 'income')
                    ->whereDate('date', $currentDate->toDateString())
                    ->sum('amount');

                $dailyExpense = Transaction::where('user_id', $userId)
                    ->where('category', 'expense')
                    ->whereDate('date', $currentDate->toDateString())
                    ->sum('amount');

                $data[] = [
                    'date' => $currentDate->toDateString(),
                    'income' => (float) $dailyIncome,
                    'expense' => (float) $dailyExpense,
                ];

                $currentDate->addDay();
            }
        } else {
            // For longer periods, show weekly or monthly data
            // PERBAIKAN: Untuk 1 year, gunakan data bulanan
            if ($days > 180) {
                $currentDate = $startDate->copy()->startOfMonth();
                
                while ($currentDate <= $endDate) {
                    $monthEnd = $currentDate->copy()->endOfMonth();
                    if ($monthEnd > $endDate) {
                        $monthEnd = $endDate;
                    }

                    $monthIncome = Transaction::where('user_id', $userId)
                        ->where('category', 'income')
                        ->whereBetween('date', [$currentDate, $monthEnd])
                        ->sum('amount');

                    $monthExpense = Transaction::where('user_id', $userId)
                        ->where('category', 'expense')
                        ->whereBetween('date', [$currentDate, $monthEnd])
                        ->sum('amount');

                    $data[] = [
                        'date' => $currentDate->toDateString(),
                        'income' => (float) $monthIncome,
                        'expense' => (float) $monthExpense,
                    ];

                    $currentDate = $monthEnd->copy()->addDay()->startOfMonth();
                }
            } else {
                // Untuk periode 90d, gunakan data mingguan
                $currentDate = $startDate->copy();
                
                while ($currentDate <= $endDate) {
                    $weekEnd = $currentDate->copy()->addDays(6);
                    if ($weekEnd > $endDate) {
                        $weekEnd = $endDate;
                    }

                    $weekIncome = Transaction::where('user_id', $userId)
                        ->where('category', 'income')
                        ->whereBetween('date', [$currentDate, $weekEnd])
                        ->sum('amount');

                    $weekExpense = Transaction::where('user_id', $userId)
                        ->where('category', 'expense')
                        ->whereBetween('date', [$currentDate, $weekEnd])
                        ->sum('amount');

                    $data[] = [
                        'date' => $currentDate->toDateString(),
                        'income' => (float) $weekIncome,
                        'expense' => (float) $weekExpense,
                    ];

                    $currentDate = $weekEnd->copy()->addDay();
                }
            }
        }

        return $data;
    }

    /**
     * Get spending categories with amounts
     */
    private function getSpendingCategories(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        // PERBAIKAN: Pastikan hanya data dalam range
        $expensesByType = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$startDate, $endDate]) // PERBAIKAN: Tambah whereBetween
            ->selectRaw('type, SUM(amount) as total_amount, COUNT(*) as transaction_count')
            ->groupBy('type')
            ->orderByDesc('total_amount')
            ->limit(8)
            ->get();

        $totalExpense = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$startDate, $endDate]) // PERBAIKAN: Tambah whereBetween
            ->sum('amount');

        $categories = [];
        $colors = ['#ef4444', '#f97316', '#8b5cf6', '#10b981', '#3b82f6', '#ec4899', '#14b8a6', '#f59e0b'];

        foreach ($expensesByType as $index => $expense) {
            $percentage = $totalExpense > 0 ? ($expense->total_amount / $totalExpense) * 100 : 0;
            
            $categories[] = [
                'name' => $expense->type,
                'amount' => (float) $expense->total_amount,
                'percentage' => round($percentage, 1),
                'transaction_count' => $expense->transaction_count,
                'color' => $colors[$index % count($colors)],
            ];
        }

        return $categories;
    }

    /**
     * Get platform usage data - ONLY FOR EXPENSES
     */
    private function getPlatformUsage(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        // PERBAIKAN: Pastikan hanya data dalam range
        $platforms = Platform::withCount(['transactions' => function ($query) use ($userId, $startDate, $endDate) {
            $query->where('user_id', $userId)
                ->where('category', 'expense')
                ->whereBetween('date', [$startDate, $endDate]); // PERBAIKAN: Tambah whereBetween
        }])
        ->withSum(['transactions' => function ($query) use ($userId, $startDate, $endDate) {
            $query->where('user_id', $userId)
                ->where('category', 'expense')
                ->whereBetween('date', [$startDate, $endDate]); // PERBAIKAN: Tambah whereBetween
        }], 'amount')
        ->orderByDesc('transactions_sum_amount')
        ->limit(6)
        ->get();

        // PERBAIKAN: Hitung total expense dalam range yang sama
        $totalExpense = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $platformIcons = [
            'GoPay' => 'Smartphone',
            'OVO' => 'CreditCard',
            'Dana' => 'Wallet',
            'Bank Transfer' => 'Globe',
            'Cash' => 'DollarSign',
            'QRIS' => 'Smartphone',
            'ShopeePay' => 'Smartphone',
            'LinkAja' => 'Smartphone',
            'Debit Card' => 'CreditCard',
            'Credit Card' => 'CreditCard',
            'PayPal' => 'Globe',
            'Virtual Account' => 'CreditCard',
            'E-Wallet' => 'Smartphone',
        ];

        $platformColors = [
            'GoPay' => '#00aa13',
            'OVO' => '#4f46e5',
            'Dana' => '#118eea',
            'Bank Transfer' => '#f59e0b',
            'Cash' => '#6b7280',
            'QRIS' => '#8b5cf6',
            'ShopeePay' => '#ee4d2d',
            'LinkAja' => '#00b2ff',
            'Debit Card' => '#3b82f6',
            'Credit Card' => '#ec4899',
            'PayPal' => '#003087',
            'Virtual Account' => '#10b981',
            'E-Wallet' => '#8b5cf6',
        ];

        $usageData = [];
        foreach ($platforms as $platform) {
            if ($platform->transactions_count > 0) {
                $expensePercentage = $totalExpense > 0 
                    ? round(($platform->transactions_sum_amount / $totalExpense) * 100, 1)
                    : 0;
                
                $usageData[] = [
                    'name' => $platform->name,
                    'amount' => (float) $platform->transactions_sum_amount,
                    'transaction_count' => $platform->transactions_count,
                    'icon' => $platformIcons[$platform->name] ?? 'CreditCard',
                    'color' => $platformColors[$platform->name] ?? '#6b7280',
                    'expense_percentage' => $expensePercentage,
                ];
            }
        }

        return $usageData;
    }

    /**
     * Calculate expense trend: compare with previous period
     */
    private function calculateExpenseTrend(int $userId, Carbon $startDate, Carbon $endDate): float
    {
        // Hitung durasi periode sekarang
        $daysInPeriod = $startDate->diffInDays($endDate) + 1;
        
        // PERBAIKAN: Pastikan hanya data dalam range
        $currentExpense = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        // Hitung periode sebelumnya dengan durasi yang sama
        $previousEndDate = $startDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($daysInPeriod - 1);
        
        // PERBAIKAN: Pastikan hanya data dalam range periode sebelumnya
        $previousExpense = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');
        
        // Jika tidak ada data periode sebelumnya, return 0
        if ($previousExpense == 0) return 0;
        
        // Hitung persentase perubahan
        return (($currentExpense - $previousExpense) / $previousExpense) * 100;
    }

    /**
     * Calculate average daily amount for income/expense
     * PERBAIKAN: Tambah parameter untuk menggunakan date range yang sedang aktif
     */
    private function calculateAverageDaily(int $userId, string $category, Carbon $startDate, Carbon $endDate): float
    {
        // PERBAIKAN: Gunakan date range yang sedang aktif, bukan bulan ini
        $total = Transaction::where('user_id', $userId)
            ->where('category', $category)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        // Hitung total hari dalam periode aktif
        $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;
        
        // Minimal 1 hari untuk hindari division by zero
        $days = max(1, $totalDaysInPeriod);
        
        return $total / $days;
    }

    /**
     * Generate financial insights based on data
     */
    private function getFinancialInsights(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $insights = [];

        // PERBAIKAN: Pastikan hanya data dalam range
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('category', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpense = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;
        $days = max(1, $startDate->diffInDays($endDate));

        // Insight 1: Income vs Expense ratio
        if ($totalIncome > 0) {
            $spendingRatio = ($totalExpense / $totalIncome) * 100;
            
            if ($spendingRatio > 80) {
                $insights[] = [
                    'title' => 'High Spending Ratio',
                    'message' => "Your expenses are " . round($spendingRatio, 1) . "% of your income this month. Consider reducing expenses to improve savings.",
                    'type' => 'warning',
                    'icon' => 'AlertCircle',
                    'color' => 'yellow',
                    'priority' => 5,
                    'metric' => round($spendingRatio, 1)
                ];
            } elseif ($spendingRatio < 30 && $totalIncome > 1000000) {
                $insights[] = [
                    'title' => 'Excellent Savings Rate',
                    'message' => "You're saving " . round(100 - $spendingRatio, 1) . "% of your income this month! Great financial discipline.",
                    'type' => 'positive',
                    'icon' => 'CheckCircle',
                    'color' => 'green',
                    'priority' => 4,
                    'metric' => round($spendingRatio, 1)
                ];
            }
        }

        // Insight 2: Balance status
        if ($balance < 0) {
            $insights[] = [
                'title' => 'Negative Cash Flow',
                'message' => "Your expenses exceed your income by " . number_format(abs($balance), 0, ',', '.') . " this month. Consider reviewing your spending.",
                'type' => 'warning',
                'icon' => 'TrendingDown',
                'color' => 'red',
                'priority' => 6,
                'metric' => round(abs($balance) / max($totalIncome, 1) * 100, 1)
            ];
        } elseif ($balance > $totalIncome * 0.5 && $totalIncome > 0) {
            $insights[] = [
                'title' => 'Strong Positive Cash Flow',
                'message' => "You're saving more than 50% of your income this month! Excellent financial management.",
                'type' => 'positive',
                'icon' => 'TrendingUp',
                'color' => 'green',
                'priority' => 3,
                'metric' => round(($balance / $totalIncome) * 100, 1)
            ];
        }

        // Insight 3: Daily spending average
        $avgDailyExpense = $totalExpense / $days;
        
        if ($avgDailyExpense > 500000) {
            $monthlyEstimate = $avgDailyExpense * 30;
            $insights[] = [
                'title' => 'High Daily Spending',
                'message' => "Your average daily spending is " . number_format($avgDailyExpense, 0, ',', '.') . " (≈" . number_format($monthlyEstimate, 0, ',', '.') . "/month).",
                'type' => 'advice',
                'icon' => 'Wallet',
                'color' => 'yellow',
                'priority' => 3,
                'metric' => round($avgDailyExpense / 1000, 1)
            ];
        }

        // Insight 4: Compare with previous period (bukan bulan sebelumnya)
        $daysInPeriod = $startDate->diffInDays($endDate);
        $previousEndDate = $startDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($daysInPeriod);
        
        $previousPeriodIncome = Transaction::where('user_id', $userId)
            ->where('category', 'income')
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');
            
        $previousPeriodExpense = Transaction::where('user_id', $userId)
            ->where('category', 'expense')
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');
        
        if ($previousPeriodIncome > 0 && $totalIncome > 0) {
            $incomeChange = (($totalIncome - $previousPeriodIncome) / $previousPeriodIncome) * 100;
            
            if ($incomeChange > 20) {
                $insights[] = [
                    'title' => 'Income Growth',
                    'message' => "Your income increased by " . round($incomeChange, 1) . "% compared to previous period.",
                    'type' => 'positive',
                    'icon' => 'TrendingUp',
                    'color' => 'green',
                    'priority' => 4,
                    'metric' => round($incomeChange, 1)
                ];
            } elseif ($incomeChange < -20) {
                $insights[] = [
                    'title' => 'Income Decrease',
                    'message' => "Your income decreased by " . round(abs($incomeChange), 1) . "% compared to previous period.",
                    'type' => 'warning',
                    'icon' => 'TrendingDown',
                    'color' => 'yellow',
                    'priority' => 4,
                    'metric' => round($incomeChange, 1)
                ];
            }
        }

        // Default insight if no others generated
        if (empty($insights)) {
            $insights[] = [
                'title' => 'Financial Health Stable',
                'message' => 'Your finances are looking stable this month. Keep tracking your expenses to maintain good financial health.',
                'type' => 'neutral',
                'icon' => 'BarChart3',
                'color' => 'gray',
                'priority' => 1,
                'metric' => null
            ];
        }

        // Sort by priority (highest first)
        usort($insights, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $insights;
    }
}