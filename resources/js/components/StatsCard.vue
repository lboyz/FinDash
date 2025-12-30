<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    amount: string;
    subtext: string;
    icon: any;
    variant: 'success' | 'danger' | 'info';
    loading?: boolean;
}>();

const variants = {
    success: {
        bg: 'from-green-500/10 to-green-900/5',
        border: 'border-green-800/20',
        text: 'text-green-400',
        subtext: 'text-green-500/70',
        iconBg: 'bg-green-500/20',
        iconText: 'text-green-400'
    },
    danger: {
        bg: 'from-red-500/10 to-red-900/5',
        border: 'border-red-800/20',
        text: 'text-red-400',
        subtext: 'text-red-500/70',
        iconBg: 'bg-red-500/20',
        iconText: 'text-red-400'
    },
    info: {
        bg: 'from-blue-500/10 to-blue-900/5',
        border: 'border-blue-800/20',
        text: 'text-blue-400',
        subtext: 'text-blue-500/70',
        iconBg: 'bg-blue-500/20',
        iconText: 'text-blue-400'
    }
};

const currentVariant = computed(() => variants[props.variant]);
</script>

<template>
    <div 
        class="bg-gradient-to-br border rounded-xl p-5 backdrop-blur-sm transition-all duration-300 hover:scale-[1.02]"
        :class="[currentVariant.bg, currentVariant.border]"
    >
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium" :class="currentVariant.text">{{ title }}</p>
                <div v-if="loading" class="h-8 w-24 bg-zinc-800/50 rounded animate-pulse mt-1"></div>
                <p v-else class="text-2xl font-bold text-white mt-1 tracking-tight">{{ amount }}</p>
                <p class="text-xs mt-1" :class="currentVariant.subtext">{{ subtext }}</p>
            </div>
            <div 
                class="w-10 h-10 rounded-full flex items-center justify-center transition-transform duration-500"
                :class="[currentVariant.iconBg]"
            >
                <component :is="icon" class="h-5 w-5" :class="currentVariant.iconText" />
            </div>
        </div>
    </div>
</template>
