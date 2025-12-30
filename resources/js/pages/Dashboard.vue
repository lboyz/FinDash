<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowDownLeft,
    ArrowUpRight,
    BarChart3,
    Bell,
    Calendar,
    CheckCircle,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CreditCard,
    DollarSign,
    Download,
    Eye,
    FileJson,
    FileSpreadsheet,
    FileText,
    Filter,
    Globe,
    Info,
    Lightbulb,
    MoreHorizontal,
    PieChart,
    RefreshCw,
    Smartphone,
    Target,
    TrendingDown,
    TrendingUp,
    Wallet,
    Zap,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

// Definisikan type untuk icon components
type IconComponent = typeof TrendingUp;
type IconName =
    | 'TrendingUp'
    | 'TrendingDown'
    | 'Wallet'
    | 'DollarSign'
    | 'CreditCard'
    | 'Smartphone'
    | 'Globe'
    | 'BarChart3'
    | 'AlertCircle'
    | 'CheckCircle'
    | 'Info'
    | 'ArrowUpRight'
    | 'ArrowDownLeft'
    | 'PieChart'
    | 'Filter'
    | 'Download'
    | 'FileText'
    | 'FileSpreadsheet'
    | 'Calendar'
    | 'RefreshCw'
    | 'MoreHorizontal'
    | 'Eye'
    | 'FileJson'
    | 'Lightbulb'
    | 'Target'
    | 'Bell'
    | 'Zap'
    | 'ChevronLeft'
    | 'ChevronRight'
    | 'ChevronDown';

// Icon components mapping
const iconMap: Record<IconName, any> = {
    TrendingUp,
    TrendingDown,
    Wallet,
    DollarSign,
    CreditCard,
    Smartphone,
    Globe,
    BarChart3,
    AlertCircle,
    CheckCircle,
    Info,
    ArrowUpRight,
    ArrowDownLeft,
    PieChart,
    Filter,
    Download,
    FileText,
    FileSpreadsheet,
    Calendar,
    RefreshCw,
    MoreHorizontal,
    Eye,
    FileJson,
    Lightbulb,
    Target,
    Bell,
    Zap,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
};

// Helper function untuk mendapatkan icon component
const getIconComponent = (iconName: IconName) => {
    return iconMap[iconName] || Info;
};

interface ChartDataPoint {
    date: string;
    income: number;
    expense: number;
    net: number;
}

interface SpendingCategory {
    name: string;
    amount: number;
    percentage: number;
    transaction_count: number;
    color: string;
}

interface PlatformUsage {
    name: string;
    amount: number;
    transaction_count: number;
    icon: IconName;
    color: string;
    expense_percentage: number;
}

interface FinancialInsight {
    title: string;
    message: string;
    type: 'positive' | 'warning' | 'advice' | 'neutral';
    icon: IconName;
    color: string;
    priority: number;
    metric?: number;
}

interface AvailableMonth {
    value: string;
    label: string;
    year: number;
    month: number;
    transaction_count: number;
}

// Definisikan tipe untuk period yang valid
type PeriodType =
    | '7d'
    | '30d'
    | '90d'
    | '1y'
    | 'current_month'
    | 'last_month'
    | 'current_year';

// Perbaikan: Tambahkan validasi untuk PeriodType
const validPeriods: PeriodType[] = [
    '7d',
    '30d',
    '90d',
    '1y',
    'current_month',
    'last_month',
    'current_year',
];

// Helper untuk validasi period
const isValidPeriod = (period: string): period is PeriodType => {
    return validPeriods.includes(period as PeriodType);
};

const props = defineProps<{
    summary: {
        total_income: number;
        total_expense: number;
        net_cash_flow: number;
        total_balance: number;
        income_trend: number;
        expense_trend: number;
        transaction_count: number;
        avg_daily_income: number;
        avg_daily_expense: number;
    };
    chart_data: ChartDataPoint[];
    spending_categories: SpendingCategory[];
    platform_usage: PlatformUsage[];
    insights: FinancialInsight[];
    period: string;
    current_date_range: {
        start: string;
        end: string;
        label: string;
    };
    available_months: AvailableMonth[];
    filter: {
        month?: number;
        year?: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// State
const activeChart = ref<'line' | 'bar' | 'area'>('line');
const timeRange = ref<PeriodType>(
    isValidPeriod(props.period) ? props.period : 'current_month',
);
const hoveredData = ref<ChartDataPoint | null>(null);
const hoverX = ref<number>(0);
const showIncome = ref(true);
const showExpense = ref(true);
const showNet = ref(false);
const isLoading = ref(false);
const selectedInsightFilter = ref<'all' | 'positive' | 'warning' | 'advice'>(
    'all',
);
const showMonthDropdown = ref(false);
const showMonthSelector = ref(true);
const monthFilterType = ref<'current' | 'specific'>('current');
const originalPeriod = ref<PeriodType>('current_month');
const isExportingQuickReport = ref(false);

// Enhanced chart data dengan validasi
const enhancedChartData = computed(() => {
    if (!props.chart_data || !Array.isArray(props.chart_data)) return [];

    return props.chart_data.map((item) => ({
        date: item.date || '',
        income: Number(item.income) || 0,
        expense: Number(item.expense) || 0,
        net: (Number(item.income) || 0) - (Number(item.expense) || 0),
    }));
});

const expenseTrend = computed(() => {
    const trend = props.summary?.expense_trend || 0;
    return isNaN(trend) ? 0 : trend;
});

// Filtered insights
const filteredInsights = computed(() => {
    if (selectedInsightFilter.value === 'all') {
        return props.insights.sort((a, b) => b.priority - a.priority);
    }
    return props.insights
        .filter((insight) => insight.type === selectedInsightFilter.value)
        .sort((a, b) => b.priority - a.priority);
});

// Format currency function dengan validasi
const formatCurrency = (amount: number | string) => {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(numAmount)) return 'Rp0';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numAmount);
};

const formatCompactCurrency = (amount: number | string) => {
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(numAmount) || numAmount === 0) return 'Rp0';

    const absAmount = Math.abs(numAmount);
    const sign = numAmount < 0 ? '-' : '';

    if (absAmount >= 1000000000) {
        return `${sign}Rp${(absAmount / 1000000000).toFixed(2)}M`;
    }
    if (absAmount >= 1000000) {
        return `${sign}Rp${(absAmount / 1000000).toFixed(1)}JT`;
    }
    if (absAmount >= 1000) {
        return `${sign}Rp${(absAmount / 1000).toFixed(0)}RB`;
    }
    return `${sign}Rp${absAmount.toFixed(0)}`;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) return 'Today';
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const formatPercentage = (value: number) => {
    if (isNaN(value)) return '0%';
    return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`;
};

// Chart interaction
const handleChartHover = (event: MouseEvent) => {
    const chartElement = event.currentTarget as HTMLElement;
    const rect = chartElement.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const width = rect.width;

    hoverX.value = (x / width) * 100;

    if (enhancedChartData.value.length > 0) {
        const index = Math.min(
            Math.floor((x / width) * enhancedChartData.value.length),
            enhancedChartData.value.length - 1,
        );
        hoveredData.value = enhancedChartData.value[index];
    }
};

const handleChartLeave = () => {
    hoveredData.value = null;
};

// Chart generation functions
const getLinePath = (key: 'income' | 'expense' | 'net') => {
    const data = enhancedChartData.value;
    if (!data || data.length < 2) return '';

    const maxVal = Math.max(
        ...data.map((d) => Math.max(d.income, d.expense, Math.abs(d.net))),
    );
    if (maxVal === 0 || isNaN(maxVal)) return '';

    let path = '';
    data.forEach((point, i) => {
        const value = point[key] || 0;
        const x = (i / (data.length - 1)) * 100;
        const y = 100 - (value / maxVal) * 100;

        if (i === 0) {
            path = `M ${x} ${y}`;
        } else {
            const prevValue = data[i - 1][key] || 0;
            const prevX = ((i - 1) / (data.length - 1)) * 100;
            const prevY = 100 - (prevValue / maxVal) * 100;
            const cpX = (prevX + x) / 2;

            path += ` Q ${cpX} ${prevY}, ${x} ${y}`;
        }
    });

    return path;
};

const getAreaPath = (key: 'income' | 'expense') => {
    const data = enhancedChartData.value;
    if (data.length < 2) return '';

    const maxVal = Math.max(...data.map((d) => Math.max(d.income, d.expense)));
    if (maxVal === 0) return '';

    let path = `M 0 100 `;
    data.forEach((point, i) => {
        const x = (i / (data.length - 1)) * 100;
        const y = 100 - (point[key] / maxVal) * 100;

        if (i === 0) {
            path += `L ${x} ${y} `;
        } else {
            const prevX = ((i - 1) / (data.length - 1)) * 100;
            const prevY = 100 - (data[i - 1][key] / maxVal) * 100;
            const cpX = (prevX + x) / 2;

            path += `Q ${cpX} ${prevY}, ${x} ${y} `;
        }
    });

    path += `L 100 100 Z`;
    return path;
};

const getBarPositions = (key: 'income' | 'expense') => {
    const data = enhancedChartData.value;
    if (data.length === 0) return [];

    const maxVal = Math.max(...data.map((d) => Math.max(d.income, d.expense)));
    if (maxVal === 0) return [];

    return data.map((point, i) => {
        const x = (i / data.length) * 100;
        const width = (100 / data.length) * 0.7;
        const height = (point[key] / maxVal) * 100;

        return {
            x,
            y: 100 - height,
            width,
            height,
            value: point[key],
        };
    });
};

// Period change handler - PERBAIKAN: Gunakan PeriodType
const handlePeriodChange = (period: string) => {
    const validPeriod: PeriodType = isValidPeriod(period)
        ? period
        : 'current_month';

    timeRange.value = validPeriod;
    isLoading.value = true;
    isExportingQuickReport.value = false;

    if (period === 'current_month' || period === 'last_month') {
        monthFilterType.value = 'current';
    } else {
        monthFilterType.value = 'current';
    }

    router.get(
        '/dashboard',
        {
            period: validPeriod,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

// Fungsi untuk quick export dengan periode spesifik
const quickExport = (type: 'pdf' | 'csv' | 'json', period: string) => {
    isExportingQuickReport.value = true;

    const url = `/export/${type}`;
    const params = new URLSearchParams();

    params.append('period', period);

    if (type === 'pdf') {
        window.open(`${url}?${params.toString()}`, '_blank');
    } else {
        window.location.href = `${url}?${params.toString()}`;
    }

    setTimeout(() => {
        isExportingQuickReport.value = false;
    }, 1000);
};

// Fungsi untuk export current month yang benar
const exportCurrentMonth = () => {
    if (
        monthFilterType.value === 'specific' &&
        props.filter?.month &&
        props.filter?.year
    ) {
        // Jika ada bulan spesifik yang dipilih, export bulan tersebut
        const url = `/export/pdf`;
        const params = new URLSearchParams();
        params.append('month', props.filter.month.toString());
        params.append('year', props.filter.year.toString());
        window.open(`${url}?${params.toString()}`, '_blank');
    } else {
        // Export bulan saat ini (current_month)
        const url = `/export/pdf`;
        const params = new URLSearchParams();
        params.append('period', 'current_month');
        window.open(`${url}?${params.toString()}`, '_blank');
    }
};

// PERBAIKAN: Handle "Current Month" button in dropdown
const goToCurrentMonth = () => {
    showMonthDropdown.value = false;
    isLoading.value = true;
    monthFilterType.value = 'current';
    timeRange.value = 'current_month';

    router.get(
        '/dashboard',
        {
            period: 'current_month',
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

// PERBAIKAN: Handle "Last Month" button in dropdown
const goToLastMonth = () => {
    showMonthDropdown.value = false;
    isLoading.value = true;
    monthFilterType.value = 'current';
    timeRange.value = 'last_month';

    router.get(
        '/dashboard',
        {
            period: 'last_month',
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

// Month navigation
const goToPreviousMonth = () => {
    if (!props.filter?.month || !props.filter?.year) return;

    const filterMonth = Number(props.filter.month);
    const filterYear = Number(props.filter.year);

    if (isNaN(filterMonth) || isNaN(filterYear)) return;

    let newMonth = filterMonth - 1;
    let newYear = filterYear;

    if (newMonth < 1) {
        newMonth = 12;
        newYear = filterYear - 1;
    }

    isLoading.value = true;
    monthFilterType.value = 'specific';
    timeRange.value = 'current_month';

    router.get(
        '/dashboard',
        {
            month: newMonth,
            year: newYear,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const goToNextMonth = () => {
    if (!props.filter?.month || !props.filter?.year) {
        return;
    }

    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();

    const filterMonth = Number(props.filter.month);
    const filterYear = Number(props.filter.year);

    if (isNaN(filterMonth) || isNaN(filterYear)) {
        return;
    }

    if (
        filterYear > currentYear ||
        (filterYear === currentYear && filterMonth > currentMonth)
    ) {
        return;
    }

    let newMonth = filterMonth + 1;
    let newYear = filterYear;

    if (newMonth > 12) {
        newMonth = 1;
        newYear += 1;
    }

    if (
        newYear > currentYear ||
        (newYear === currentYear && newMonth > currentMonth)
    ) {
        return;
    }

    isLoading.value = true;
    monthFilterType.value = 'specific';
    timeRange.value = 'current_month';

    router.get(
        '/dashboard',
        {
            month: newMonth,
            year: newYear,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const selectMonth = (month: AvailableMonth) => {
    showMonthDropdown.value = false;
    isLoading.value = true;
    monthFilterType.value = 'specific';
    timeRange.value = 'current_month';

    router.get(
        '/dashboard',
        {
            month: month.month,
            year: month.year,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

// Computed properties
const currentMonthLabel = computed(() => {
    if (monthFilterType.value === 'current') {
        if (timeRange.value === 'current_month') {
            return 'Current Month';
        } else if (timeRange.value === 'last_month') {
            return 'Last Month';
        } else {
            // Untuk period 7d, 30d, 90d
            return props.current_date_range?.label || timeRange.value;
        }
    }

    if (
        monthFilterType.value === 'specific' &&
        props.filter?.month &&
        props.filter?.year
    ) {
        const date = new Date(props.filter.year, props.filter.month - 1, 1);
        return date.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric',
        });
    }

    return 'Select Month';
});

// PERBAIKAN: Update computed property isNextMonthDisabled juga
const isNextMonthDisabled = computed(() => {
    if (!props.filter?.month || !props.filter?.year) return true;

    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();

    const filterMonth = Number(props.filter.month);
    const filterYear = Number(props.filter.year);

    if (isNaN(filterMonth) || isNaN(filterYear)) return true;

    return (
        filterYear > currentYear ||
        (filterYear === currentYear && filterMonth >= currentMonth)
    );
});

// Export CSV
const exportCSV = () => {
    const url = `/export/csv`;
    const params = new URLSearchParams();

    if (
        monthFilterType.value === 'specific' &&
        props.filter?.month &&
        props.filter?.year
    ) {
        params.append('month', props.filter.month.toString());
        params.append('year', props.filter.year.toString());
    } else {
        params.append('period', timeRange.value);
    }
    window.location.href = `${url}?${params.toString()}`;
};

// Export PDF
const exportPDF = () => {
    const url = `/export/pdf`;
    const params = new URLSearchParams();

    if (
        monthFilterType.value === 'specific' &&
        props.filter?.month &&
        props.filter?.year
    ) {
        params.append('month', props.filter.month.toString());
        params.append('year', props.filter.year.toString());
    } else {
        params.append('period', timeRange.value);
    }
    window.open(`${url}?${params.toString()}`, '_blank');
};

const exportJSON = () => {
    const url = `/export/json`;
    const params = new URLSearchParams();

    if (
        monthFilterType.value === 'specific' &&
        props.filter?.month &&
        props.filter?.year
    ) {
        params.append('month', props.filter.month.toString());
        params.append('year', props.filter.year.toString());
    } else {
        params.append('period', timeRange.value);
    }
    window.location.href = `${url}?${params.toString()}`;
};

// // Quick export untuk period tertentu
// const setPeriodAndExport = (period: string) => {
//     const validPeriod: PeriodType = isValidPeriod(period) ? period : 'current_month';

//     timeRange.value = validPeriod;
//     monthFilterType.value = 'current';

//     let url = `/export/pdf`;
//     const params = new URLSearchParams();
//     params.append('period', validPeriod);

//     window.open(`${url}?${params.toString()}`, '_blank');
// };

// Spending ratio dengan validasi
const spendingRatio = computed(() => {
    if (!props.summary) return 0;

    const income = props.summary.total_income || 0;
    const expense = props.summary.total_expense || 0;

    if (income === 0) return 0;
    return (expense / income) * 100;
});

// Initialize dengan validasi
onMounted(() => {
    if (enhancedChartData.value.length > 0) {
        hoveredData.value =
            enhancedChartData.value[enhancedChartData.value.length - 1];
    }

    if (isValidPeriod(props.period)) {
        originalPeriod.value = props.period;
        timeRange.value = props.period;
    }

    // Validasi props.filter
    if (props.filter && props.filter.month && props.filter.year) {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();

        // PERBAIKAN: Konversi ke number
        const filterMonth = Number(props.filter.month);
        const filterYear = Number(props.filter.year);

        if (!isNaN(filterMonth) && !isNaN(filterYear)) {
            if (filterMonth === currentMonth && filterYear === currentYear) {
                monthFilterType.value = 'current';
            } else {
                monthFilterType.value = 'specific';
            }
        }
    }
});

// Watch dengan validasi
watch(
    () => props.period,
    (newPeriod) => {
        if (isValidPeriod(newPeriod)) {
            timeRange.value = newPeriod;
        } else {
            timeRange.value = 'current_month'; // default
        }
    },
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-8 p-8 pt-6">
            <!-- Hero Section -->
            <div
                class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center"
            >
                <div class="flex-1">
                    <div class="mb-2 flex items-center gap-3">
                        <div
                            class="rounded-lg bg-gradient-to-br from-orange-600 to-orange-700 p-2"
                        >
                            <BarChart3 class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h2
                                class="text-3xl font-bold tracking-tight text-white/90"
                            >
                                Financial Dashboard
                            </h2>
                            <p class="text-muted-foreground">
                                <span
                                    v-if="
                                        monthFilterType === 'current' &&
                                        timeRange === 'current_month'
                                    "
                                >
                                    {{
                                        current_date_range?.label ||
                                        'Current Month Overview'
                                    }}
                                </span>
                                <span
                                    v-else-if="monthFilterType === 'specific'"
                                >
                                    {{ currentMonthLabel }}
                                </span>
                                <span v-else>
                                    {{
                                        timeRange === '7d'
                                            ? 'Last 7 Days Overview'
                                            : timeRange === '30d'
                                              ? 'Last 30 Days Overview'
                                              : timeRange === '90d'
                                                ? 'Last 90 Days Overview'
                                                : timeRange === '1y'
                                                  ? 'Last Year Overview'
                                                  : timeRange ===
                                                      ('last_month' as PeriodType)
                                                    ? 'Last Month Overview'
                                                    : timeRange ===
                                                        'current_year'
                                                      ? 'Current Year Overview'
                                                      : 'Overview'
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Month Selector & Quick Stats -->
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Month Selector -->
                    <div
                        class="flex items-center gap-2"
                        v-if="
                            showMonthSelector && timeRange === 'current_month'
                        "
                    >
                        <button
                            @click="goToPreviousMonth"
                            class="rounded-md p-1.5 transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-30"
                            :disabled="isLoading"
                            title="Previous Month"
                        >
                            <ChevronLeft class="h-4 w-4 text-zinc-400" />
                        </button>

                        <div class="relative">
                            <button
                                @click="showMonthDropdown = !showMonthDropdown"
                                class="flex items-center gap-2 rounded-lg border border-zinc-800 bg-zinc-900/50 px-3 py-1.5 text-sm transition-colors hover:bg-zinc-800"
                                :disabled="isLoading"
                            >
                                <Calendar class="h-4 w-4 text-zinc-400" />
                                <span class="text-zinc-300">{{
                                    currentMonthLabel
                                }}</span>
                                <ChevronDown class="h-4 w-4 text-zinc-400" />
                            </button>

                            <!-- Month Dropdown -->
                            <div
                                v-if="showMonthDropdown"
                                class="absolute top-full left-0 z-50 mt-1 max-h-60 w-48 overflow-y-auto rounded-lg border border-zinc-800 bg-zinc-900 shadow-xl"
                                v-click-outside="
                                    () => (showMonthDropdown = false)
                                "
                            >
                                <div class="p-2">
                                    <div
                                        class="px-3 py-2 text-xs text-zinc-500"
                                    >
                                        Available Months
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <!-- PERBAIKAN: Gunakan goToCurrentMonth -->
                                        <button
                                            @click="goToCurrentMonth"
                                            class="mb-1 flex w-full items-center justify-between rounded-md px-3 py-2 text-sm transition-colors hover:bg-zinc-800"
                                            :class="
                                                monthFilterType === 'current' &&
                                                timeRange === 'current_month'
                                                    ? 'bg-zinc-800 text-white'
                                                    : 'text-zinc-400 hover:text-white'
                                            "
                                        >
                                            <span>Current Month</span>
                                            <span class="text-xs text-zinc-500"
                                                >Now</span
                                            >
                                        </button>

                                        <!-- PERBAIKAN: Gunakan goToLastMonth -->
                                        <button
                                            @click="goToLastMonth"
                                            class="mb-1 flex w-full items-center justify-between rounded-md px-3 py-2 text-sm transition-colors hover:bg-zinc-800"
                                            :class="
                                                timeRange ===
                                                    ('last_month' as PeriodType) &&
                                                monthFilterType === 'current'
                                                    ? 'bg-zinc-800 text-white'
                                                    : 'text-zinc-400 hover:text-white'
                                            "
                                        >
                                            <span>Last Month</span>
                                            <span class="text-xs text-zinc-500"
                                                >Auto</span
                                            >
                                        </button>

                                        <div
                                            class="my-2 border-t border-zinc-800"
                                        ></div>

                                        <button
                                            v-for="month in available_months"
                                            :key="month.value"
                                            @click="selectMonth(month)"
                                            class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm transition-colors hover:bg-zinc-800"
                                            :class="
                                                monthFilterType ===
                                                    'specific' &&
                                                filter?.month === month.month &&
                                                filter?.year === month.year
                                                    ? 'bg-zinc-800 text-white'
                                                    : 'text-zinc-400 hover:text-white'
                                            "
                                        >
                                            <span>{{ month.label }}</span>
                                            <span class="text-xs text-zinc-500"
                                                >{{
                                                    month.transaction_count
                                                }}
                                                trans</span
                                            >
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            @click="goToNextMonth"
                            @keyup.enter="goToNextMonth"
                            class="rounded-md p-1.5 transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-30"
                            :disabled="isNextMonthDisabled || isLoading"
                            title="Next Month"
                        >
                            <ChevronRight class="h-4 w-4 text-zinc-400" />
                        </button>
                    </div>

                    <!-- Quick Stats -->
                    <div class="flex flex-wrap gap-4">
                        <div
                            class="flex items-center gap-2 rounded-lg border border-zinc-800 bg-zinc-900/50 px-4 py-2"
                        >
                            <Calendar class="h-4 w-4 text-zinc-400" />
                            <span class="text-sm text-zinc-300">{{
                                new Date().toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    month: 'long',
                                    day: 'numeric',
                                })
                            }}</span>
                        </div>
                        <button
                            @click="router.get('/dashboard')"
                            class="flex items-center gap-2 rounded-lg border border-zinc-800 bg-zinc-900/50 px-4 py-2 transition-colors hover:bg-zinc-800"
                            :disabled="isLoading"
                        >
                            <RefreshCw
                                class="h-4 w-4 text-zinc-400"
                                :class="{ 'animate-spin': isLoading }"
                            />
                            <span class="text-sm text-zinc-300">{{
                                isLoading ? 'Loading...' : 'Refresh'
                            }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Stats Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Balance Card -->
                <div
                    class="group relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-zinc-700"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-lg bg-gradient-to-br from-blue-600 to-blue-800 p-2"
                        >
                            <Wallet class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-zinc-400">
                            Total Balance
                        </p>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-3xl font-bold text-white">
                                {{
                                    formatCurrency(summary?.total_balance || 0)
                                }}
                            </h3>
                            <span
                                class="rounded-full bg-zinc-800 px-2 py-1 text-xs text-zinc-300"
                                >IDR</span
                            >
                        </div>
                        <p class="text-xs text-zinc-500">
                            {{ summary?.transaction_count }} transactions
                        </p>
                    </div>
                    <div
                        class="absolute -right-8 -bottom-8 h-32 w-32 opacity-5"
                    >
                        <svg
                            viewBox="0 0 100 100"
                            class="h-full w-full fill-current text-white"
                        >
                            <circle cx="50" cy="50" r="50" />
                        </svg>
                    </div>
                </div>

                <!-- Total Income Card -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-zinc-700"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-lg bg-gradient-to-br from-green-600 to-green-800 p-2"
                        >
                            <ArrowUpRight class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-zinc-400">
                            Total Income
                        </p>
                        <h3 class="text-3xl font-bold text-white">
                            {{ formatCurrency(summary.total_income) }}
                        </h3>
                        <p class="text-xs text-zinc-500">
                            Avg:
                            {{
                                formatCompactCurrency(summary.avg_daily_income)
                            }}/day
                        </p>
                    </div>
                    <div
                        class="absolute -right-6 -bottom-6 h-24 w-24 opacity-5"
                    >
                        <svg
                            viewBox="0 0 100 100"
                            class="h-full w-full fill-current text-green-500"
                        >
                            <path d="M0 100 L0 50 Q25 0 50 50 T100 50 V100 Z" />
                        </svg>
                    </div>
                </div>

                <!-- Total Expense Card -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-zinc-700"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-lg bg-gradient-to-br from-red-600 to-red-800 p-2"
                        >
                            <ArrowDownLeft class="h-5 w-5 text-white" />
                        </div>
                        <div class="flex items-center gap-1">
                            <TrendingUp
                                class="h-4 w-4 text-red-500"
                                v-if="expenseTrend >= 0"
                            />
                            <TrendingDown
                                class="h-4 w-4 text-green-500"
                                v-else
                            />
                            <span
                                class="text-xs font-medium"
                                :class="
                                    expenseTrend >= 0
                                        ? 'text-red-500'
                                        : 'text-green-500'
                                "
                            >
                                {{ formatPercentage(expenseTrend) }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-zinc-400">
                            Total Expense
                        </p>
                        <h3 class="text-3xl font-bold text-white">
                            {{ formatCurrency(summary.total_expense) }}
                        </h3>
                        <p class="text-xs text-zinc-500">
                            Avg:
                            {{
                                formatCompactCurrency(
                                    summary.avg_daily_expense,
                                )
                            }}/day
                        </p>
                    </div>
                    <div
                        class="absolute -right-6 -bottom-6 h-24 w-24 opacity-5"
                    >
                        <svg
                            viewBox="0 0 100 100"
                            class="h-full w-full fill-current text-red-500"
                        >
                            <path d="M0 0 L0 50 Q25 100 50 50 T100 50 V0 Z" />
                        </svg>
                    </div>
                </div>

                <!-- Net Flow Card -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6 shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-zinc-700"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-lg bg-gradient-to-br from-purple-600 to-purple-800 p-2"
                        >
                            <BarChart3 class="h-5 w-5 text-white" />
                        </div>
                        <div
                            class="rounded-full px-2 py-1"
                            :class="
                                summary.net_cash_flow >= 0
                                    ? 'bg-green-500/20'
                                    : 'bg-red-500/20'
                            "
                        >
                            <span
                                class="text-xs font-medium"
                                :class="
                                    summary.net_cash_flow >= 0
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                            >
                                {{
                                    summary.net_cash_flow >= 0
                                        ? 'Profit'
                                        : 'Loss'
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-zinc-400">
                            Net Cash Flow
                        </p>
                        <h3
                            class="text-3xl font-bold"
                            :class="
                                summary.net_cash_flow >= 0
                                    ? 'text-green-500'
                                    : 'text-red-500'
                            "
                        >
                            {{ summary.net_cash_flow >= 0 ? '+' : ''
                            }}{{ formatCurrency(summary.net_cash_flow) }}
                        </h3>
                        <p class="text-xs text-zinc-500">
                            Spending Ratio: {{ spendingRatio.toFixed(1) }}%
                        </p>
                    </div>
                    <div
                        class="absolute -right-6 -bottom-6 h-24 w-24 opacity-5"
                    >
                        <svg
                            viewBox="0 0 100 100"
                            class="h-full w-full fill-current text-purple-500"
                        >
                            <path d="M50 0 L100 50 L50 100 L0 50 Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Chart - Full Width -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6 lg:col-span-2"
                >
                    <div
                        class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div>
                            <h3 class="text-lg font-semibold text-white">
                                Cash Flow Analysis
                            </h3>
                            <p class="text-sm text-zinc-500">
                                Income vs Expense trends
                            </p>
                        </div>

                        <div
                            class="flex flex-col items-start gap-3 sm:flex-row sm:items-center"
                        >
                            <!-- Time Range -->
                            <div class="flex rounded-lg bg-zinc-900/50 p-1">
                                <button
                                    @click="handlePeriodChange('7d')"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        timeRange === '7d'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                    :disabled="isLoading"
                                >
                                    7D
                                </button>
                                <button
                                    @click="handlePeriodChange('30d')"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        timeRange === '30d'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                    :disabled="isLoading"
                                >
                                    30D
                                </button>
                                <button
                                    @click="handlePeriodChange('90d')"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        timeRange === '90d'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                    :disabled="isLoading"
                                >
                                    90D
                                </button>
                                <button
                                    @click="handlePeriodChange('current_month')"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        timeRange ===
                                        ('current_month' as PeriodType)
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                    :disabled="isLoading"
                                >
                                    Month
                                </button>
                                <button
                                    @click="handlePeriodChange('current_year')"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        timeRange === 'current_year'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                    :disabled="isLoading"
                                >
                                    Year
                                </button>
                            </div>

                            <!-- Chart Type Selector -->
                            <div class="flex rounded-lg bg-zinc-900/50 p-1">
                                <button
                                    @click="activeChart = 'line'"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        activeChart === 'line'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                >
                                    Line
                                </button>
                                <button
                                    @click="activeChart = 'area'"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        activeChart === 'area'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                >
                                    Area
                                </button>
                                <button
                                    @click="activeChart = 'bar'"
                                    class="rounded-md px-3 py-1.5 text-sm transition-all"
                                    :class="
                                        activeChart === 'bar'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                >
                                    Bar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Legend -->
                    <div class="mb-6 flex flex-wrap items-center gap-4">
                        <button
                            @click="showIncome = !showIncome"
                            class="flex items-center gap-2 rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                showIncome
                                    ? 'bg-green-500/10'
                                    : 'bg-zinc-800/50'
                            "
                        >
                            <div
                                class="h-3 w-3 rounded-full"
                                :class="
                                    showIncome ? 'bg-green-500' : 'bg-zinc-600'
                                "
                            ></div>
                            <span
                                class="text-sm font-medium"
                                :class="
                                    showIncome
                                        ? 'text-green-500'
                                        : 'text-zinc-500'
                                "
                                >Income</span
                            >
                            <span class="ml-1 text-xs text-zinc-500"
                                >({{
                                    formatCompactCurrency(summary.total_income)
                                }})</span
                            >
                        </button>
                        <button
                            @click="showExpense = !showExpense"
                            class="flex items-center gap-2 rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                showExpense ? 'bg-red-500/10' : 'bg-zinc-800/50'
                            "
                        >
                            <div
                                class="h-3 w-3 rounded-full"
                                :class="
                                    showExpense ? 'bg-red-500' : 'bg-zinc-600'
                                "
                            ></div>
                            <span
                                class="text-sm font-medium"
                                :class="
                                    showExpense
                                        ? 'text-red-500'
                                        : 'text-zinc-500'
                                "
                                >Expense</span
                            >
                            <span class="ml-1 text-xs text-zinc-500"
                                >({{
                                    formatCompactCurrency(
                                        summary.total_expense,
                                    )
                                }})</span
                            >
                        </button>
                        <button
                            @click="showNet = !showNet"
                            class="flex items-center gap-2 rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                showNet ? 'bg-purple-500/10' : 'bg-zinc-800/50'
                            "
                        >
                            <div
                                class="h-3 w-3 rounded-full"
                                :class="
                                    showNet ? 'bg-purple-500' : 'bg-zinc-600'
                                "
                            ></div>
                            <span
                                class="text-sm font-medium"
                                :class="
                                    showNet
                                        ? 'text-purple-500'
                                        : 'text-zinc-500'
                                "
                                >Net Flow</span
                            >
                            <span class="ml-1 text-xs text-zinc-500"
                                >({{ summary.total_balance >= 0 ? '+' : ''
                                }}{{
                                    formatCompactCurrency(
                                        summary.total_balance,
                                    )
                                }})</span
                            >
                        </button>
                    </div>

                    <!-- Chart Container -->
                    <div class="relative">
                        <div
                            class="relative h-[280px] w-full cursor-crosshair"
                            @mousemove="handleChartHover"
                            @mouseleave="handleChartLeave"
                        >
                            <!-- Grid Lines -->
                            <div
                                class="pointer-events-none absolute inset-0 flex flex-col justify-between opacity-20"
                            >
                                <div
                                    class="h-px w-full bg-gradient-to-r from-transparent via-zinc-700 to-transparent"
                                ></div>
                                <div
                                    class="h-px w-full bg-gradient-to-r from-transparent via-zinc-700 to-transparent"
                                ></div>
                                <div
                                    class="h-px w-full bg-gradient-to-r from-transparent via-zinc-700 to-transparent"
                                ></div>
                                <div
                                    class="h-px w-full bg-gradient-to-r from-transparent via-zinc-700 to-transparent"
                                ></div>
                                <div
                                    class="h-px w-full bg-gradient-to-r from-transparent via-zinc-700 to-transparent"
                                ></div>
                            </div>

                            <!-- Chart -->
                            <svg
                                class="h-full w-full overflow-visible"
                                viewBox="0 0 100 100"
                                preserveAspectRatio="none"
                            >
                                <defs>
                                    <linearGradient
                                        id="incomeGradient"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#22c55e"
                                            stop-opacity="0.3"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#22c55e"
                                            stop-opacity="0"
                                        />
                                    </linearGradient>
                                    <linearGradient
                                        id="expenseGradient"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#ef4444"
                                            stop-opacity="0.3"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#ef4444"
                                            stop-opacity="0"
                                        />
                                    </linearGradient>
                                    <linearGradient
                                        id="netGradient"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stop-color="#8b5cf6"
                                            stop-opacity="0.3"
                                        />
                                        <stop
                                            offset="100%"
                                            stop-color="#8b5cf6"
                                            stop-opacity="0"
                                        />
                                    </linearGradient>
                                </defs>

                                <!-- Area Charts -->
                                <template v-if="activeChart === 'area'">
                                    <path
                                        v-if="showExpense"
                                        :d="getAreaPath('expense')"
                                        fill="url(#expenseGradient)"
                                        class="transition-all duration-500"
                                    />
                                    <path
                                        v-if="showIncome"
                                        :d="getAreaPath('income')"
                                        fill="url(#incomeGradient)"
                                        class="transition-all duration-500"
                                    />
                                </template>

                                <!-- Line Charts -->
                                <template v-if="activeChart === 'line'">
                                    <path
                                        v-if="showExpense"
                                        :d="getLinePath('expense')"
                                        fill="none"
                                        stroke="#ef4444"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="drop-shadow-[0_0_8px_rgba(239,68,68,0.3)] transition-all duration-500"
                                    />
                                    <path
                                        v-if="showIncome"
                                        :d="getLinePath('income')"
                                        fill="none"
                                        stroke="#22c55e"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="drop-shadow-[0_0_8px_rgba(34,197,94,0.3)] transition-all duration-500"
                                    />
                                    <path
                                        v-if="showNet"
                                        :d="getLinePath('net')"
                                        fill="none"
                                        stroke="#8b5cf6"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="drop-shadow-[0_0_8px_rgba(139,92,246,0.3)] transition-all duration-500"
                                    />
                                </template>

                                <!-- Bar Charts -->
                                <template v-if="activeChart === 'bar'">
                                    <g v-if="showExpense">
                                        <rect
                                            v-for="(
                                                bar, index
                                            ) in getBarPositions('expense')"
                                            :key="'expense-' + index"
                                            :x="bar.x"
                                            :y="bar.y"
                                            :width="bar.width"
                                            :height="bar.height"
                                            fill="#ef4444"
                                            class="opacity-70 transition-all duration-300 hover:opacity-100"
                                        />
                                    </g>
                                    <g v-if="showIncome">
                                        <rect
                                            v-for="(
                                                bar, index
                                            ) in getBarPositions('income')"
                                            :key="'income-' + index"
                                            :x="bar.x"
                                            :y="bar.y"
                                            :width="bar.width"
                                            :height="bar.height"
                                            fill="#22c55e"
                                            class="opacity-70 transition-all duration-300 hover:opacity-100"
                                        />
                                    </g>
                                </template>

                                <!-- Hover Line -->
                                <line
                                    v-if="hoveredData"
                                    :x1="hoverX"
                                    y1="0"
                                    :x2="hoverX"
                                    y2="100"
                                    stroke="#52525b"
                                    stroke-width="1"
                                    stroke-dasharray="4 2"
                                    class="transition-all duration-100"
                                />
                            </svg>

                            <!-- Hover Tooltip -->
                            <div
                                v-if="hoveredData"
                                class="pointer-events-none absolute top-0 z-10 min-w-[180px] rounded-xl border border-zinc-800 bg-zinc-900/95 p-4 shadow-2xl backdrop-blur-sm transition-all duration-100"
                                :style="{
                                    left: `${hoverX}%`,
                                    transform:
                                        hoverX > 50
                                            ? 'translateX(-100%) translateY(-110%)'
                                            : 'translateY(-110%)',
                                }"
                            >
                                <div class="space-y-3">
                                    <div class="border-b border-zinc-800 pb-2">
                                        <p
                                            class="text-xs font-medium text-zinc-400"
                                        >
                                            {{ formatDate(hoveredData.date) }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            Daily Summary
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="h-2 w-2 rounded-full bg-green-500"
                                                ></div>
                                                <span
                                                    class="text-xs text-zinc-400"
                                                    >Income</span
                                                >
                                            </div>
                                            <span
                                                class="text-sm font-medium text-green-500"
                                                >{{
                                                    formatCurrency(
                                                        hoveredData.income,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="h-2 w-2 rounded-full bg-red-500"
                                                ></div>
                                                <span
                                                    class="text-xs text-zinc-400"
                                                    >Expense</span
                                                >
                                            </div>
                                            <span
                                                class="text-sm font-medium text-red-500"
                                                >{{
                                                    formatCurrency(
                                                        hoveredData.expense,
                                                    )
                                                }}</span
                                            >
                                        </div>

                                        <div
                                            class="flex items-center justify-between border-t border-zinc-800 pt-2"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="h-2 w-2 rounded-full bg-purple-500"
                                                ></div>
                                                <span
                                                    class="text-xs text-zinc-400"
                                                    >Net</span
                                                >
                                            </div>
                                            <span
                                                class="text-sm font-medium"
                                                :class="
                                                    hoveredData.net >= 0
                                                        ? 'text-green-500'
                                                        : 'text-red-500'
                                                "
                                            >
                                                {{
                                                    hoveredData.net >= 0
                                                        ? '+'
                                                        : ''
                                                }}{{
                                                    formatCurrency(
                                                        hoveredData.net,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No Data Message -->
                            <div
                                v-if="
                                    enhancedChartData.length === 0 ||
                                    enhancedChartData.every(
                                        (d) =>
                                            d.income === 0 && d.expense === 0,
                                    )
                                "
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <div class="p-8 text-center">
                                    <BarChart3
                                        class="mx-auto mb-4 h-12 w-12 text-zinc-700"
                                    />
                                    <p class="font-medium text-zinc-500">
                                        No transaction data available
                                    </p>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        Add transactions to see your cash flow
                                        analysis
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar - Categories & Platforms -->
                <div class="space-y-6">
                    <!-- Spending Categories -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-gradient-to-br from-pink-600 to-pink-800 p-2"
                                >
                                    <PieChart class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">
                                        Spending Categories
                                    </h3>
                                    <p class="text-sm text-zinc-500">
                                        Where your money goes
                                    </p>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-zinc-800 px-2 py-1 text-xs text-zinc-300"
                            >
                                {{ spending_categories.length }} categories
                            </span>
                        </div>

                        <div
                            class="space-y-4"
                            v-if="spending_categories.length > 0"
                        >
                            <div
                                v-for="category in spending_categories"
                                :key="category.name"
                                class="space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-3 w-3 rounded-full"
                                            :style="{
                                                backgroundColor: category.color,
                                            }"
                                        ></div>
                                        <div>
                                            <span
                                                class="text-sm font-medium text-zinc-300"
                                                >{{ category.name }}</span
                                            >
                                            <p class="text-xs text-zinc-500">
                                                {{
                                                    category.transaction_count
                                                }}
                                                transactions
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-sm font-medium text-white"
                                        >
                                            {{
                                                formatCompactCurrency(
                                                    category.amount,
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ category.percentage }}% of
                                            expenses
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="h-2 overflow-hidden rounded-full bg-zinc-800"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{
                                            width: `${category.percentage}%`,
                                            backgroundColor: category.color,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <PieChart
                                class="mx-auto mb-3 h-10 w-10 text-zinc-700"
                            />
                            <p class="text-zinc-500">
                                No spending data available
                            </p>
                            <p class="mt-1 text-sm text-zinc-600">
                                Add expense transactions to see categories
                            </p>
                        </div>

                        <div class="mt-6 border-t border-zinc-800 pt-4">
                            <a
                                href="/transactions"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-800 py-2.5 text-sm font-medium text-zinc-300 transition-colors hover:bg-zinc-800 hover:text-white"
                            >
                                <Eye class="h-4 w-4" />
                                View All Transactions
                            </a>
                        </div>
                    </div>

                    <!-- Platform Usage -->
                    <div
                        class="rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-gradient-to-br from-blue-600 to-blue-800 p-2"
                                >
                                    <CreditCard class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">
                                        Platform Usage
                                    </h3>
                                    <p class="text-sm text-zinc-500">
                                        Top payment methods for expenses
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4" v-if="platform_usage.length > 0">
                            <div
                                v-for="platform in platform_usage"
                                :key="platform.name"
                                class="group flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-zinc-800/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-lg p-2"
                                        :style="{
                                            backgroundColor:
                                                platform.color + '20',
                                        }"
                                    >
                                        <component
                                            :is="
                                                getIconComponent(platform.icon)
                                            "
                                            class="h-4 w-4"
                                            :style="{ color: platform.color }"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-white"
                                        >
                                            {{ platform.name }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{
                                                platform.transaction_count
                                            }}
                                            expense transactions
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-white">
                                        {{
                                            formatCompactCurrency(
                                                platform.amount,
                                            )
                                        }}
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        {{ platform.expense_percentage }}% of
                                        expenses
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <CreditCard
                                class="mx-auto mb-3 h-10 w-10 text-zinc-700"
                            />
                            <p class="text-zinc-500">No platform usage data</p>
                            <p class="mt-1 text-sm text-zinc-600">
                                Add expense transactions to see platform usage
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section - Quick Actions & Insights -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Quick Actions -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="font-semibold text-white">Quick Actions</h3>
                        <span
                            class="rounded-full bg-zinc-800 px-2 py-1 text-xs text-zinc-300"
                        >
                            Export Tools
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <a
                            href="/transactions"
                            class="group rounded-xl border border-zinc-800 bg-zinc-900/50 p-4 transition-all hover:border-zinc-700 hover:bg-zinc-800"
                        >
                            <div
                                class="flex flex-col items-center gap-2 text-center"
                            >
                                <div
                                    class="rounded-lg bg-gradient-to-br from-orange-600 to-orange-700 p-2 transition-transform group-hover:scale-110"
                                >
                                    <BarChart3 class="h-5 w-5 text-white" />
                                </div>
                                <p class="text-sm font-medium text-white">
                                    View Transactions
                                </p>
                                <p class="text-xs text-zinc-500">
                                    See all records
                                </p>
                            </div>
                        </a>
                        <button
                            @click="exportCSV"
                            class="group rounded-xl border border-zinc-800 bg-zinc-900/50 p-4 transition-all hover:border-zinc-700 hover:bg-zinc-800"
                        >
                            <div
                                class="flex flex-col items-center gap-2 text-center"
                            >
                                <div
                                    class="rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 p-2 transition-transform group-hover:scale-110"
                                >
                                    <FileSpreadsheet
                                        class="h-5 w-5 text-white"
                                    />
                                </div>
                                <p class="text-sm font-medium text-white">
                                    Export CSV
                                </p>
                                <p class="text-xs text-zinc-500">
                                    Download data
                                </p>
                            </div>
                        </button>
                        <button
                            @click="exportPDF"
                            class="group rounded-xl border border-zinc-800 bg-zinc-900/50 p-4 transition-all hover:border-zinc-700 hover:bg-zinc-800"
                        >
                            <div
                                class="flex flex-col items-center gap-2 text-center"
                            >
                                <div
                                    class="rounded-lg bg-gradient-to-br from-green-600 to-green-700 p-2 transition-transform group-hover:scale-110"
                                >
                                    <FileText class="h-5 w-5 text-white" />
                                </div>
                                <p class="text-sm font-medium text-white">
                                    Export PDF
                                </p>
                                <p class="text-xs text-zinc-500">
                                    Printable report
                                </p>
                            </div>
                        </button>
                        <button
                            @click="exportJSON"
                            class="group rounded-xl border border-zinc-800 bg-zinc-900/50 p-4 transition-all hover:border-zinc-700 hover:bg-zinc-800"
                        >
                            <div
                                class="flex flex-col items-center gap-2 text-center"
                            >
                                <div
                                    class="rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 p-2 transition-transform group-hover:scale-110"
                                >
                                    <FileJson class="h-5 w-5 text-white" />
                                </div>
                                <p class="text-sm font-medium text-white">
                                    Export JSON
                                </p>
                                <p class="text-xs text-zinc-500">
                                    For developers
                                </p>
                            </div>
                        </button>
                    </div>

                    <!-- Quick Export Options -->
                    <div class="mt-6 border-t border-zinc-800 pt-6">
                        <p class="mb-3 text-sm font-medium text-zinc-400">
                            Quick Export for:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="quickExport('pdf', '7d')"
                                class="rounded-lg border border-zinc-800 bg-zinc-900/50 px-3 py-1.5 text-xs transition-colors hover:bg-zinc-800"
                            >
                                7D Report
                            </button>
                            <button
                                @click="quickExport('pdf', '30d')"
                                class="rounded-lg border border-zinc-800 bg-zinc-900/50 px-3 py-1.5 text-xs transition-colors hover:bg-zinc-800"
                            >
                                30D Report
                            </button>
                            <button
                                @click="quickExport('pdf', '90d')"
                                class="rounded-lg border border-zinc-800 bg-zinc-900/50 px-3 py-1.5 text-xs transition-colors hover:bg-zinc-800"
                            >
                                90D Report
                            </button>
                            <button
                                @click="exportCurrentMonth()"
                                class="rounded-lg border border-zinc-800 bg-zinc-900/50 px-3 py-1.5 text-xs transition-colors hover:bg-zinc-800"
                            >
                                {{
                                    monthFilterType === 'specific'
                                        ? 'Selected Month'
                                        : 'Current Month'
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Financial Insights -->
                <div
                    class="rounded-2xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-black p-6 lg:col-span-2"
                >
                    <div
                        class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-gradient-to-br from-indigo-600 to-indigo-800 p-2"
                            >
                                <Lightbulb class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">
                                    Financial Insights
                                </h3>
                                <p class="text-sm text-zinc-500">
                                    Smart recommendations based on your data
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div
                                class="rounded-full bg-zinc-800 px-2 py-1 text-xs"
                            >
                                <span class="text-zinc-300"
                                    >{{ insights.length }} insights</span
                                >
                            </div>
                            <div class="flex rounded-lg bg-zinc-900/50 p-1">
                                <button
                                    @click="selectedInsightFilter = 'all'"
                                    class="rounded-md px-3 py-1 text-xs transition-all"
                                    :class="
                                        selectedInsightFilter === 'all'
                                            ? 'bg-zinc-800 text-white'
                                            : 'text-zinc-400 hover:text-white'
                                    "
                                >
                                    All
                                </button>
                                <button
                                    @click="selectedInsightFilter = 'positive'"
                                    class="rounded-md px-3 py-1 text-xs transition-all"
                                    :class="
                                        selectedInsightFilter === 'positive'
                                            ? 'bg-green-500/20 text-green-500'
                                            : 'text-zinc-400 hover:text-green-500'
                                    "
                                >
                                    Positive
                                </button>
                                <button
                                    @click="selectedInsightFilter = 'warning'"
                                    class="rounded-md px-3 py-1 text-xs transition-all"
                                    :class="
                                        selectedInsightFilter === 'warning'
                                            ? 'bg-yellow-500/20 text-yellow-500'
                                            : 'text-zinc-400 hover:text-yellow-500'
                                    "
                                >
                                    Warning
                                </button>
                                <button
                                    @click="selectedInsightFilter = 'advice'"
                                    class="rounded-md px-3 py-1 text-xs transition-all"
                                    :class="
                                        selectedInsightFilter === 'advice'
                                            ? 'bg-blue-500/20 text-blue-500'
                                            : 'text-zinc-400 hover:text-blue-500'
                                    "
                                >
                                    Advice
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4" v-if="filteredInsights.length > 0">
                        <div
                            v-for="(insight, index) in filteredInsights"
                            :key="index"
                            class="group flex items-start gap-4 rounded-xl border border-zinc-800 p-4 transition-colors hover:bg-zinc-800/30"
                        >
                            <div
                                class="mt-1 rounded-lg p-2"
                                :class="{
                                    'bg-gradient-to-br from-green-600 to-green-800':
                                        insight.color === 'green' ||
                                        insight.type === 'positive',
                                    'bg-gradient-to-br from-yellow-600 to-yellow-800':
                                        insight.color === 'yellow' ||
                                        insight.type === 'warning',
                                    'bg-gradient-to-br from-blue-600 to-blue-800':
                                        insight.color === 'blue' ||
                                        insight.type === 'advice',
                                    'bg-gradient-to-br from-gray-600 to-gray-800':
                                        insight.color === 'gray' ||
                                        insight.type === 'neutral',
                                }"
                            >
                                <component
                                    :is="getIconComponent(insight.icon)"
                                    class="h-5 w-5 text-white"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="mb-1 flex flex-col justify-between gap-2 sm:flex-row sm:items-center"
                                >
                                    <h4 class="truncate font-medium text-white">
                                        {{ insight.title }}
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded-full px-2 py-1 text-xs whitespace-nowrap"
                                            :class="{
                                                'bg-green-500/20 text-green-500':
                                                    insight.type === 'positive',
                                                'bg-yellow-500/20 text-yellow-500':
                                                    insight.type === 'warning',
                                                'bg-blue-500/20 text-blue-500':
                                                    insight.type === 'advice',
                                                'bg-gray-500/20 text-gray-500':
                                                    insight.type === 'neutral',
                                            }"
                                        >
                                            {{
                                                insight.type === 'positive'
                                                    ? 'Positive'
                                                    : insight.type === 'warning'
                                                      ? 'Warning'
                                                      : insight.type ===
                                                          'advice'
                                                        ? 'Advice'
                                                        : 'Info'
                                            }}
                                        </span>
                                        <span
                                            v-if="insight.metric"
                                            class="text-xs text-zinc-500"
                                        >
                                            {{ insight.metric }}%
                                        </span>
                                    </div>
                                </div>
                                <p class="text-sm break-words text-zinc-400">
                                    {{ insight.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <Lightbulb
                            class="mx-auto mb-3 h-10 w-10 text-zinc-700"
                        />
                        <p class="text-zinc-500">
                            No insights available for this filter
                        </p>
                        <p class="mt-1 text-sm text-zinc-600">
                            Add more transactions to generate insights
                        </p>
                    </div>

                    <!-- Insight Summary -->
                    <div class="mt-6 border-t border-zinc-800 pt-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-zinc-500">
                                Showing {{ filteredInsights.length }} of
                                {{ insights.length }} insights
                            </div>
                            <div
                                class="flex items-center gap-1 text-xs text-zinc-500"
                            >
                                <div
                                    class="h-2 w-2 rounded-full bg-green-500"
                                ></div>
                                <span
                                    >{{
                                        insights.filter(
                                            (i) => i.type === 'positive',
                                        ).length
                                    }}
                                    Positive</span
                                >
                                <div
                                    class="ml-2 h-2 w-2 rounded-full bg-yellow-500"
                                ></div>
                                <span
                                    >{{
                                        insights.filter(
                                            (i) => i.type === 'warning',
                                        ).length
                                    }}
                                    Warning</span
                                >
                                <div
                                    class="ml-2 h-2 w-2 rounded-full bg-blue-500"
                                ></div>
                                <span
                                    >{{
                                        insights.filter(
                                            (i) => i.type === 'advice',
                                        ).length
                                    }}
                                    Advice</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
