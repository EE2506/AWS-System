<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    document: Object,
    documentTypes: Object,
    can: Object,
});

const form = useForm({});
const shareForm = useForm({
    expires_in_days: 5,
});
const confirmDelete = ref(false);
const showShareOptions = ref(false);

const expiryOptions = [
    { label: '5 Days', value: 5 },
    { label: '10 Days', value: 10 },
    { label: '15 Days', value: 15 },
    { label: '30 Days', value: 30 },
];

const daysRemaining = computed(() => {
    if (!props.document.public_link?.expires_at) return null;
    const now = new Date();
    const expires = new Date(props.document.public_link.expires_at);
    const diff = Math.max(0, Math.ceil((expires - now) / (1000 * 60 * 60 * 24)));
    return diff;
});

const deleteDocument = () => {
    form.delete(route('documents.destroy', props.document.id), {
        onSuccess: () => confirmDelete.value = false,
    });
};

const createShare = () => {
    shareForm.post(route('documents.share', props.document.id), {
        onSuccess: () => showShareOptions.value = false,
    });
};


const revokeShare = () => {
    shareForm.delete(route('documents.revoke-share', props.document.id));
};

const toggleShare = () => {
    if (props.document.public_link) {
        revokeShare();
    } else {
        showShareOptions.value = true;
    }
};

const linkCopied = ref(false);

const copyLink = () => {
    const url = route('public.document.show', props.document.public_link.token);
    navigator.clipboard.writeText(url);
    linkCopied.value = true;
    setTimeout(() => linkCopied.value = false, 2500);
};
</script>

<template>
    <Head :title="`${documentTypes[document.type]} - ${document.control_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ document.control_number || 'Draft Document' }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ documentTypes[document.type] }} &bull; {{ new Date(document.document_date).toLocaleDateString() }}
                </p>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Action Buttons Row -->
                <div class="flex flex-wrap gap-2">
                    <a
                        :href="route('documents.preview', document.id)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-md transition-colors text-sm font-medium shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        View PDF
                    </a>

                    <a
                        :href="route('documents.download', document.id)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download PDF
                    </a>

                    <Link
                        v-if="document.status === 'draft' && can.change_status"
                        :href="route('documents.complete', document.id)"
                        method="patch"
                        as="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md transition-colors text-sm font-medium shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        Mark as Complete
                    </Link>

                    <Link
                        :href="route('documents.edit', document.id)"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-md transition-colors text-sm font-medium shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit
                    </Link>

                    <button
                        @click="confirmDelete = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-md transition-colors text-sm font-medium shadow-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete
                    </button>
                </div>

                <!-- Status & Sharing Card -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span 
                                class="px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wide"
                                :class="[
                                    document.status === 'finalized' ? 'bg-green-100 text-green-800' : '',
                                    document.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : '',
                                    document.status === 'draft' ? 'bg-gray-100 text-gray-800' : ''
                                ]"
                            >
                                {{ document.status }}
                            </span>
                            
                            <div v-if="document.public_link" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                <span>Publicly Shared</span>
                            </div>

                            <!-- Days Remaining Badge -->
                            <span
                                v-if="document.public_link && daysRemaining !== null"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                :class="daysRemaining <= 1
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <template v-if="daysRemaining <= 0">Expired</template>
                                <template v-else>{{ daysRemaining }} day{{ daysRemaining !== 1 ? 's' : '' }} left</template>
                            </span>
                        </div>

                        <div class="flex gap-2">
                             <Link
                                v-if="document.status === 'finalized' && can.change_status"
                                :href="route('documents.paid', document.id)"
                                method="patch"
                                as="button"
                                class="text-sm font-medium text-emerald-600 hover:text-emerald-700 underline mr-2"
                            >
                                Mark as Paid
                            </Link>
                            <button 
                                v-if="document.public_link" 
                                type="button"
                                @click="copyLink"
                                class="text-sm transition-colors duration-200"
                                :class="linkCopied ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-cyan-600 hover:underline'"
                            >
                                <span v-if="linkCopied" class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    Copied!
                                </span>
                                <span v-else>Copy Link</span>
                            </button>
                            <SecondaryButton @click="toggleShare" :disabled="shareForm.processing">
                                {{ document.public_link ? 'Revoke Access' : 'Share Publicly' }}
                            </SecondaryButton>
                        </div>
                    </div>

                    <!-- Inline Share Options Panel (expands within the card) -->
                    <div v-if="showShareOptions" class="border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-900/30 rounded-b-lg">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Share Document Publicly
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Choose how long this link should remain active. After the selected period, the link will automatically expire.
                        </p>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wide">
                                Link expires after
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="option in expiryOptions"
                                    :key="option.value"
                                    type="button"
                                    @click="shareForm.expires_in_days = option.value"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-150 border-2 cursor-pointer"
                                    :class="shareForm.expires_in_days === option.value
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-md'
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500'"
                                >
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    The recipient will see a confidentiality notice and a countdown showing how many days remain before the link expires.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <SecondaryButton type="button" @click="showShareOptions = false">Cancel</SecondaryButton>
                            <PrimaryButton type="button" @click="createShare" :disabled="shareForm.processing">
                                Generate Link
                            </PrimaryButton>
                        </div>
                    </div>
                </div>


                <!-- Document Content -->
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

                    <!-- Bible Verse Footer -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-6 border-t border-gray-200 dark:border-gray-700 text-center">
                        <p class="text-sm text-gray-500 italic">“The Lord detests dishonest scales, but He delights in accurate weights.” — Proverbs 11:1</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmDelete" @close="confirmDelete = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Are you sure you want to delete this document?
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-4">
                    <SecondaryButton @click="confirmDelete = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteDocument" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Delete Document
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
