<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="dark h-screen w-full flex flex-col lg:grid lg:grid-cols-2 overflow-hidden bg-black text-white">
        <!-- Left Side: Branding/Visuals -->
        <div class="hidden lg:flex flex-col justify-between bg-zinc-900 p-10 text-white relative overflow-hidden">
             <!-- Background Decoration -->
            <div class="absolute inset-0 z-0 pointer-events-none">
                <div class="absolute top-[-20%] left-[-20%] w-[60%] h-[60%] rounded-full bg-orange-600/20 blur-[150px]"></div>
                <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] rounded-full bg-blue-600/20 blur-[150px]"></div>
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
            </div>

            <div class="relative z-10">
                <Link :href="home()" class="flex items-center gap-2 font-medium text-lg">
                    <div class="bg-gradient-to-tr from-orange-500 to-red-600 w-8 h-8 rounded-lg flex items-center justify-center">
                        <AppLogoIcon class="size-5 fill-white" />
                    </div>
                    FinDash
                </Link>
            </div>

            <div class="relative z-10 space-y-4">
                <h2 class="text-4xl font-bold tracking-tight">
                    Manage your wealth <br/> with precision.
                </h2>
                <p class="text-zinc-400 max-w-md">
                    Track expenses, analyze trends, and plan for the future. 
                    Join thousands of users mastering their financial life.
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-4 text-sm text-zinc-500">
                <p>&copy; 2025 FinDash Inc.</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="flex flex-1 items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-black">
             <div class="mx-auto grid w-[350px] gap-6">
                <div class="flex flex-col gap-2 text-center">
                    <h1 class="text-3xl font-bold">{{ title }}</h1>
                    <p class="text-balance text-zinc-400">
                        {{ description }}
                    </p>
                </div>
                
                <slot />
             </div>
        </div>
    </div>
</template>
