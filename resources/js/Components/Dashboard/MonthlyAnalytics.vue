<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    analytics: Object,  // full monthly analytics structure
    isAdmin: Boolean,   // controls ₱ visibility
});

// ─── Peso formatter ───────────────────────────────────────────────────────────
const formatPeso = (val) => {
    if (!props.isAdmin) return '₱ ---';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(val || 0);
};

// ─── Section B: Status breakdown rows ────────────────────────────────────────
const statusRows = computed(() => {
    const sb = props.analytics?.status_breakdown ?? { all: 0, draft: 0, finalized: 0, paid: 0 };
    const all = sb.all || 1;

    return [
        { label: 'All',       count: sb.all,      color: 'bg-blue-500',  pct: 100 },
        { label: 'Drafts',    count: sb.draft,     color: 'bg-gray-400',  pct: (sb.draft      / all) * 100 },
        { label: 'Finalized', count: sb.finalized, color: 'bg-amber-400', pct: (sb.finalized  / all) * 100 },
        { label: 'Paid',      count: sb.paid,      color: 'bg-green-500', pct: (sb.paid       / all) * 100 },
    ].map((row) => ({
        ...row,
        displayPct: ((row.count / all) * 100).toFixed(1),
    }));
});

// ─── Section C: Stacked bar chart ─────────────────────────────────────────────
const chartData = computed(() => {
    const v = props.analytics?.volume_by_type ?? {
        labels: [], soa: [], purchase_order: [], quotation: [], delivery_receipt: [],
    };
    return {
        labels: v.labels,
        datasets: [
            { label: 'SOA',              backgroundColor: '#378ADD', data: v.soa             },
            { label: 'Purchase Order',   backgroundColor: '#EF9F27', data: v.purchase_order  },
            { label: 'Quotation',        backgroundColor: '#1D9E75', data: v.quotation        },
            { label: 'Delivery Receipt', backgroundColor: '#D4537E', data: v.delivery_receipt },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: { stacked: true },
        y: { stacked: true },
    },
    plugins: {
        legend: { display: false },
    },
};

// ─── Section D: Top Recipients ────────────────────────────────────────────────
const maxRecipientValue = computed(() => {
    const r = props.analytics?.top_recipients ?? [];
    if (!r.length) return 1;
    return Math.max(...r.map((x) => x.total_value)) || 1;
});
</script>

<template>
    <div class="mt-8 mb-4 space-y-6">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Monthly Analytics
            </h2>
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                {{ analytics?.selected_month }}
            </span>
        </div>

        <!-- ── Section A: KPI Cards ── -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Total Docs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Total Docs This Month</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ analytics?.kpis?.total_docs ?? 0 }}
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Total Value</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ formatPeso(analytics?.kpis?.total_value) }}
                </div>
            </div>

            <!-- Finalized Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Finalized Rate</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ Number(analytics?.kpis?.finalized_rate ?? 0).toFixed(1) }}%
                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500 ml-1">finalized</span>
                </div>
            </div>

            <!-- Paid Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Paid Rate</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ Number(analytics?.kpis?.paid_rate ?? 0).toFixed(1) }}%
                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500 ml-1">paid</span>
                </div>
            </div>
        </div>

        <!-- ── Sections B + D side-by-side ── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Section B: Status Breakdown (Tailwind bars, no chart lib) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Status Breakdown</h3>
                <div class="space-y-4">
                    <div
                        v-for="(row, idx) in statusRows"
                        :key="idx"
                        class="flex items-center gap-3"
                    >
                        <!-- Label -->
                        <div class="w-24 shrink-0 text-sm font-medium text-gray-600 dark:text-gray-300">
                            {{ row.label }}
                        </div>

                        <!-- Bar track -->
                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 h-3 rounded-full overflow-visible">
                            <div
                                :class="['h-full rounded-full transition-all duration-500', row.color]"
                                :style="{
                                    width: row.pct > 0 ? row.pct + '%' : '0%',
                                    minWidth: row.count > 0 ? '4px' : '0px',
                                }"
                            ></div>
                        </div>

                        <!-- Count badge -->
                        <span class="inline-flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-semibold px-2 py-0.5 rounded min-w-[2rem] text-center">
                            {{ row.count }}
                        </span>

                        <!-- Percentage -->
                        <div class="w-12 text-right text-xs text-gray-500 dark:text-gray-400">
                            {{ row.displayPct }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section D: Top 5 Recipients -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Top 5 Recipients</h3>

                <div v-if="!analytics?.top_recipients?.length" class="text-gray-500 dark:text-gray-400 text-sm">
                    No recipient data for this month.
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(recipient, idx) in analytics?.top_recipients"
                        :key="idx"
                        class="space-y-1"
                    >
                        <!-- Rank + Name + Value -->
                        <div class="flex items-center justify-between text-sm gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="shrink-0 w-5 text-xs font-bold text-gray-400 dark:text-gray-500 font-mono text-center">
                                    {{ idx + 1 }}.
                                </span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 truncate">
                                    {{ recipient.name }}
                                </span>
                            </div>
                            <div class="shrink-0 font-semibold text-gray-900 dark:text-gray-100 text-right">
                                {{ formatPeso(recipient.total_value) }}
                            </div>
                        </div>

                        <!-- Proportional bar (admin only) -->
                        <div
                            v-if="isAdmin"
                            class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden"
                        >
                            <div
                                class="bg-blue-400 h-full rounded-full transition-all duration-500"
                                :style="{ width: ((recipient.total_value / maxRecipientValue) * 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section C: Stacked Bar Chart (full width) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Document Volume — Rolling 6 Months</h3>

                <!-- Custom HTML legend -->
                <div class="flex flex-wrap gap-x-5 gap-y-2 mb-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded" style="background-color: #378ADD;"></div> SOA
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded" style="background-color: #EF9F27;"></div> Purchase Order
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded" style="background-color: #1D9E75;"></div> Quotation
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded" style="background-color: #D4537E;"></div> Delivery Receipt
                    </div>
                </div>

                <!-- Chart: 220px fixed height, position relative required by vue-chartjs -->
                <div style="height: 220px; position: relative;">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>

        </div>
    </div>
</template>
