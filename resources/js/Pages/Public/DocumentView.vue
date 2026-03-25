<script setup>
import { Head } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { computed } from 'vue';

const props = defineProps({
    document: Object,
    documentTypes: Object,
    allowDownload: Boolean,
    expiresAt: String,
    daysRemaining: Number,
});

const currentUrl = window.location.href;

const isExpiringSoon = computed(() => props.daysRemaining !== null && props.daysRemaining <= 1);
</script>

<template>
    <Head :title="`${documentTypes[document.type]} - ${document.control_number}`" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 pb-12">
        <!-- Public Header -->
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-8 text-cyan-600 dark:text-cyan-400 fill-current" />
                    <span class="text-lg font-bold text-gray-800 dark:text-white">AWS System</span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Secure Public Document
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- CONFIDENTIALITY & EXPIRATION BANNER                -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="mb-6 rounded-xl overflow-hidden border border-blue-300 dark:border-blue-500/30 shadow-lg shadow-blue-500/5">
                <!-- Main blue privacy notice -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 px-5 py-4">
                    <div class="flex items-start gap-3">
                        <!-- Shield Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="w-6 h-6 text-blue-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-bold text-sm uppercase tracking-wide mb-1.5">
                                🔒 Confidential Document — Do Not Share
                            </h3>
                            <ul class="space-y-1.5 text-blue-100 text-sm leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-200 mt-0.5">•</span>
                                    <span>This document is intended <strong class="text-white">exclusively for the named recipient</strong>. Please do not share, forward, or distribute this link to anyone.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-200 mt-0.5">•</span>
                                    <span>Unauthorized sharing may result in the link being <strong class="text-white">immediately revoked</strong> without prior notice.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-200 mt-0.5">•</span>
                                    <span>All access to this document is <strong class="text-white">logged and monitored</strong> for security purposes.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Expiration countdown bar -->
                <div
                    v-if="daysRemaining !== null"
                    class="px-5 py-3 flex flex-wrap items-center justify-between gap-3"
                    :class="isExpiringSoon
                        ? 'bg-amber-50 dark:bg-amber-900/30 border-t border-amber-200 dark:border-amber-700/50'
                        : 'bg-blue-50 dark:bg-blue-900/20 border-t border-blue-200 dark:border-blue-700/30'"
                >
                    <div class="flex items-center gap-2">
                        <svg
                            class="w-5 h-5"
                            :class="isExpiringSoon ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span
                            class="text-sm font-semibold"
                            :class="isExpiringSoon ? 'text-amber-800 dark:text-amber-300' : 'text-blue-800 dark:text-blue-300'"
                        >
                            <template v-if="isExpiringSoon">
                                ⚠ This link is about to expire!
                            </template>
                            <template v-else>
                                ⏱ This link expires in {{ daysRemaining }} day{{ daysRemaining !== 1 ? 's' : '' }}
                            </template>
                        </span>
                    </div>

                    <!-- Urgent message when expiring soon -->
                    <span
                        v-if="isExpiringSoon"
                        class="text-xs text-amber-700 dark:text-amber-400"
                    >
                        Please contact the company to request a new link if needed.
                    </span>

                    <!-- Days remaining pill -->
                    <span
                        v-else
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                        :class="'bg-blue-600 text-white'"
                    >
                        {{ daysRemaining }} day{{ daysRemaining !== 1 ? 's' : '' }} remaining
                    </span>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ document.control_number}}
                    </h1>
                    <p class="text-gray-500">
                        {{ documentTypes[document.type] }}
                    </p>
                </div>
                <div>
                    <a 
                        v-if="allowDownload"
                        :href="`${currentUrl}/download`" 
                        class="inline-flex items-center px-4 py-2 bg-cyan-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cyan-500 focus:bg-cyan-700 active:bg-cyan-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download PDF
                    </a>
                </div>
            </div>

            <!-- Document Content (Reused styles from Show.vue) -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <!-- Recipient Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Recipient</h3>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ document.recipient_name }}</p>
                        <p class="text-gray-600 dark:text-gray-400" v-if="document.recipient_email">{{ document.recipient_email }}</p>
                        <p class="text-gray-600 dark:text-gray-400" v-if="document.recipient_phone">{{ document.recipient_phone }}</p>
                        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line mt-2" v-if="document.recipient_address">{{ document.recipient_address }}</p>
                    </div>
                    <div class="text-right">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Details</h3>
                            <div class="space-y-1">
                            <div class="flex justify-end gap-4">
                                <span class="text-gray-500">Date:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ new Date(document.document_date).toLocaleDateString() }}</span>
                            </div>
                                <div class="flex justify-end gap-4">
                                <span class="text-gray-500">Issued By:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ document.user?.name }}</span>
                            </div>
                            </div>
                    </div>
                </div>

                <!-- Mobile Items View -->
                <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="(item, index) in document.items" :key="item.id" class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-500">Item {{ index + 1 }}</span>
                            <span class="font-mono font-medium text-gray-900 dark:text-white">
                                {{ Number(item.total_cost).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                            </span>
                        </div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400" v-if="item.description">{{ item.description }}</div>
                        <div class="text-xs text-gray-500 italic" v-if="item.remarks">{{ item.remarks }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 flex justify-between">
                            <span>{{ item.quantity }} x ₱{{ Number(item.unit_cost).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop Items Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-500 dark:text-gray-400">
                                <th class="px-6 py-4 w-16">#</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4 w-24 text-center">Qty</th>
                                <th class="px-6 py-4 w-32 text-right">Unit Cost</th>
                                <th class="px-6 py-4 w-32 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(item, index) in document.items" :key="item.id">
                                <td class="px-6 py-4 text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400" v-if="item.description">{{ item.description }}</div>
                                    <div class="text-xs text-gray-500 mt-1" v-if="item.remarks">{{ item.remarks }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300">{{ item.quantity }}</td>
                                <td class="px-6 py-4 text-right font-mono text-gray-600 dark:text-gray-300">
                                    ₱{{ Number(item.unit_cost).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-medium text-gray-900 dark:text-white">
                                    ₱{{ Number(item.total_cost).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Totals -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-end">
                        <div class="w-full sm:w-1/3 space-y-3">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span class="font-mono">
                                    ₱{{ parseFloat((Number(document.total_amount) + Number(document.discount)).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400" v-if="Number(document.discount) > 0">
                                <span>Discount</span>
                                <span class="font-mono text-red-500">
                                    -₱{{ Number(document.discount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-600 pt-3">
                                <span>Total Amount</span>
                                <span class="font-mono">
                                    ₱{{ Number(document.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center text-sm text-gray-500 space-y-2">
                <p class="italic">"The Lord detests dishonest scales, but He delights in accurate weights." — Proverbs 11:1</p>
                <p>&copy; {{ new Date().getFullYear() }} AWS System. All rights reserved.</p>
            </div>
        </main>
    </div>
</template>
