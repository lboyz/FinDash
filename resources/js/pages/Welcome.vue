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

    <div
        class="min-h-screen bg-black text-white selection:bg-orange-500 selection:text-white"
    >
        <!-- Decoration -->
        <div class="pointer-events-none fixed inset-0 z-0">
            <div
                class="absolute top-[-10%] left-[-10%] h-[40%] w-[40%] rounded-full bg-orange-600/10 blur-[120px]"
            ></div>
            <div
                class="absolute right-[-10%] bottom-[-10%] h-[40%] w-[40%] rounded-full bg-blue-600/10 blur-[120px]"
            ></div>
        </div>

        <div class="relative z-10 flex min-h-screen flex-col">
            <!-- Navbar -->
            <nav
                class="container mx-auto flex items-center justify-between px-6 py-6"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr from-orange-500 to-red-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="h-5 w-5 text-white"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z"
                                clip-rule="evenodd"
                            />
                            <path
                                fill-rule="evenodd"
                                d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight"
                        >FinDash</span
                    >
                </div>

                <div class="flex items-center gap-4">
                    <Link href="/login" v-if="$page.props.auth.user === null">
                        <Button
                            variant="ghost"
                            class="text-gray-300 hover:bg-white/10 hover:text-white"
                        >
                            Log in
                        </Button>
                    </Link>
                    <Link href="/dashboard" v-else>
                        <Button variant="secondary" class="font-semibold">
                            Dashboard
                        </Button>
                    </Link>

                    <Link href="/register" v-if="!$page.props.auth.user">
                        <Button
                            class="rounded-full bg-orange-600 px-6 font-semibold text-white hover:bg-orange-700"
                        >
                            Get Started
                        </Button>
                    </Link>
                </div>
            </nav>

            <!-- Hero Section -->
            <main
                class="flex flex-grow flex-col items-center justify-center px-4 py-20 text-center sm:px-6 lg:px-8"
            >
                <div class="mx-auto max-w-4xl space-y-8">
                    <div
                        class="mb-4 inline-flex items-center rounded-full border border-orange-500/30 bg-orange-500/10 px-3 py-1 text-sm font-medium text-orange-300"
                    >
                        <span
                            class="mr-2 flex h-2 w-2 rounded-full bg-orange-500"
                        ></span>
                        Coming Soon
                    </div>

                    <h1
                        class="bg-gradient-to-b from-white to-gray-400 bg-clip-text text-5xl font-bold tracking-tight text-transparent sm:text-7xl"
                    >
                        Master Your Finances <br class="hidden sm:block" />
                        <span class="text-white">With Confidence</span>
                    </h1>

                    <p
                        class="mx-auto max-w-2xl text-lg leading-relaxed text-gray-400 sm:text-xl"
                    >
                        Track expenses, manage budgets, and achieve your
                        financial goals with FinDash. The modern dashboard built
                        for the future of personal finance.
                    </p>

                    <div
                        class="flex flex-col items-center justify-center gap-4 pt-4 sm:flex-row"
                    >
                        <Link href="/register">
                            <Button
                                size="lg"
                                class="h-12 w-full bg-white px-8 text-base font-semibold text-black hover:bg-gray-200 sm:w-auto"
                            >
                                Start for free
                            </Button>
                        </Link>
                        <Link href="#features">
                            <Button
                                size="lg"
                                variant="outline"
                                class="h-12 w-full border-gray-700 px-8 text-base text-gray-300 hover:bg-gray-800 hover:text-white sm:w-auto"
                            >
                                View Demo
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Dashboard Preview (Abstract) -->
                <div class="relative mx-auto mt-20 w-full max-w-5xl">
                    <div
                        class="absolute inset-0 z-10 bg-gradient-to-t from-black via-transparent to-transparent"
                    ></div>
                    <div
                        class="overflow-hidden rounded-xl border border-white/10 bg-gray-900/50 p-4 shadow-2xl backdrop-blur-sm"
                    >
                        <div class="grid grid-cols-12 gap-4 opacity-50">
                            <!-- Sidebar Mockup -->
                            <div
                                class="col-span-2 hidden h-[400px] space-y-4 border-r border-white/5 pr-4 md:block"
                            >
                                <div class="h-8 w-24 rounded bg-white/10"></div>
                                <div class="mt-8 space-y-2">
                                    <div
                                        class="h-4 w-full rounded bg-white/5"
                                    ></div>
                                    <div
                                        class="h-4 w-full rounded bg-white/5"
                                    ></div>
                                    <div
                                        class="h-4 w-full rounded bg-white/5"
                                    ></div>
                                </div>
                            </div>
                            <!-- Main Content Mockup -->
                            <div class="col-span-12 space-y-4 md:col-span-10">
                                <div class="flex justify-between">
                                    <div
                                        class="h-8 w-32 rounded bg-white/10"
                                    ></div>
                                    <div
                                        class="h-8 w-8 rounded-full bg-white/10"
                                    ></div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div
                                        class="h-32 rounded-lg border border-white/5 bg-white/5"
                                    ></div>
                                    <div
                                        class="h-32 rounded-lg border border-white/5 bg-white/5"
                                    ></div>
                                    <div
                                        class="h-32 rounded-lg border border-white/5 bg-white/5"
                                    ></div>
                                </div>
                                <div
                                    class="h-64 rounded-lg border border-white/5 bg-white/5"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/10 bg-black py-12">
                <div
                    class="container mx-auto flex flex-col items-center justify-between px-6 text-sm text-gray-500 md:flex-row"
                >
                    <p>&copy; 2025 FinDash. All rights reserved.</p>
                    <div class="mt-4 flex gap-6 md:mt-0">
                        <a href="#" class="transition-colors hover:text-white"
                            >Privacy</a
                        >
                        <a href="#" class="transition-colors hover:text-white"
                            >Terms</a
                        >
                        <a href="#" class="transition-colors hover:text-white"
                            >Support</a
                        >
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add custom animations if needed, but Tailwind is usually enough */
</style>
