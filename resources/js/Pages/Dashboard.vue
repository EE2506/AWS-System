<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    verse: Object,
    stats: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(value || 0);
};

const valueFilter = ref('all'); // all, paid, finalized, draft

const displayedAmount = computed(() => {
    switch (valueFilter.value) {
        case 'paid': return props.stats.paid_amount;
        case 'finalized': return props.stats.finalized_amount;
        case 'draft': return props.stats.drafts_amount;
        default: return props.stats.total_amount;
    }
});

const displayedLabel = computed(() => {
    switch (valueFilter.value) {
        case 'paid': return 'Total Paid';
        case 'finalized': return 'Pending Payment';
        case 'draft': return 'Draft Value';
        default: return 'Total Value';
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome & Verse Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Welcome Card -->
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 h-full flex flex-col justify-center">
                            <h3 class="text-xl font-bold mb-2">Welcome back, {{ $page.props.auth.user.name }}!</h3>
                            <p class="text-gray-600 dark:text-gray-400">Here's what's happening with your documents today.</p>
                            
                            <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg text-center">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_documents }}</div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Total</div>
                                </div>

                                <Link :href="route('documents.index', { status: 'draft' })" class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg text-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer group">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">{{ stats.drafts }}</div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Drafts</div>
                                </Link>

                                <Link :href="route('documents.index', { status: 'finalized' })" class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg text-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer group">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ stats.finalized }}</div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Pending</div>
                                </Link>

                                <Link :href="route('documents.index', { status: 'paid' })" class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg text-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer group">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{{ stats.paid }}</div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Paid</div>
                                </Link>
                            </div>

                            <!-- Total Value Row with Filters -->
                            <div v-if="stats.is_admin" class="mt-4 flex flex-col sm:flex-row items-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg">
                                <div class="flex-1 text-center sm:text-left">
                                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ displayedLabel }}</div>
                                    <div class="text-2xl font-bold transition-all duration-300"
                                        :class="{
                                            'text-emerald-600 dark:text-emerald-400': valueFilter === 'paid',
                                            'text-yellow-600 dark:text-yellow-400': valueFilter === 'finalized',
                                            'text-blue-600 dark:text-blue-400': valueFilter === 'all',
                                            'text-gray-500': valueFilter === 'draft'
                                        }"
                                    >
                                        {{ formatCurrency(displayedAmount) }}
                                    </div>
                                </div>
                                
                                <!-- Filter Buttons Inline -->
                                <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                                <button 
                                    @click="valueFilter = 'all'" 
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200 flex items-center gap-1.5',
                                        valueFilter === 'all' 
                                            ? 'bg-blue-500 text-white shadow-sm' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                    All
                                </button>
                                <button 
                                    @click="valueFilter = 'paid'" 
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200 flex items-center gap-1.5',
                                        valueFilter === 'paid' 
                                            ? 'bg-emerald-500 text-white shadow-sm' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Paid
                                </button>
                                <button 
                                    @click="valueFilter = 'finalized'" 
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200 flex items-center gap-1.5',
                                        valueFilter === 'finalized' 
                                            ? 'bg-yellow-500 text-white shadow-sm' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Pending
                                </button>
                                <button 
                                    @click="valueFilter = 'draft'" 
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200 flex items-center gap-1.5',
                                        valueFilter === 'draft' 
                                            ? 'bg-gray-500 text-white shadow-sm' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    Drafts
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Verse Card -->
                    <div class="bg-gradient-to-br from-cyan-600 to-blue-700 overflow-hidden shadow-sm sm:rounded-lg text-white relative">
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-white/10 rounded-full blur-xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-2 -ml-2 w-16 h-16 bg-black/10 rounded-full blur-xl"></div>
                        
                        <div class="p-6 relative z-10 h-full flex flex-col justify-center">
                            <h4 class="text-xs font-bold uppercase tracking-widest opacity-75 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                Daily Verse
                            </h4>
                            <p class="text-lg font-serif italic leading-relaxed mb-3">
                                "{{ verse.text }}"
                            </p>
                            <p class="text-sm font-medium text-right opacity-90">
                                — {{ verse.reference }} ({{ verse.version }})
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <Link 
                        :href="route('documents.create', { type: 'soa' })" 
                        class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow group"
                    >
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-900 dark:text-white">New SOA</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create Statement of Account</p>
                    </Link>

                     <Link 
                        :href="route('documents.create', { type: 'quotation' })" 
                        class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow group"
                    >
                        <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <h4 class="font-semibold text-slate-900 dark:text-white">New Quote</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Send a new Quotation</p>
                    </Link>

                     <Link 
                        :href="route('documents.create', { type: 'purchase_order' })" 
                        class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow group"
                    >
                        <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-900 dark:text-white">New PO</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create Purchase Order</p>
                    </Link>

                    <Link 
                        :href="route('documents.create', { type: 'delivery_receipt' })" 
                        class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow group"
                    >
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                             <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-900 dark:text-white">New DR</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create Delivery Receipt</p>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
