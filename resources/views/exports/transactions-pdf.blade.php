<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Transaction Report - {{ date('Y-m-d') }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4 portrait;
        }
        
        @page :first {
            margin-top: 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #1a1a1a;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px;
            min-height: 100vh;
        }
        
        /* Pop-up Container */
        .report-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.08),
                0 8px 24px rgba(0, 0, 0, 0.04),
                0 2px 8px rgba(0, 0, 0, 0.02);
            padding: 40px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Decorative Elements */
        .report-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            z-index: 1;
        }
        
        .corner-decoration {
            position: absolute;
            width: 120px;
            height: 120px;
            opacity: 0.03;
            z-index: 0;
        }
        
        .corner-tl {
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #3b82f6 0%, transparent 70%);
            border-radius: 0 0 100% 0;
        }
        
        .corner-br {
            bottom: 20px;
            right: 20px;
            background: linear-gradient(315deg, #ec4899 0%, transparent 70%);
            border-radius: 100% 0 0 0;
        }
        
        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            z-index: 2;
        }
        
        .logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.2);
        }
        
        .logo-icon {
            font-size: 28px;
            color: white;
            font-weight: bold;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
        }
        
        .report-subtitle {
            color: #94a3b8;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        
        /* Meta Info Cards */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
            position: relative;
            z-index: 2;
        }
        
        .meta-card {
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .meta-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }
        
        .meta-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
        }
        
        .meta-value {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
        }
        
        /* Summary Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 32px 0;
            position: relative;
            z-index: 2;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 16px 16px 0 0;
        }
        
        .stat-income::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        
        .stat-expense::before {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        
        .stat-balance::before {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }
        
        .stat-income .stat-icon {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }
        
        .stat-expense .stat-icon {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }
        
        .stat-balance .stat-icon {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }
        
        .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
            margin: 8px 0;
        }
        
        .stat-income .stat-value {
            color: #059669;
        }
        
        .stat-expense .stat-value {
            color: #dc2626;
        }
        
        .stat-balance .stat-value {
            color: #2563eb;
        }
        
        .stat-note {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 8px;
        }
        
        /* Transactions Table */
        .table-section {
            margin: 40px 0;
            position: relative;
            z-index: 2;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            border-radius: 2px;
        }
        
        .transaction-count {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        
        thead {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
        
        th {
            padding: 18px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            vertical-align: middle;
        }
        
        tbody tr {
            transition: background-color 0.2s ease;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Table Cell Styles */
        .date-cell {
            font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
        }
        
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            gap: 6px;
        }
        
        .category-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        
        .category-income {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .category-income::before {
            background: #10b981;
        }
        
        .category-expense {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .category-expense::before {
            background: #ef4444;
        }
        
        .platform-cell {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            font-weight: 500;
        }
        
        .platform-icon {
            width: 24px;
            height: 24px;
            background: #f1f5f9;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #64748b;
        }
        
        .type-cell {
            color: #475569;
            font-weight: 500;
        }
        
        .description-cell {
            color: #475569;
            max-width: 200px;
            line-height: 1.5;
        }
        
        .description-empty {
            color: #94a3b8;
            font-style: italic;
        }
        
        .amount-cell {
            text-align: right;
            font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }
        
        .amount-income {
            color: #059669;
        }
        
        .amount-expense {
            color: #dc2626;
        }
        
        /* Total Row */
        .total-row {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
        
        .total-row td {
            padding: 20px 16px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
        }
        
        .total-label {
            font-size: 12px;
            color: #475569;
        }
        
        .total-value {
            font-size: 14px;
            color: #1e293b;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .footer-item {
            text-align: center;
        }
        
        .footer-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
        }
        
        .footer-value {
            font-size: 12px;
            color: #334155;
            font-weight: 500;
        }
        
        .signature-line {
            width: 200px;
            height: 1px;
            background: #e2e8f0;
            margin: 20px auto 10px;
        }
        
        .copyright {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 20px;
            line-height: 1.6;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
            border-radius: 12px;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            margin: 20px 0;
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.4;
        }
        
        .empty-title {
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }
        
        .empty-description {
            font-size: 13px;
            max-width: 300px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        /* Print Optimizations */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            .report-container {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                padding: 20px !important;
                border-radius: 0 !important;
            }
            
            .stat-card:hover,
            .meta-card:hover {
                transform: none !important;
                box-shadow: none !important;
            }
            
            tbody tr:hover {
                background-color: inherit !important;
            }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .report-container {
                padding: 20px;
            }
            
            .meta-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            table {
                font-size: 11px;
            }
            
            th, td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Decorative Corners -->
        <div class="corner-decoration corner-tl"></div>
        <div class="corner-decoration corner-br"></div>
        
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <div class="logo-icon">FD</div>
            </div>
            <h1 class="company-name">FinDash</h1>
            <div class="report-title">Transaction Report</div>
            <div class="report-subtitle">Financial Summary & Analysis</div>
            
            <!-- Meta Information -->
            <div class="meta-grid">
                <div class="meta-card">
                    <span class="meta-label">Report Date</span>
                    <span class="meta-value">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Generated By</span>
                    <span class="meta-value">{{ auth()->user()->name }}</span>
                </div>
                @if(isset($dateRange) && $dateRange['start'] && $dateRange['end'])
                <div class="meta-card">
                    <span class="meta-label">Date Range</span>
                    <span class="meta-value">
                        {{ \Carbon\Carbon::parse($dateRange['start'])->format('d M') }} - {{ \Carbon\Carbon::parse($dateRange['end'])->format('d M Y') }}
                    </span>
                </div>
                @endif
                <div class="meta-card">
                    <span class="meta-label">Report ID</span>
                    <span class="meta-value">#{{ strtoupper(substr(md5(time() . auth()->id()), 0, 6)) }}</span>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-income">
                <div class="stat-icon">↑</div>
                <div class="stat-label">Total Income</div>
                <div class="stat-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                <div class="stat-note">{{ $transactions->where('category', 'income')->count() }} transactions</div>
            </div>
            
            <div class="stat-card stat-expense">
                <div class="stat-icon">↓</div>
                <div class="stat-label">Total Expense</div>
                <div class="stat-value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
                <div class="stat-note">{{ $transactions->where('category', 'expense')->count() }} transactions</div>
            </div>
            
            <div class="stat-card stat-balance">
                <div class="stat-icon">⚖️</div>
                <div class="stat-label">Net Balance</div>
                <div class="stat-value {{ $balance >= 0 ? '' : 'text-red-600' }}">
                    {{ $balance >= 0 ? '+' : '-' }}Rp {{ number_format(abs($balance), 0, ',', '.') }}
                </div>
                <div class="stat-note">
                    {{ $balance >= 0 ? 'Positive cash flow' : 'Review needed' }}
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="table-section">
            <div class="section-header">
                <h2 class="section-title">Transaction Details</h2>
                <div class="transaction-count">{{ $transactions->count() }} Transactions</div>
            </div>
            
            @if($transactions->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td class="date-cell">{{ $transaction->date->format('d/m/Y') }}</td>
                        <td>
                            <span class="category-badge category-{{ $transaction->category }}">
                                {{ $transaction->category === 'income' ? 'Income' : 'Expense' }}
                            </span>
                        </td>
                        <td class="platform-cell">
                            <div class="platform-icon">💳</div>
                            <span>{{ $transaction->platform->name ?? 'N/A' }}</span>
                        </td>
                        <td class="type-cell">{{ ucfirst($transaction->type) }}</td>
                        <td class="description-cell {{ !$transaction->description ? 'description-empty' : '' }}">
                            {{ $transaction->description ?: 'No description' }}
                        </td>
                        <td class="amount-cell amount-{{ $transaction->category }}">
                            {{ $transaction->category === 'income' ? '+' : '-' }}
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <div class="empty-title">No Transactions Found</div>
                <div class="empty-description">
                    No transaction data available for the selected period.
                </div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-grid">
                <div class="footer-item">
                    <span class="footer-label">Generated</span>
                    <span class="footer-value">{{ now()->format('d M Y H:i') }}</span>
                </div>
                <div class="footer-item">
                    <span class="footer-label">Page</span>
                    <span class="footer-value">1 of 1</span>
                </div>
                <div class="footer-item">
                    <span class="footer-label">Format</span>
                    <span class="footer-value">PDF Report</span>
                </div>
            </div>
            
            <div class="signature-line"></div>
            
            <div class="copyright">
                © {{ date('Y') }} FinDash Financial Dashboard • Confidential Document
                <br>
                Report ID: {{ strtoupper(substr(md5(time() . auth()->id()), 0, 12)) }} • v2.1 • Generated automatically
            </div>
        </div>
    </div>
</body>
</html>