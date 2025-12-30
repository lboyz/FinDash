<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Transaction } from '@/types/transaction';
import {
    ArrowDownLeft,
    ArrowUpRight,
    CreditCard,
    Pencil,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    transaction: Transaction;
}>();

const emit = defineEmits<{
    (e: 'edit', t: Transaction): void;
    (e: 'delete', id: number): void;
}>();

const isIncome = computed(() => props.transaction.category === 'income');

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
        return date.getDate();
    } catch {
        return '--';
    }
};

const getMonthYear = (dateString: string) => {
    try {
        if (!dateString) return '---';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return '---';
    }
};

const formatCurrency = (amount: number) => {
    try {
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
</script>

<template>
    <div
        class="group relative rounded-2xl border border-zinc-800/50 bg-gradient-to-br from-zinc-900/80 to-black/50 p-5 backdrop-blur-sm transition-all duration-300 hover:border-zinc-700/50 hover:bg-zinc-900/60"
    >
        <div class="relative flex items-start justify-between">
            <!-- Left Section -->
            <div class="flex items-start gap-4">
                <!-- Date Circle -->
                <div class="flex flex-col items-center">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl shadow-lg transition-all duration-300"
                        :class="
                            isIncome
                                ? 'border border-green-800/30 bg-green-500/10 group-hover:bg-green-500/20'
                                : 'border border-orange-800/30 bg-orange-500/10 group-hover:bg-orange-500/20'
                        "
                    >
                        <div class="text-center">
                            <div
                                class="text-lg leading-none font-bold text-white"
                            >
                                {{ getDayOfMonth(transaction.date) }}
                            </div>
                            <div
                                class="mt-0.5 text-[10px] font-semibold tracking-wider uppercase"
                                :class="
                                    isIncome
                                        ? 'text-green-400'
                                        : 'text-orange-400'
                                "
                            >
                                {{
                                    getMonthYear(transaction.date).split(' ')[0]
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-1.5">
                    <!-- Category & Type -->
                    <div class="flex items-center gap-2.5">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide uppercase"
                            :class="
                                isIncome
                                    ? 'border border-green-500/20 bg-green-500/10 text-green-400'
                                    : 'border border-red-500/20 bg-red-500/10 text-red-400'
                            "
                        >
                            <ArrowUpRight v-if="isIncome" class="h-3 w-3" />
                            <ArrowDownLeft v-else class="h-3 w-3" />
                            {{ isIncome ? 'Income' : 'Expense' }}
                        </span>
                        <span class="text-sm font-medium text-zinc-300">
                            {{ transaction.type || 'Uncategorized' }}
                        </span>
                    </div>

                    <!-- Platform & Description -->
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <CreditCard class="h-3.5 w-3.5 text-zinc-500" />
                            <span
                                class="text-sm font-medium text-zinc-400 transition-colors group-hover:text-zinc-300"
                                >{{
                                    transaction.platform?.name || 'No Platform'
                                }}</span
                            >
                        </div>
                        <p
                            v-if="transaction.description"
                            class="line-clamp-1 pl-5.5 text-sm text-zinc-500 transition-colors group-hover:text-zinc-400"
                        >
                            {{ transaction.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex flex-col items-end gap-1">
                <!-- Amount -->
                <div
                    class="text-lg font-bold tracking-tight"
                    :class="isIncome ? 'text-green-400' : 'text-white'"
                >
                    {{ isIncome ? '+' : '-'
                    }}{{
                        transaction.formatted_amount ||
                        formatCurrency(transaction.amount || 0)
                    }}
                </div>

                <!-- Date -->
                <div
                    class="text-xs text-zinc-600 transition-colors group-hover:text-zinc-500"
                >
                    {{ formatDate(transaction.date) }}
                </div>

                <!-- Actions (Visible on Hover) -->
                <div
                    class="mt-3 flex translate-y-2 items-center gap-1 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100"
                >
                    <Button
                        @click="$emit('edit', transaction)"
                        size="icon"
                        variant="ghost"
                        class="h-8 w-8 rounded-full text-zinc-500 hover:bg-zinc-800/80 hover:text-white"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <Button
                        @click="$emit('delete', transaction.id)"
                        size="icon"
                        variant="ghost"
                        class="h-8 w-8 rounded-full text-zinc-500 hover:bg-red-500/10 hover:text-red-400"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
