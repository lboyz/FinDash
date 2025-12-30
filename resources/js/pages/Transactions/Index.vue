<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowUpRight,
    Calendar,
    ChevronLeft,
    ChevronRight,
    CreditCard,
    Download,
    FileSpreadsheet,
    FileText,
    FileType,
    Filter,
    Pencil,
    Search,
    Trash2,
    TrendingDown,
    TrendingUp,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface Platform {
    id: number;
    name: string;
}

interface Transaction {
    id: number;
    date: string;
    category: 'income' | 'expense';
    platform: Platform;
    type: string;
    description: string | null;
    amount: number;
    attachment: string | null;
    formatted_amount: string;
}

interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Statistics {
    total_income: number;
    total_expenses: number;
    net_balance: number;
    formatted_total_income: string;
    formatted_total_expenses: string;
    formatted_net_balance: string;
}

const props = defineProps<{
    transactions: PaginatedTransactions;
    platforms: Platform[];
    filters: {
        start_date?: string;
        end_date?: string;
        category?: string;
        platform_id?: number;
        type?: string;
        description?: string;
    };
    types: string[];
    transaction_types: Record<string, string[]>;
    statistics: Statistics; // Tambahkan prop statistics
}>();

const breadcrumbs = [{ title: 'Transactions', href: '/transactions' }];

// State
const showAddModal = ref(false);
const showFilters = ref(false);
const showEditModal = ref(false);
const selectedCategory = ref<'income' | 'expense' | null>(null);
const editingTransaction = ref<Transaction | null>(null);

// Computed available types based on selected category
const availableTypes = computed(() => {
    const category = transactionForm.category;
    if (!category || !props.transaction_types) return [];
    return props.transaction_types[category] || [];
});

// Watch for category changes to reset type if needed
import { watch } from 'vue';
watch(
    () => transactionForm.category,
    (newCategory, oldCategory) => {
        if (oldCategory && newCategory !== oldCategory) {
            // Only reset if we're not in edit mode with the initial type
            if (
                !editingTransaction.value ||
                editingTransaction.value.category !== newCategory
            ) {
                transactionForm.type = '';
            }
        }
    },
);

// Fungsi untuk mendapatkan tanggal awal bulan dan tanggal saat ini
const getDefaultDates = () => {
    const now = new Date();
    const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);

    const formatJakartaDate = (date: Date) => {
        return date.toLocaleDateString('en-CA', {
            timeZone: 'Asia/Jakarta',
        });
    };

    return {
        start_date: formatJakartaDate(firstDayOfMonth),
        end_date: formatJakartaDate(now),
    };
};

// Forms
const filterForm = useForm({
    start_date: props.filters.start_date || getDefaultDates().start_date,
    end_date: props.filters.end_date || getDefaultDates().end_date,
    category: props.filters.category || '',
    platform_id: props.filters.platform_id || '',
    type: props.filters.type || '',
    description: props.filters.description || '',
});

const transactionForm = useForm({
    date: new Date().toISOString().split('T')[0],
    category: 'income' as 'income' | 'expense',
    platform_id: '',
    type: '',
    description: '',
    amount: '',
    attachment: null as File | null,
    remove_attachment: false,
});

// Computed properties menggunakan statistics dari backend
const totalIncome = computed(() => {
    return props.statistics?.total_income || 0;
});

const totalExpenses = computed(() => {
    return props.statistics?.total_expenses || 0;
});

const netBalance = computed(() => {
    return props.statistics?.net_balance || 0;
});

// Helper functions
const formatCurrency = (amount: number) => {
    try {
        if (typeof amount !== 'number' || isNaN(amount) || !isFinite(amount)) {
            amount = 0;
        }
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount);
    } catch {
        return 'Rp 0';
    }
};

const formatDate = (dateString: string) => {
    try {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
        });
    } catch {
        return 'N/A';
    }
};

const getDayOfMonth = (dateString: string) => {
    try {
        if (!dateString) return '--';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '--';
        return date.getDate();
    } catch {
        return '--';
    }
};

const getMonthYear = (dateString: string) => {
    try {
        if (!dateString) return '---';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '---';
        return date.toLocaleDateString('en-US', {
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return '---';
    }
};

// Actions
const applyFilters = () =>
    filterForm.get('/transactions', {
        preserveState: true,
        preserveScroll: true,
    });
const resetFilters = () => (window.location.href = '/transactions');

// Modal Actions
const openAddModal = (category: 'income' | 'expense') => {
    selectedCategory.value = category;
    transactionForm.reset();
    transactionForm.category = category;
    transactionForm.date = new Date().toISOString().split('T')[0];
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    selectedCategory.value = null;
    transactionForm.reset();
    transactionForm.date = new Date().toISOString().split('T')[0];
};

const submitTransaction = () => {
    const formData = new FormData();
    formData.append('date', transactionForm.date);
    formData.append('category', transactionForm.category);
    formData.append('platform_id', transactionForm.platform_id.toString());
    formData.append('type', transactionForm.type);
    formData.append('description', transactionForm.description);
    formData.append('amount', transactionForm.amount.toString());

    if (transactionForm.attachment) {
        formData.append('attachment', transactionForm.attachment);
    }

    router.post('/transactions', formData, {
        preserveScroll: true,
        onSuccess: () => {
            closeAddModal();
        },
    });
};

const openEditModal = (t: Transaction) => {
    editingTransaction.value = t;
    transactionForm.date = t.date;
    transactionForm.category = t.category;
    transactionForm.platform_id = t.platform.id.toString();
    transactionForm.type = t.type;
    transactionForm.description = t.description || '';
    transactionForm.amount = t.amount.toString();
    transactionForm.remove_attachment = false; // Reset removal flag
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingTransaction.value = null;
    transactionForm.reset();
    transactionForm.date = new Date().toISOString().split('T')[0];
};

const updateTransaction = () => {
    if (!editingTransaction.value) return;

    const formData = new FormData();
    formData.append('_method', 'PUT'); // Spoof PUT method for FormData
    formData.append('date', transactionForm.date);
    formData.append('category', transactionForm.category);
    formData.append('platform_id', transactionForm.platform_id.toString());
    formData.append('type', transactionForm.type);
    formData.append('description', transactionForm.description);
    formData.append('amount', transactionForm.amount.toString());

    if (transactionForm.attachment) {
        formData.append('attachment', transactionForm.attachment);
    }

    if (transactionForm.remove_attachment) {
        formData.append('remove_attachment', '1');
    }

    router.post(`/transactions/${editingTransaction.value.id}`, formData, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const deleteTransaction = (id: number) => {
    if (confirm('Delete this transaction?')) {
        router.delete(`/transactions/${id}`, { preserveScroll: true });
    }
};

const exportCSV = () => {
    const params = new URLSearchParams(filterForm.data() as any);
    window.location.href = `/export/csv?${params.toString()}`;
};

const exportPDF = () => {
    const params = new URLSearchParams(filterForm.data() as any);
    window.open(`/export/pdf?${params.toString()}`, '_blank');
};

// Set tanggal default saat komponen dimuat
onMounted(() => {
    if (!props.filters.start_date) {
        filterForm.start_date = getDefaultDates().start_date;
    }
    if (!props.filters.end_date) {
        filterForm.end_date = getDefaultDates().end_date;
    }
});
</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-6 p-6">
            <!-- Header dengan Statistik -->
            <div class="flex flex-col space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2
                            class="text-2xl font-semibold tracking-tight text-white"
                        >
                            Transactions
                        </h2>
                        <p class="mt-1 text-sm text-zinc-400">
                            Manage your financial records
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            class="h-9 border-zinc-800 bg-black/50 text-zinc-300 hover:bg-zinc-900 hover:text-white"
                            @click="showFilters = !showFilters"
                        >
                            <Filter class="mr-2 h-4 w-4" />
                            {{ showFilters ? 'Hide' : 'Filter' }}
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="outline"
                                    class="h-9 border-zinc-800 bg-black/50 text-zinc-300 hover:bg-zinc-900 hover:text-white"
                                >
                                    <Download class="mr-2 h-4 w-4" />
                                    Export
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="w-48 border-zinc-800 bg-zinc-950"
                            >
                                <DropdownMenuItem
                                    @click="exportCSV"
                                    class="cursor-pointer text-zinc-300 hover:bg-zinc-900 hover:text-white"
                                >
                                    <FileSpreadsheet class="mr-2 h-4 w-4" />
                                    CSV
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="exportPDF"
                                    class="cursor-pointer text-zinc-300 hover:bg-zinc-900 hover:text-white"
                                >
                                    <FileText class="mr-2 h-4 w-4" />
                                    PDF
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Button
                            class="h-9 bg-gradient-to-r from-orange-600 to-orange-500 text-white shadow-lg shadow-orange-900/25 hover:from-orange-700 hover:to-orange-600"
                            @click="showAddModal = true"
                        >
                            + Add
                        </Button>
                    </div>
                </div>

                <!-- Statistik Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div
                        class="rounded-xl border border-green-800/20 bg-gradient-to-br from-green-500/10 to-green-900/5 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-400">
                                    Total Income
                                </p>
                                <p class="mt-1 text-2xl font-bold text-white">
                                    {{ formatCurrency(totalIncome) }}
                                </p>
                                <p class="mt-1 text-xs text-green-500/70">
                                    Based on filtered period
                                </p>
                            </div>
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500/20"
                            >
                                <TrendingUp class="h-5 w-5 text-green-400" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-red-800/20 bg-gradient-to-br from-red-500/10 to-red-900/5 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-400">
                                    Total Expenses
                                </p>
                                <p class="mt-1 text-2xl font-bold text-white">
                                    {{ formatCurrency(totalExpenses) }}
                                </p>
                                <p class="mt-1 text-xs text-red-500/70">
                                    Based on filtered period
                                </p>
                            </div>
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/20"
                            >
                                <TrendingDown class="h-5 w-5 text-red-400" />
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-blue-800/20 bg-gradient-to-br from-blue-500/10 to-blue-900/5 p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-400">
                                    Net Balance
                                </p>
                                <p class="mt-1 text-2xl font-bold text-white">
                                    {{ formatCurrency(netBalance) }}
                                </p>
                                <p class="mt-1 text-xs text-blue-500/70">
                                    Income - Expenses
                                </p>
                            </div>
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/20"
                            >
                                <Wallet class="h-5 w-5 text-blue-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div
                    v-if="showFilters"
                    class="space-y-4 rounded-xl border border-zinc-800/50 bg-gradient-to-b from-zinc-900/50 to-zinc-950/30 p-5 backdrop-blur-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Filter class="h-4 w-4 text-zinc-500" />
                            <h3 class="text-sm font-medium text-zinc-300">
                                Filter Transactions
                            </h3>
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <div class="space-y-2">
                            <Label
                                class="flex items-center gap-2 text-xs font-medium text-zinc-400"
                            >
                                <Calendar class="h-3 w-3" />
                                Date Range
                            </Label>
                            <div class="grid grid-cols-2 gap-2">
                                <Input
                                    type="date"
                                    v-model="filterForm.start_date"
                                    class="h-9 border-zinc-800 bg-black/50 text-sm text-white"
                                />
                                <Input
                                    type="date"
                                    v-model="filterForm.end_date"
                                    class="h-9 border-zinc-800 bg-black/50 text-sm text-white"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-medium text-zinc-400"
                                >Category</Label
                            >
                            <div class="flex gap-2">
                                <Button
                                    @click="filterForm.category = ''"
                                    :variant="
                                        filterForm.category === ''
                                            ? 'default'
                                            : 'outline'
                                    "
                                    size="sm"
                                    class="h-8 text-xs"
                                    :class="
                                        filterForm.category === ''
                                            ? 'bg-zinc-800'
                                            : 'border-zinc-800 text-zinc-400'
                                    "
                                >
                                    All
                                </Button>
                                <Button
                                    @click="filterForm.category = 'income'"
                                    :variant="
                                        filterForm.category === 'income'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    size="sm"
                                    class="h-8 text-xs"
                                    :class="
                                        filterForm.category === 'income'
                                            ? 'border-green-800/30 bg-green-600/20 text-green-400'
                                            : 'border-zinc-800 text-zinc-400'
                                    "
                                >
                                    Income
                                </Button>
                                <Button
                                    @click="filterForm.category = 'expense'"
                                    :variant="
                                        filterForm.category === 'expense'
                                            ? 'default'
                                            : 'outline'
                                    "
                                    size="sm"
                                    class="h-8 text-xs"
                                    :class="
                                        filterForm.category === 'expense'
                                            ? 'border-red-800/30 bg-red-600/20 text-red-400'
                                            : 'border-zinc-800 text-zinc-400'
                                    "
                                >
                                    Expense
                                </Button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="flex items-center gap-2 text-xs font-medium text-zinc-400"
                            >
                                <CreditCard class="h-3 w-3" />
                                Platform
                            </Label>
                            <select
                                v-model="filterForm.platform_id"
                                class="h-9 w-full rounded-md border border-zinc-800 bg-black/50 px-3 text-sm text-white focus:border-orange-600/50 focus:ring-1 focus:ring-orange-600/50"
                            >
                                <option value="">All Platforms</option>
                                <option
                                    v-for="p in platforms"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="flex items-center gap-2 text-xs font-medium text-zinc-400"
                            >
                                <FileType class="h-3 w-3" />
                                Type
                            </Label>
                            <select
                                v-model="filterForm.type"
                                class="h-9 w-full rounded-md border border-zinc-800 bg-black/50 px-3 text-sm text-white focus:border-orange-600/50 focus:ring-1 focus:ring-orange-600/50"
                            >
                                <option value="">All Types</option>
                                <option v-for="t in types" :key="t" :value="t">
                                    {{ t }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="flex items-center gap-2 text-xs font-medium text-zinc-400"
                            >
                                <Search class="h-3 w-3" />
                                Search
                            </Label>
                            <Input
                                type="text"
                                v-model="filterForm.description"
                                placeholder="Search descriptions..."
                                class="h-9 border-zinc-800 bg-black/50 text-white"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="text-xs font-medium text-zinc-400 opacity-0"
                                >Actions</Label
                            >
                            <div class="flex gap-2">
                                <Button
                                    @click="applyFilters"
                                    class="h-9 flex-1 bg-gradient-to-r from-orange-600 to-orange-500 text-white hover:from-orange-700 hover:to-orange-600"
                                >
                                    Apply
                                </Button>
                                <Button
                                    @click="resetFilters"
                                    variant="outline"
                                    class="h-9 border-zinc-800 text-zinc-400 hover:bg-zinc-900 hover:text-white"
                                >
                                    Clear
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Transaction Cards Grid -->
            <div class="space-y-4">
                <!-- Header dengan total count -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-white">
                            Recent Transactions
                        </h3>
                        <p class="text-sm text-zinc-500">
                            {{ transactions?.total || 0 }} total records •
                            Showing {{ transactions?.from || 0 }}-{{
                                transactions?.to || 0
                            }}
                            • {{ transactions?.per_page || 16 }} per page
                        </p>
                    </div>
                </div>

                <!-- Cards Container -->
                <div
                    v-if="transactions?.data?.length"
                    class="grid grid-cols-1 gap-4 lg:grid-cols-2"
                >
                    <transition-group name="list">
                        <div
                            v-for="t in transactions.data"
                            :key="t.id"
                            class="group relative rounded-2xl border border-zinc-800/50 bg-gradient-to-br from-zinc-900/50 to-black/30 p-5 transition-all duration-300 hover:border-zinc-700/50 hover:bg-zinc-900/30"
                        >
                            <div
                                class="absolute inset-0 rounded-2xl bg-gradient-to-r from-transparent via-white/5 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                            ></div>

                            <div
                                class="relative flex items-start justify-between"
                            >
                                <!-- Left Section -->
                                <div class="flex items-start gap-4">
                                    <!-- Date Circle -->
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-xl"
                                            :class="
                                                t.category === 'income'
                                                    ? 'border border-green-800/30 bg-green-500/10'
                                                    : 'border border-red-800/30 bg-red-500/10'
                                            "
                                        >
                                            <div class="text-center">
                                                <div
                                                    class="text-lg font-bold text-white"
                                                >
                                                    {{ getDayOfMonth(t.date) }}
                                                </div>
                                                <div
                                                    class="text-[10px] tracking-wider uppercase"
                                                    :class="
                                                        t.category === 'income'
                                                            ? 'text-green-400'
                                                            : 'text-red-400'
                                                    "
                                                >
                                                    {{
                                                        getMonthYear(
                                                            t.date,
                                                        ).split(' ')[0]
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-2">
                                        <!-- Category & Type -->
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                                :class="
                                                    t.category === 'income'
                                                        ? 'bg-green-500/20 text-green-400'
                                                        : 'bg-red-500/20 text-red-400'
                                                "
                                            >
                                                <ArrowUpRight
                                                    v-if="
                                                        t.category === 'income'
                                                    "
                                                    class="h-3 w-3"
                                                />
                                                <ArrowDownLeft
                                                    v-if="
                                                        t.category === 'expense'
                                                    "
                                                    class="h-3 w-3"
                                                />
                                                {{
                                                    t.category === 'income'
                                                        ? 'Income'
                                                        : 'Expense'
                                                }}
                                            </span>
                                            <span
                                                class="text-xs font-medium text-zinc-500"
                                            >
                                                {{ t.type || 'Uncategorized' }}
                                            </span>
                                        </div>

                                        <!-- Platform & Description -->
                                        <div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <CreditCard
                                                    class="h-3.5 w-3.5 text-zinc-600"
                                                />
                                                <span
                                                    class="text-sm font-medium text-white"
                                                    >{{
                                                        t.platform?.name ||
                                                        'No Platform'
                                                    }}</span
                                                >
                                            </div>
                                            <p
                                                v-if="t.description"
                                                class="mt-1 line-clamp-1 text-sm text-zinc-400"
                                            >
                                                {{ t.description }}
                                            </p>
                                            <p
                                                v-else
                                                class="mt-1 text-sm text-zinc-600 italic"
                                            >
                                                No description
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Section -->
                                <div class="flex flex-col items-end">
                                    <!-- Amount -->
                                    <div
                                        class="text-xl font-bold"
                                        :class="
                                            t.category === 'income'
                                                ? 'text-green-400'
                                                : 'text-white'
                                        "
                                    >
                                        {{ t.category === 'income' ? '+' : '-'
                                        }}{{
                                            t.formatted_amount ||
                                            formatCurrency(t.amount || 0)
                                        }}
                                    </div>

                                    <!-- Formatted Amount -->
                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ formatDate(t.date) }}
                                    </div>

                                    <!-- Actions -->
                                    <div
                                        class="mt-4 flex items-center gap-1 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                    >
                                        <Button
                                            @click="openEditModal(t)"
                                            size="icon"
                                            variant="ghost"
                                            class="h-7 w-7 text-zinc-500 hover:bg-zinc-800/50 hover:text-white"
                                        >
                                            <Pencil class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            @click="deleteTransaction(t.id)"
                                            size="icon"
                                            variant="ghost"
                                            class="h-7 w-7 text-zinc-500 hover:bg-red-500/10 hover:text-red-500"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Border -->
                            <div
                                class="absolute right-0 bottom-0 left-0 h-0.5 scale-x-0 transform rounded-full transition-transform duration-300 group-hover:scale-x-100"
                                :class="
                                    t.category === 'income'
                                        ? 'bg-gradient-to-r from-green-500/50 to-transparent'
                                        : 'bg-gradient-to-r from-red-500/50 to-transparent'
                                "
                            ></div>
                        </div>
                    </transition-group>
                </div>

                <!-- Empty State -->
                <div v-else class="py-16 text-center">
                    <div class="mx-auto max-w-md space-y-4">
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-zinc-800 bg-zinc-900/50"
                        >
                            <CreditCard class="h-8 w-8 text-zinc-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-white">
                                No transactions found
                            </h3>
                            <p class="mt-1 text-zinc-500">
                                Start by adding your first transaction
                            </p>
                        </div>
                        <Button
                            @click="showAddModal = true"
                            class="mt-4 bg-gradient-to-r from-orange-600 to-orange-500 text-white hover:from-orange-700 hover:to-orange-600"
                        >
                            + Add Transaction
                        </Button>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        transactions?.data?.length &&
                        transactions?.last_page > 1
                    "
                    class="flex items-center justify-between border-t border-zinc-800/50 pt-6"
                >
                    <p class="text-sm text-zinc-500">
                        Page {{ transactions?.current_page || 1 }} of
                        {{ transactions?.last_page || 1 }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            :disabled="
                                !transactions?.current_page ||
                                transactions.current_page <= 1
                            "
                            @click="
                                router.get(
                                    `/transactions?page=${(transactions?.current_page || 1) - 1}`,
                                )
                            "
                            variant="outline"
                            size="sm"
                            class="h-9 w-9 border-zinc-800 bg-black/50 text-white hover:bg-zinc-900 disabled:cursor-not-allowed disabled:opacity-30"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <div class="flex items-center gap-1">
                            <span
                                v-for="page in Math.min(
                                    5,
                                    transactions?.last_page || 1,
                                )"
                                :key="page"
                                @click="
                                    router.get(`/transactions?page=${page}`)
                                "
                                :class="[
                                    'flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-sm transition-all',
                                    transactions?.current_page === page
                                        ? 'bg-orange-600 text-white'
                                        : 'text-zinc-400 hover:bg-zinc-900/50 hover:text-white',
                                ]"
                            >
                                {{ page }}
                            </span>
                            <span
                                v-if="transactions?.last_page > 5"
                                class="px-2 text-zinc-600"
                                >...</span
                            >
                        </div>
                        <Button
                            :disabled="
                                !transactions?.current_page ||
                                transactions.current_page >=
                                    transactions.last_page
                            "
                            @click="
                                router.get(
                                    `/transactions?page=${(transactions?.current_page || 1) + 1}`,
                                )
                            "
                            variant="outline"
                            size="sm"
                            class="h-9 w-9 border-zinc-800 bg-black/50 text-white hover:bg-zinc-900 disabled:cursor-not-allowed disabled:opacity-30"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <div
            v-if="showAddModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
        >
            <div
                class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                @click="closeAddModal"
            ></div>
            <div
                class="relative z-50 w-full max-w-md animate-in rounded-2xl border border-zinc-800 bg-gradient-to-b from-zinc-950 to-black shadow-2xl duration-300 zoom-in-95 fade-in"
            >
                <div class="p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white">
                                Add Transaction
                            </h3>
                            <p class="text-sm text-zinc-500">
                                Record your financial activity
                            </p>
                        </div>
                        <button
                            @click="closeAddModal"
                            class="text-zinc-500 transition-colors hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Category Selection -->
                    <div v-if="!selectedCategory" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                @click="openAddModal('income')"
                                class="group flex h-24 flex-col items-center justify-center gap-3 rounded-xl border border-green-800/30 bg-gradient-to-br from-green-500/5 to-green-900/10 transition-all hover:border-green-600/50 hover:bg-green-500/10"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500/20 text-green-500 transition-all duration-300 group-hover:bg-green-500 group-hover:text-white"
                                >
                                    <ArrowUpRight class="h-5 w-5" />
                                </div>
                                <span class="font-medium text-white"
                                    >Income</span
                                >
                            </button>
                            <button
                                @click="openAddModal('expense')"
                                class="group flex h-24 flex-col items-center justify-center gap-3 rounded-xl border border-red-800/30 bg-gradient-to-br from-red-500/5 to-red-900/10 transition-all hover:border-red-600/50 hover:bg-red-500/10"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-red-500/20 text-red-500 transition-all duration-300 group-hover:bg-red-500 group-hover:text-white"
                                >
                                    <ArrowDownLeft class="h-5 w-5" />
                                </div>
                                <span class="font-medium text-white"
                                    >Expense</span
                                >
                            </button>
                        </div>
                    </div>

                    <!-- Transaction Form -->
                    <form
                        v-else
                        @submit.prevent="submitTransaction"
                        class="space-y-4"
                    >
                        <div>
                            <Label
                                class="mb-2 block text-sm font-medium text-zinc-300"
                                >Amount</Label
                            >
                            <div class="relative">
                                <span
                                    class="absolute top-1/2 left-3 -translate-y-1/2 text-zinc-500"
                                    >Rp</span
                                >
                                <Input
                                    v-model="transactionForm.amount"
                                    type="number"
                                    class="h-12 border-zinc-800 bg-black/50 pl-12 text-lg font-medium text-white focus:border-orange-600/50 focus:ring-orange-600/20"
                                    placeholder="0"
                                    required
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-zinc-300"
                                    >Date</Label
                                >
                                <Input
                                    v-model="transactionForm.date"
                                    type="date"
                                    class="h-10 border-zinc-800 bg-black/50 text-white focus:border-orange-600/50"
                                    required
                                />
                            </div>
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-zinc-300"
                                    >Platform</Label
                                >
                                <select
                                    v-model="transactionForm.platform_id"
                                    class="h-10 w-full rounded-md border border-zinc-800 bg-black/50 px-3 text-sm text-white focus:border-orange-600/50 focus:ring-1 focus:ring-orange-600/50"
                                    required
                                >
                                    <option value="" disabled selected>
                                        Select platform
                                    </option>
                                    <option
                                        v-for="p in platforms"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <Label
                                class="mb-2 block text-sm font-medium text-zinc-300"
                                >Type</Label
                            >
                            <select
                                v-model="transactionForm.type"
                                class="h-10 w-full rounded border border-zinc-800 bg-black/50 px-3 text-white focus:border-orange-600/50"
                                required
                            >
                                <option value="" disabled>Select a type</option>
                                <option
                                    v-for="type in availableTypes"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ type }}
                                </option>
                            </select>
                        </div>

                        <div v-if="selectedCategory === 'expense'">
                            <Label
                                class="mb-2 block text-sm font-medium text-zinc-300"
                                >Receipt (Optional)</Label
                            >
                            <div
                                class="rounded-lg border border-dashed border-zinc-800 p-4 text-center transition-colors hover:border-zinc-700"
                            >
                                <Input
                                    type="file"
                                    @input="
                                        transactionForm.attachment =
                                            $event.target.files[0]
                                    "
                                    class="hidden"
                                    id="attachment"
                                />
                                <label for="attachment" class="cursor-pointer">
                                    <div class="text-sm text-zinc-500">
                                        Click to upload receipt
                                        <p class="mt-1 text-xs text-zinc-600">
                                            JPG, PNG, PDF up to 2MB
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <Label
                                class="mb-2 block text-sm font-medium text-zinc-300"
                                >Description</Label
                            >
                            <Input
                                v-model="transactionForm.description"
                                type="text"
                                placeholder="Add a note (optional)"
                                class="h-10 border-zinc-800 bg-black/50 text-white focus:border-orange-600/50"
                            />
                        </div>

                        <div
                            class="flex justify-end gap-3 border-t border-zinc-800 pt-4"
                        >
                            <Button
                                type="button"
                                variant="ghost"
                                @click="selectedCategory = null"
                                class="text-zinc-400 hover:bg-zinc-900/50 hover:text-white"
                            >
                                Back
                            </Button>
                            <Button
                                type="submit"
                                class="w-32 bg-gradient-to-r from-orange-600 to-orange-500 text-white hover:from-orange-700 hover:to-orange-600"
                                :disabled="transactionForm.processing"
                            >
                                {{
                                    transactionForm.processing
                                        ? 'Saving...'
                                        : 'Save'
                                }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
        >
            <div
                class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                @click="closeEditModal"
            ></div>
            <div
                class="relative z-50 w-full max-w-md animate-in rounded-2xl border border-zinc-800 bg-gradient-to-b from-zinc-950 to-black shadow-2xl duration-300 zoom-in-95 fade-in"
            >
                <div class="p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white">
                                Edit Transaction
                            </h3>
                            <p class="text-sm text-zinc-500">
                                Update transaction details
                            </p>
                        </div>
                        <button
                            @click="closeEditModal"
                            class="text-zinc-500 transition-colors hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="updateTransaction" class="space-y-4">
                        <div>
                            <Label
                                class="mb-2 block text-sm font-medium text-zinc-300"
                                >Amount</Label
                            >
                            <div class="relative">
                                <span
                                    class="absolute top-1/2 left-3 -translate-y-1/2 text-zinc-500"
                                    >Rp</span
                                >
                                <Input
                                    v-model="transactionForm.amount"
                                    type="number"
                                    class="h-12 border-zinc-800 bg-black/50 pl-12 text-lg font-medium text-white focus:border-orange-600/50 focus:ring-orange-600/20"
                                    placeholder="0"
                                    required
                                    min="0"
                                    step="0.01"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-zinc-300"
                                    >Category</Label
                                >
                                <select
                                    v-model="transactionForm.category"
                                    class="h-10 w-full rounded-md border border-zinc-800 bg-black/50 px-3 text-sm text-white focus:ring-1 focus:ring-orange-600/50"
                                >
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-zinc-300"
                                    >Date</Label
                                >
                                <Input
                                    v-model="transactionForm.date"
                                    type="date"
                                    class="h-10 border-zinc-800 bg-black/50 text-white focus:border-orange-600/50"
                                    required
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-zinc-300"
                                    >Platform</Label
                                >
                                <select
                                    v-model="transactionForm.platform_id"
                                    class="h-10 w-full rounded-md border border-zinc-800 bg-black/50 px-3 text-sm text-white focus:ring-1 focus:ring-orange-600/50"
                                    required
                                >
                                    <option value="" disabled>
                                        Select platform
                                    </option>
                                    <option
                                        v-for="p in platforms"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <Label
                                    class="mb-2 block text-sm font-medium text-zinc-300"
                                    >Type</Label
                                >
                                <select
                                    v-model="transactionForm.type"
                                    class="h-10 w-full rounded border border-zinc-800 bg-black/50 px-3 text-white focus:border-orange-600/50"
                                    required
                                >
                                    <option value="" disabled>
                                        Select a type
                                    </option>
                                    <option
                                        v-for="type in availableTypes"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <Label
                                class="mb-2 block text-sm font-medium text-zinc-300"
                                >Description</Label
                            >
                            <Input
                                v-model="transactionForm.description"
                                type="text"
                                class="h-10 border-zinc-800 bg-black/50 text-white focus:border-orange-600/50"
                            />
                        </div>

                        <div
                            class="flex justify-end gap-3 border-t border-zinc-800 pt-4"
                        >
                            <Button
                                type="button"
                                variant="ghost"
                                @click="closeEditModal"
                                class="text-zinc-400 hover:bg-zinc-900/50 hover:text-white"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                class="w-32 bg-gradient-to-r from-orange-600 to-orange-500 text-white hover:from-orange-700 hover:to-orange-600"
                                :disabled="transactionForm.processing"
                            >
                                {{
                                    transactionForm.processing
                                        ? 'Saving...'
                                        : 'Update'
                                }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.4s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.list-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.list-move {
    transition: transform 0.4s ease;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.animate-in {
    animation: animate-in 0.3s ease-out;
}

@keyframes animate-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.fade-in {
    animation: fade-in 0.3s ease-out;
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.zoom-in-95 {
    animation: zoom-in-95 0.3s ease-out;
}

@keyframes zoom-in-95 {
    from {
        transform: scale(0.95);
    }
    to {
        transform: scale(1);
    }
}
</style>
