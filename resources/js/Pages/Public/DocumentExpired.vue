<script setup>
import { Head } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="Link Expired" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 flex flex-col">
        <!-- Header -->
        <header class="border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-8 text-cyan-400 fill-current" />
                    <span class="text-lg font-bold text-white">AWS System</span>
                </div>
            </div>
        </header>

        <!-- Expired Content -->
        <main class="flex-1 flex items-center justify-center px-4">
            <div class="max-w-md w-full text-center">
                <!-- Animated Clock Icon -->
                <div class="mx-auto w-24 h-24 rounded-full bg-blue-500/10 border-2 border-blue-400/30 flex items-center justify-center mb-8 animate-pulse">
                    <svg class="w-12 h-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-white mb-3">Link Expired</h1>
                <p class="text-blue-200/80 text-lg mb-6">
                    This document link has expired and is no longer accessible.
                </p>

                <div class="bg-blue-500/10 border border-blue-400/20 rounded-xl p-6 mb-8 backdrop-blur-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-blue-100 font-semibold mb-1">Need access?</p>
                            <p class="text-blue-200/70 text-sm leading-relaxed">
                                Please contact the issuing company to request a new document link. Share links are time-limited for your security.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2 text-sm text-blue-300/50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Protected by AWS System</span>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 text-center">
            <p class="text-sm text-blue-300/40">&copy; {{ new Date().getFullYear() }} AWS System. All rights reserved.</p>
        </footer>
    </div>
</template>
