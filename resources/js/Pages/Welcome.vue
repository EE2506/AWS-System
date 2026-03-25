<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="bg-gray-950 text-gray-200 min-h-screen font-sans selection:bg-gray-500 selection:text-white overflow-x-hidden">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-6 flex justify-between items-center relative z-50">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="w-10 h-10 rounded-xl shadow-lg shadow-gray-500/20" />
                <span class="text-xl font-bold tracking-tight text-white">AWS<span class="text-gray-400">System</span></span>
            </div>

            <div v-if="canLogin" class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="px-5 py-2.5 rounded-lg font-medium text-sm text-white bg-gray-800 hover:bg-gray-700 transition-all border border-gray-700 hover:border-gray-600"
                >
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="px-5 py-2.5 rounded-lg font-medium text-sm text-gray-300 hover:text-white transition-colors"
                    >
                        Log in
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-5 py-2.5 rounded-lg font-medium text-sm text-white bg-gray-700 hover:bg-gray-600 transition-all shadow-lg shadow-gray-500/25 hover:shadow-gray-500/40"
                    >
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative pt-20 pb-32 lg:pt-32">
            <!-- Background Glows (Neutral) -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gray-600/10 rounded-full blur-[120px] -z-10 pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-[800px] h-[600px] bg-gray-500/5 rounded-full blur-[100px] -z-10 pointer-events-none"></div>

            <div class="container mx-auto px-6 text-center max-w-5xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-800/50 border border-gray-700/50 text-xs font-medium text-gray-400 mb-8 animate-fade-in-up">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gray-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-500"></span>
                    </span>
                    Next Generation Document Automation
                </div>
                
                <h1 class="text-5xl lg:text-7xl font-bold tracking-tight text-white mb-8 animate-fade-in-up animation-delay-100">
                    Streamline Your <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-gray-200 to-gray-500">HVAC Business</span> Docs
                </h1>
                
                <p class="text-lg text-gray-400 mb-12 max-w-2xl mx-auto leading-relaxed animate-fade-in-up animation-delay-200">
                    Create professional Statements of Account, Purchase Orders, and Delivery Receipts in seconds. 
                    The all-in-one document management system designed for modern HVAC contractors.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up animation-delay-300">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-white bg-gradient-to-r from-gray-700 to-gray-600 hover:from-gray-600 hover:to-gray-500 transition-all shadow-xl shadow-gray-500/25 transform hover:-translate-y-1"
                    >
                        Start For Free
                    </Link>
                    <a href="#features" class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-gray-300 hover:text-white bg-gray-800/50 hover:bg-gray-800 border border-gray-700/50 hover:border-gray-600 transition-all backdrop-blur-sm">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- UI Mockup / Visual -->
            <div class="mt-20 container mx-auto px-6 animate-fade-in-up animation-delay-500">
                <div class="relative rounded-2xl border border-gray-800 bg-gray-900/50 backdrop-blur-xl shadow-2xl p-4 lg:p-6 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-gray-500/5 to-gray-500/5 pointer-events-none"></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card 1 -->
                        <div class="bg-gray-950 p-6 rounded-xl border border-gray-800/60 shadow-lg">
                            <div class="w-10 h-10 rounded-lg bg-gray-500/10 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">Smart Invoicing</h3>
                            <p class="text-sm text-gray-400">Generate SOA and Invoices with auto-calculated totals and professional layouts.</p>
                        </div>
                         <!-- Card 2 -->
                        <div class="bg-gray-950 p-6 rounded-xl border border-gray-800/60 shadow-lg">
                            <div class="w-10 h-10 rounded-lg bg-gray-500/10 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">Instant Creation</h3>
                            <p class="text-sm text-gray-400">Create new documents in seconds using pre-built templates for all your needs.</p>
                        </div>
                         <!-- Card 3 -->
                        <div class="bg-gray-950 p-6 rounded-xl border border-gray-800/60 shadow-lg">
                             <div class="w-10 h-10 rounded-lg bg-gray-500/10 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">Organized Records</h3>
                            <p class="text-sm text-gray-400">Keep track of every quotation, PO, and receipt in one searchable secure database.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Grid -->
        <section id="features" class="py-24 bg-gray-900/50 border-y border-gray-800/50">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl font-bold text-white mb-4">Everything you need to manage docs</h2>
                    <p class="text-gray-400">Stop wrestling with Word/Excel. Get a dedicated system built for HVAC workflows.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Feature 1 -->
                    <div class="group hover:bg-gray-800/50 p-6 rounded-2xl transition-all border border-transparent hover:border-gray-700">
                        <div class="w-12 h-12 bg-gray-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Professional PDFs</h3>
                        <p class="text-gray-400 text-sm">Automatically generate branded PDF documents ready to email or print.</p>
                    </div>

                     <!-- Feature 2 -->
                    <div class="group hover:bg-gray-800/50 p-6 rounded-2xl transition-all border border-transparent hover:border-gray-700">
                        <div class="w-12 h-12 bg-gray-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Secure & Private</h3>
                        <p class="text-gray-400 text-sm">Role-based access ensures only authorized staff can view or edit sensitive docs.</p>
                    </div>

                     <!-- Feature 3 -->
                    <div class="group hover:bg-gray-800/50 p-6 rounded-2xl transition-all border border-transparent hover:border-gray-700">
                        <div class="w-12 h-12 bg-gray-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Public Sharing</h3>
                        <p class="text-gray-400 text-sm">Generate secure links to share documents with clients without them needing an account.</p>
                    </div>

                     <!-- Feature 4 -->
                    <div class="group hover:bg-gray-800/50 p-6 rounded-2xl transition-all border border-transparent hover:border-gray-700">
                        <div class="w-12 h-12 bg-gray-500/10 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">HVAC Tailored</h3>
                        <p class="text-gray-400 text-sm">Fields and layouts specifically designed for HVAC service, installation, and parts.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-gray-800 text-center">
            <div class="container mx-auto px-6">
                <div class="flex items-center justify-center gap-2 mb-6 opacity-75">
                     <span class="text-lg font-bold text-white">AWS<span class="text-gray-400">System</span></span>
                </div>
                <p class="text-gray-500 text-sm">© {{ new Date().getFullYear() }} AWS Document System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
