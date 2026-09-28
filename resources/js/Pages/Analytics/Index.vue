<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, BarElement, CategoryScale, Filler, Legend, LinearScale, LineElement, PointElement, Title, Tooltip } from 'chart.js';

ChartJS.register(ArcElement, BarElement, CategoryScale, Filler, Legend, LinearScale, LineElement, PointElement, Title, Tooltip);

const props = defineProps({
    filters: Object,
    types: Object,
    users: Array,
    kpis: Object,
    bucket: String,
    trendRows: Array,
    clients: Array,
    items: Array,
    status: Object,
    weekday: Array,
    hours: Array,
    sharing: Object,
    people: Object,
    quality: Object,
});

const preset = ref(props.filters?.preset || '30d');
const selectedTypes = ref(props.filters?.types || []);
const selectedUser = ref(props.filters?.user_id || '');
const compare = ref(Boolean(props.filters?.compare));

const currency = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 });
const number = new Intl.NumberFormat('en-PH');
const money = (value) => currency.format(Number(value || 0));
const count = (value) => number.format(Number(value || 0));

const typeColors = { soa: '#22d3ee', purchase_order: '#2dd4bf', quotation: '#60a5fa', delivery_receipt: '#94a3b8' };
const typeLabels = { soa: 'SOA', purchase_order: 'Purchase Order', quotation: 'Quotation', delivery_receipt: 'Delivery Receipt' };

const applyFilters = () => {
    router.get(route('analytics.index'), { preset: preset.value, types: selectedTypes.value, user_id: selectedUser.value || undefined, compare: compare.value ? 1 : undefined }, { preserveState: true, preserveScroll: true });
};

const trendBuckets = computed(() => [...new Set((props.trendRows || []).map((row) => row.bucket))]);
const trendData = computed(() => ({
    labels: trendBuckets.value,
    datasets: Object.keys(typeLabels).map((type) => ({
        label: typeLabels[type],
        data: trendBuckets.value.map((bucket) => props.trendRows?.find((row) => row.bucket === bucket && row.type === type)?.count || 0),
        backgroundColor: typeColors[type],
        borderColor: typeColors[type],
        borderWidth: 1,
        fill: true,
    })),
}));
const valueData = computed(() => ({
    labels: trendBuckets.value,
    datasets: [
        { label: 'Billed value', data: trendBuckets.value.map((bucket) => props.trendRows?.filter((row) => row.bucket === bucket).reduce((sum, row) => sum + Number(row.billed || 0), 0) || 0), borderColor: '#22d3ee', backgroundColor: 'rgba(34, 211, 238, .12)', fill: true },
        { label: 'Quoted value', data: trendBuckets.value.map((bucket) => props.trendRows?.filter((row) => row.bucket === bucket).reduce((sum, row) => sum + Number(row.quoted || 0), 0) || 0), borderColor: '#60a5fa', backgroundColor: 'rgba(96, 165, 250, .12)', fill: true },
    ],
}));
const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#cbd5e1' } } }, scales: { x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,.12)' } }, y: { beginAtZero: true, ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,.12)' } } } };
const typeData = computed(() => ({ labels: Object.keys(props.kpis?.by_type || {}).map((type) => typeLabels[type] || type), datasets: [{ data: Object.values(props.kpis?.by_type || {}), backgroundColor: Object.keys(props.kpis?.by_type || {}).map((type) => typeColors[type] || '#64748b'), borderWidth: 0 }] }));
</script>

<template>
    <AuthenticatedLayout>
        <div class="analytics-page px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
            <div class="analytics-shell mx-auto max-w-7xl space-y-6">
                <header class="analytics-header flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_14px_rgba(52,211,153,0.7)]"></span>
                            Business intelligence
                        </div>
                        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-white">Analytics</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-slate-400">A clear read on document activity, value, and account health.</p>
                    </div>
                    <div class="analytics-filters flex flex-wrap items-center gap-2">
                        <select v-model="preset" aria-label="Date range" class="analytics-control rounded-lg border bg-slate-900 px-3 py-2 text-sm text-slate-200" @change="applyFilters">
                            <option value="today">Today</option><option value="7d">Last 7 days</option><option value="30d">Last 30 days</option><option value="this_month">This month</option><option value="last_month">Last month</option><option value="this_year">This year</option>
                        </select>
                        <select v-if="users" v-model="selectedUser" aria-label="Filter by user" class="analytics-control rounded-lg border bg-slate-900 px-3 py-2 text-sm text-slate-200" @change="applyFilters">
                            <option value="">All users</option><option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <a :href="route('analytics.export', { preset, types: selectedTypes, user_id: selectedUser || undefined })" class="analytics-export rounded-lg border px-3 py-2 text-sm font-medium">Export CSV</a>
                    </div>
                </header>

                <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <div v-for="(card, index) in [{ label: 'Documents', value: count(kpis?.total_documents) }, { label: 'Billed value', value: money(kpis?.total_billed) }, { label: 'Quoted value', value: money(kpis?.total_quoted) }, { label: 'Discounts', value: money(kpis?.total_discounts) }, { label: 'SOA average', value: money(kpis?.average_by_type?.soa) }]" :key="card.label" class="analytics-panel analytics-kpi rounded-xl p-4">
                        <div class="flex items-center justify-between"><p class="text-sm text-slate-400">{{ card.label }}</p><span class="analytics-kpi-index">0{{ index + 1 }}</span></div><p class="mt-4 text-2xl font-semibold text-white">{{ card.value }}</p>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-2">
                    <div class="analytics-panel rounded-xl p-5"><div class="mb-4 flex items-center justify-between"><div><p class="analytics-section-label">Activity</p><h2 class="mt-1 font-semibold text-white">Document volume</h2></div><label class="flex items-center gap-2 text-xs text-slate-400"><input v-model="compare" type="checkbox" class="rounded border-slate-700 bg-slate-800" @change="applyFilters"> Compare</label></div><div class="h-72"><Bar :data="trendData" :options="{ ...chartOptions, scales: { ...chartOptions.scales, x: { ...chartOptions.scales.x, stacked: true }, y: { ...chartOptions.scales.y, stacked: true } } }" /></div></div>
                    <div class="analytics-panel rounded-xl p-5"><p class="analytics-section-label">Value movement</p><h2 class="mt-1 mb-4 font-semibold text-white">Billed and quoted value</h2><div class="h-72"><Line :data="valueData" :options="chartOptions" /></div></div>
                </section>

                <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="analytics-panel rounded-xl p-5"><p class="analytics-section-label">Relationships</p><h2 class="mt-1 mb-4 font-semibold text-white">Top clients by billed value</h2><div class="overflow-x-auto"><table class="w-full min-w-136 text-left text-sm"><thead class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-500"><tr><th class="pb-3">Client</th><th class="pb-3">Documents</th><th class="pb-3 text-right">Billed</th></tr></thead><tbody><tr v-for="client in clients" :key="client.name" class="border-b border-slate-800/70"><td class="py-3 font-medium text-slate-200">{{ client.name }}</td><td class="py-3 text-slate-400">{{ count(client.document_count) }}</td><td class="py-3 text-right text-cyan-200">{{ money(client.billed_value) }}</td></tr><tr v-if="!clients?.length"><td colspan="3" class="py-8 text-center text-slate-500">No client data for this period.</td></tr></tbody></table></div></div>
                    <div class="analytics-panel rounded-xl p-5"><p class="analytics-section-label">Composition</p><h2 class="mt-1 mb-4 font-semibold text-white">Document mix</h2><div class="mx-auto h-52 max-w-xs"><Doughnut :data="typeData" :options="{ ...chartOptions, plugins: { ...chartOptions.plugins, legend: { position: 'bottom', labels: { color: '#cbd5e1' } } } }" /></div></div>
                </section>

                <section class="grid gap-6 md:grid-cols-3">
                    <div class="analytics-panel rounded-xl p-5"><h2 class="font-semibold text-white">Sharing</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-400">Created</dt><dd>{{ count(sharing?.created) }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Active</dt><dd class="text-emerald-300">{{ count(sharing?.active) }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Expired</dt><dd class="text-amber-300">{{ count(sharing?.expired) }}</dd></div></dl></div>
                    <div class="analytics-panel rounded-xl p-5"><h2 class="font-semibold text-white">Status</h2><dl class="mt-4 space-y-3 text-sm"><div v-for="(value, key) in status" :key="key" class="flex justify-between"><dt class="capitalize text-slate-400">{{ key }}</dt><dd>{{ count(value) }}</dd></div></dl></div>
                    <div class="analytics-panel rounded-xl p-5"><h2 class="font-semibold text-white">Data health</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-400">Missing control no.</dt><dd>{{ count(quality?.missing_control_number) }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Zero-item documents</dt><dd>{{ count(quality?.zero_items) }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Duplicate control no.</dt><dd>{{ count(quality?.duplicate_control_numbers) }}</dd></div></dl></div>
                </section>

                <section v-if="people" class="analytics-panel rounded-xl p-5"><p class="analytics-section-label">Administration</p><h2 class="mt-1 mb-4 font-semibold text-white">People</h2><div class="overflow-x-auto"><table class="w-full min-w-136 text-left text-sm"><thead class="border-b border-slate-800 text-xs uppercase tracking-wider text-slate-500"><tr><th class="pb-3">User</th><th class="pb-3">Documents</th><th class="pb-3 text-right">Billed value</th></tr></thead><tbody><tr v-for="user in people.leaderboard" :key="user.id" class="border-b border-slate-800/70"><td class="py-3">{{ user.name }}</td><td class="py-3 text-slate-400">{{ count(user.document_count) }}</td><td class="py-3 text-right text-cyan-200">{{ money(user.billed_value) }}</td></tr></tbody></table></div></section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.analytics-page {
    min-height: calc(100vh - 9rem);
    background:
        radial-gradient(circle at 86% 0%, rgba(34, 211, 238, 0.08), transparent 28rem),
        linear-gradient(135deg, rgba(15, 23, 42, 0.25), rgba(2, 6, 23, 0.96));
    margin: -2rem;
}

.analytics-header {
    border-bottom: 1px solid rgba(148, 163, 184, 0.14);
    padding-bottom: 1.5rem;
}

.analytics-panel {
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.72);
    box-shadow: 0 18px 50px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(18px);
}

.analytics-kpi:first-child {
    border-top: 2px solid rgba(52, 211, 153, 0.7);
}

.analytics-kpi-index,
.analytics-section-label {
    color: rgba(52, 211, 153, 0.82);
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.analytics-control {
    border-color: rgba(148, 163, 184, 0.2);
}

.analytics-control:focus {
    border-color: rgba(52, 211, 153, 0.8);
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.12);
    outline: none;
}

.analytics-export {
    border-color: rgba(52, 211, 153, 0.42);
    color: #a7f3d0;
}

.analytics-export:hover {
    background: rgba(52, 211, 153, 0.1);
}

@media (prefers-reduced-motion: no-preference) {
    .analytics-panel {
        animation: analytics-rise 0.55s ease-out both;
    }

    .analytics-panel:nth-child(2) {
        animation-delay: 70ms;
    }

    .analytics-panel:nth-child(3) {
        animation-delay: 140ms;
    }
}

@keyframes analytics-rise {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>