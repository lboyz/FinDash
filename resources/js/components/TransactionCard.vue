<script setup lang="ts">
import { computed } from 'vue';
import { Transaction } from '@/types/transaction';
import { Button } from '@/components/ui/button';
import { 
    Pencil, 
    Trash2, 
    ArrowUpRight, 
    ArrowDownLeft,
    CreditCard 
} from 'lucide-vue-next';

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
            month: 'short'
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
            year: 'numeric'
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
            maximumFractionDigits: 2
        }).format(amount);
    } catch {
        return 'Rp 0';
    }
};
</script>

<template>
    <div 
        class="group relative bg-gradient-to-br from-zinc-900/80 to-black/50 border border-zinc-800/50 rounded-2xl p-5 hover:border-zinc-700/50 hover:bg-zinc-900/60 transition-all duration-300 backdrop-blur-sm"
    >
        <div class="relative flex items-start justify-between">
            <!-- Left Section -->
            <div class="flex items-start gap-4">
                <!-- Date Circle -->
                <div class="flex flex-col items-center">
                    <div 
                        class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-lg"
                        :class="isIncome ? 'bg-green-500/10 border border-green-800/30 group-hover:bg-green-500/20' : 'bg-orange-500/10 border border-orange-800/30 group-hover:bg-orange-500/20'"
                    >
                        <div class="text-center">
                            <div class="text-lg font-bold text-white leading-none">{{ getDayOfMonth(transaction.date) }}</div>
                            <div class="text-[10px] uppercase tracking-wider font-semibold mt-0.5" :class="isIncome ? 'text-green-400' : 'text-orange-400'">
                                {{ getMonthYear(transaction.date).split(' ')[0] }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-1.5">
                    <!-- Category & Type -->
                    <div class="flex items-center gap-2.5">
                        <span 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide uppercase"
                            :class="isIncome ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'"
                        >
                            <ArrowUpRight v-if="isIncome" class="h-3 w-3" />
                            <ArrowDownLeft v-else class="h-3 w-3" />
                            {{ isIncome ? 'Income' : 'Expense' }}
                        </span>
                        <span class="text-sm text-zinc-300 font-medium">
                            {{ transaction.type || 'Uncategorized' }}
                        </span>
                    </div>

                    <!-- Platform & Description -->
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <CreditCard class="h-3.5 w-3.5 text-zinc-500" />
                            <span class="text-sm font-medium text-zinc-400 group-hover:text-zinc-300 transition-colors">{{ transaction.platform?.name || 'No Platform' }}</span>
                        </div>
                        <p v-if="transaction.description" class="text-sm text-zinc-500 line-clamp-1 group-hover:text-zinc-400 transition-colors pl-5.5">
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
                    {{ isIncome ? '+' : '-' }}{{ transaction.formatted_amount || formatCurrency(transaction.amount || 0) }}
                </div>
                
                <!-- Date -->
                <div class="text-xs text-zinc-600 group-hover:text-zinc-500 transition-colors">
                    {{ formatDate(transaction.date) }}
                </div>

                <!-- Actions (Visible on Hover) -->
                <div class="flex items-center gap-1 mt-3 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                    <Button 
                        @click="$emit('edit', transaction)" 
                        size="icon" 
                        variant="ghost" 
                        class="h-8 w-8 text-zinc-500 hover:text-white hover:bg-zinc-800/80 rounded-full"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <Button 
                        @click="$emit('delete', transaction.id)" 
                        size="icon" 
                        variant="ghost" 
                        class="h-8 w-8 text-zinc-500 hover:text-red-400 hover:bg-red-500/10 rounded-full"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
