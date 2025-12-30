<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Welcome to FinDash" />

    <div class="min-h-screen bg-black text-white selection:bg-orange-500 selection:text-white">
        <!-- Decoration -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-orange-600/10 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[120px]"></div>
        </div>

        <div class="relative z-10 flex flex-col min-h-screen">
            <!-- Navbar -->
            <nav class="container mx-auto px-6 py-6 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="bg-gradient-to-tr from-orange-500 to-red-600 w-8 h-8 rounded-lg flex items-center justify-center">
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white">
                            <path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">FinDash</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <Link href="/login" v-if="$page.props.auth.user === null">
                        <Button variant="ghost" class="text-gray-300 hover:text-white hover:bg-white/10">
                            Log in
                        </Button>
                    </Link>
                    <Link href="/dashboard" v-else>
                         <Button variant="secondary" class="font-semibold">
                            Dashboard
                        </Button>
                    </Link>

                    <Link href="/register" v-if="!$page.props.auth.user">
                        <Button class="bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-full px-6">
                            Get Started
                        </Button>
                    </Link>
                </div>
            </nav>

            <!-- Hero Section -->
            <main class="flex-grow flex flex-col items-center justify-center text-center px-4 sm:px-6 lg:px-8 py-20">
                <div class="max-w-4xl mx-auto space-y-8">
                    <div class="inline-flex items-center rounded-full border border-orange-500/30 bg-orange-500/10 px-3 py-1 text-sm font-medium text-orange-300 mb-4">
                        <span class="flex h-2 w-2 rounded-full bg-orange-500 mr-2"></span>
                        Coming Soon
                    </div>

                    <h1 class="text-5xl sm:text-7xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-400">
                        Master Your Finances <br class="hidden sm:block" />
                        <span class="text-white">With Confidence</span>
                    </h1>
                    
                    <p class="text-lg sm:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                        Track expenses, manage budgets, and achieve your financial goals with FinDash. 
                        The modern dashboard built for the future of personal finance.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                        <Link href="/register">
                            <Button size="lg" class="bg-white text-black hover:bg-gray-200 h-12 px-8 text-base font-semibold w-full sm:w-auto">
                                Start for free
                            </Button>
                        </Link>
                        <Link href="#features">
                             <Button size="lg" variant="outline" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white h-12 px-8 text-base w-full sm:w-auto">
                                View Demo
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Dashboard Preview (Abstract) -->
                <div class="mt-20 relative max-w-5xl mx-auto w-full">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent z-10"></div>
                    <div class="rounded-xl border border-white/10 bg-gray-900/50 backdrop-blur-sm p-4 shadow-2xl overflow-hidden">
                        <div class="grid grid-cols-12 gap-4 opacity-50">
                            <!-- Sidebar Mockup -->
                            <div class="col-span-2 hidden md:block space-y-4 border-r border-white/5 pr-4 h-[400px]">
                                <div class="h-8 w-24 bg-white/10 rounded"></div>
                                <div class="space-y-2 mt-8">
                                    <div class="h-4 w-full bg-white/5 rounded"></div>
                                    <div class="h-4 w-full bg-white/5 rounded"></div>
                                    <div class="h-4 w-full bg-white/5 rounded"></div>
                                </div>
                            </div>
                            <!-- Main Content Mockup -->
                            <div class="col-span-12 md:col-span-10 space-y-4">
                                <div class="flex justify-between">
                                    <div class="h-8 w-32 bg-white/10 rounded"></div>
                                    <div class="h-8 w-8 bg-white/10 rounded-full"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="h-32 bg-white/5 rounded-lg border border-white/5"></div>
                                    <div class="h-32 bg-white/5 rounded-lg border border-white/5"></div>
                                    <div class="h-32 bg-white/5 rounded-lg border border-white/5"></div>
                                </div>
                                <div class="h-64 bg-white/5 rounded-lg border border-white/5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/10 py-12 bg-black">
                <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                    <p>&copy; 2025 FinDash. All rights reserved.</p>
                    <div class="flex gap-6 mt-4 md:mt-0">
                        <a href="#" class="hover:text-white transition-colors">Privacy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms</a>
                        <a href="#" class="hover:text-white transition-colors">Support</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add custom animations if needed, but Tailwind is usually enough */
</style>
