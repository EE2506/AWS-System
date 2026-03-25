<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
});

const form = useForm({
    role: '',
});

const editingUser = ref(null);

const editUser = (user) => {
    editingUser.value = user.id;
    // Assuming user has one role for now, as per business logic (admin or user)
    form.role = user.roles[0]?.name || 'user';
};

const cancelEdit = () => {
    editingUser.value = null;
    form.reset();
};

const updateRole = (user) => {
    form.patch(route('admin.users.update-role', user.id), {
        onSuccess: () => {
            editingUser.value = null;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Start Managing Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                User Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 dark:border-slate-700">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-medium">
                                    <th class="px-6 py-4">Name</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Documents</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div v-if="editingUser === user.id" class="flex items-center gap-2">
                                            <select 
                                                v-model="form.role"
                                                class="text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="user">User</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                        <span 
                                            v-else
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                            :class="{
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': user.roles[0]?.name === 'admin',
                                                'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300': user.roles[0]?.name !== 'admin'
                                            }"
                                        >
                                            {{ user.roles[0]?.name || 'No Role' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': user.is_active,
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': !user.is_active
                                            }"
                                        >
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ user.documents_count }} docs
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3 items-center">
                                            <!-- Edit Role -->
                                            <div v-if="editingUser === user.id" class="flex items-center gap-2">
                                                <button @click="updateRole(user)" class="text-green-600 hover:text-green-900 font-semibold">Save</button>
                                                <button @click="cancelEdit" class="text-slate-500 hover:text-slate-700">Cancel</button>
                                            </div>
                                            <button 
                                                v-else 
                                                @click="editUser(user)" 
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 hover:underline"
                                            >
                                                Edit Role
                                            </button>

                                            <!-- Toggle Status -->
                                            <Link 
                                                :href="route('admin.users.toggle-status', user.id)" 
                                                method="patch" 
                                                as="button"
                                                preserve-scroll
                                                class="text-xs px-2 py-1 rounded border transition-colors"
                                                :class="{
                                                    'border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20': user.is_active,
                                                    'border-green-200 text-green-600 hover:bg-green-50 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/20': !user.is_active
                                                }"
                                            >
                                                {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <div v-if="users.links.length > 3" class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                        <Pagination :links="users.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
