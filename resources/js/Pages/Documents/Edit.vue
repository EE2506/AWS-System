<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DocumentForm from '@/Components/Documents/DocumentForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    document: {
        type: Object,
        required: true,
    },
    documentTypes: {
        type: Object,
        required: true,
    },
    recentClients: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="`Edit ${document.control_number || 'Document'}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Document
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <DocumentForm 
                    :document="document"
                    :document-types="documentTypes"
                    :recent-clients="recentClients"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
