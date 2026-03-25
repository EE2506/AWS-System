<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    documentTypes: Object,
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const status = ref(props.filters.status || '');

// Debounce search
let timeout;
watch([search, type, status], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            route('documents.index'),
            { search: search.value, type: type.value, status: status.value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const getStatusColor = (status) => {
    if (status === 'paid') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
    return status === 'finalized' 
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// Delete modal
const deleteForm = useForm({});
const confirmingDelete = ref(false);
const documentToDelete = ref(null);

const confirmDelete = (doc) => {
    documentToDelete.value = doc;
    confirmingDelete.value = true;
};

const deleteDocument = () => {
    deleteForm.delete(route('documents.destroy', documentToDelete.value.id), {
        onSuccess: () => {
            confirmingDelete.value = false;
            documentToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Documents" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Documents
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 space-y-4">
                    <!-- Top Row: Search & Type -->
                    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Search by name or control number..."
                                class="w-full"
                            />
                        </div>
                        <div class="w-full sm:w-48">
                            <select
                                v-model="type"
                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-md shadow-sm"
                            >
                                <option value="">All Types</option>
                                <option v-for="(label, key) in documentTypes" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Tabs + Create New -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex gap-2 overflow-x-auto pb-1">
                        <button 
                            @click="status = ''"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                            :class="status === '' ? 'bg-gray-800 text-white dark:bg-gray-200 dark:text-gray-800' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'"
                        >
                            All Documents
                        </button>
                        <button 
                            @click="status = 'draft'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                            :class="status === 'draft' ? 'bg-gray-800 text-white dark:bg-gray-200 dark:text-gray-800' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'"
                        >
                            Drafts
                        </button>
                        <button 
                            @click="status = 'finalized'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                            :class="status === 'finalized' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'"
                        >
                            Completed
                        </button>
                        <button 
                            @click="status = 'paid'"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                            :class="status === 'paid' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm'"
                        >
                            Paid
                        </button>
                        </div>

                        <Link
                            :href="route('documents.create')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg shadow-sm transition-colors whitespace-nowrap flex-shrink-0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create New
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <!-- Mobile Card View -->
                    <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-if="documents.data.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                             No documents found.
                        </div>
                        <div v-for="doc in documents.data" :key="doc.id" class="p-4 space-y-3 bg-white dark:bg-gray-800">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ doc.control_number || 'DRAFT' }}</h3>
                                    <span class="text-xs text-gray-500">{{ documentTypes[doc.type] }}</span>
                                </div>
                                <span 
                                    class="inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full"
                                    :class="getStatusColor(doc.status)"
                                >
                                    {{ doc.status }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <div class="font-medium">{{ doc.recipient_name }}</div>
                                <div class="text-xs text-gray-400" v-if="doc.recipient_email">{{ doc.recipient_email }}</div>
                            </div>

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">{{ new Date(doc.document_date).toLocaleDateString() }}</span>
                                <span class="font-mono font-medium text-gray-900 dark:text-white">
                                    ₱{{ Number(doc.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                </span>
                            </div>

                            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <a 
                                    :href="route('documents.preview', doc.id)" 
                                    target="_blank"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900"
                                >
                                    PDF
                                </a>
                                <Link 
                                    :href="route('documents.show', doc.id)" 
                                    class="text-sm text-cyan-600 dark:text-cyan-400 hover:text-cyan-900"
                                >
                                    View
                                </Link>
                                <Link 
                                    :href="route('documents.edit', doc.id)" 
                                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="confirmDelete(doc)"
                                    class="text-sm text-red-500 dark:text-red-400 hover:text-red-700"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-medium">
                                    <th class="px-6 py-4">Control #</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4">Recipient</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-if="documents.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No documents found.
                                    </td>
                                </tr>
                                <tr 
                                    v-for="doc in documents.data" 
                                    :key="doc.id" 
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                >
                                    <td class="px-6 py-4 font-mono text-sm text-gray-600 dark:text-gray-300">
                                        {{ doc.control_number || 'DRAFT' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ documentTypes[doc.type] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ doc.recipient_name }}
                                        <div class="text-xs text-gray-400 font-mono mt-0.5" v-if="doc.recipient_email">
                                            {{ doc.recipient_email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ new Date(doc.document_date).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-right text-gray-900 dark:text-white">
                                        ₱{{ Number(doc.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span 
                                            class="inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full"
                                            :class="getStatusColor(doc.status)"
                                        >
                                            {{ doc.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a 
                                                :href="route('documents.preview', doc.id)" 
                                                target="_blank"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 hover:underline"
                                            >
                                                PDF
                                            </a>
                                            <Link 
                                                :href="route('documents.show', doc.id)" 
                                                class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-900 hover:underline"
                                            >
                                                View
                                            </Link>
                                             <Link 
                                                :href="route('documents.edit', doc.id)" 
                                                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="confirmDelete(doc)"
                                                class="text-red-500 dark:text-red-400 hover:text-red-700 hover:underline"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="documents.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <Pagination :links="documents.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDelete" @close="confirmingDelete = false">
            <div class="p-6">
                <!-- Warning Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <h2 class="text-lg font-bold text-center text-gray-900 dark:text-gray-100 mb-1">
                    Delete Document?
                </h2>
                <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-1">
                    You are about to permanently delete:
                </p>
                <p class="text-center font-mono font-bold text-red-600 dark:text-red-400 mb-4">
                    {{ documentToDelete?.control_number || 'this document' }}
                    <span class="font-normal text-gray-500 text-xs block">
                        {{ documentToDelete ? documentTypes[documentToDelete.type] : '' }} &bull; {{ documentToDelete?.recipient_name }}
                    </span>
                </p>
                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-6">
                    This action cannot be undone.
                </p>

                <div class="flex justify-center gap-3">
                    <SecondaryButton @click="confirmingDelete = false" :disabled="deleteForm.processing">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        @click="deleteDocument"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                    >
                        Yes, Delete It
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
