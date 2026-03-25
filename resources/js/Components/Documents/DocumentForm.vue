<script setup>
import { computed, watch, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    document: {
        type: Object,
        default: null,
    },
    documentTypes: {
        type: Object,
        required: true,
    },
    initialType: {
        type: String,
        default: 'soa',
    },
    recentClients: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    type: props.document?.type || props.initialType,
    document_date: props.document?.document_date ? props.document.document_date.split('T')[0] : new Date().toISOString().split('T')[0],
    control_number: props.document?.control_number || '',
    recipient_name: props.document?.recipient_name || '',
    recipient_email: props.document?.recipient_email || '',
    recipient_phone: props.document?.recipient_phone || '',
    recipient_address: props.document?.recipient_address || '',
    items: props.document?.items?.map(item => ({
        name: item.name,
        description: item.description,
        quantity: item.quantity,
        unit_cost: item.unit_cost,
        remarks: item.remarks,
    })) || [
        { name: '', description: '', quantity: 1, unit_cost: 0, remarks: '' }
    ],
    discount: props.document?.discount || 0,
});

// Auto-Suggest Logic
const showClientSuggestions = ref(false);

const filteredClients = computed(() => {
    if (!form.recipient_name) return props.recentClients.slice(0, 5); // Show first 5 if empty
    
    const query = form.recipient_name.toLowerCase();
    return props.recentClients.filter(client => 
        client.recipient_name && client.recipient_name.toLowerCase().includes(query)
    ).slice(0, 5);
});

const selectClient = (client) => {
    form.recipient_name = client.recipient_name;
    form.recipient_email = client.recipient_email || form.recipient_email;
    form.recipient_phone = client.recipient_phone || form.recipient_phone;
    form.recipient_address = client.recipient_address || form.recipient_address;
    
    showClientSuggestions.value = false;
};

const closeSuggestionsDelayed = () => {
    setTimeout(() => {
        showClientSuggestions.value = false;
    }, 200);
};

// Type-specific label for the reference number field
const referenceLabel = computed(() => {
    const labels = {
        quotation: 'Control#',
        purchase_order: 'Control No.',
        soa: 'SOA no.',
        delivery_receipt: 'Reference',
    };
    return labels[form.type] || 'Reference No.';
});

// Auto-calculate item totals and grand total
const itemsWithTotal = computed(() => {
    return form.items.map(item => ({
        ...item,
        total: (item.quantity || 0) * (item.unit_cost || 0),
    }));
});

const subtotal = computed(() => {
    return itemsWithTotal.value.reduce((sum, item) => sum + item.total, 0);
});

const grandTotal = computed(() => {
    return Math.max(0, subtotal.value - (form.discount || 0));
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_cost: 0, remarks: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    if (props.document) {
        form.put(route('documents.update', props.document.id));
    } else {
        form.post(route('documents.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">
        <!-- Header / Basics -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Document Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type -->
                <div>
                    <InputLabel value="Document Type" />
                    <select
                        v-model="form.type"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:border-cyan-500 focus:ring-cyan-500 dark:focus:border-cyan-500 dark:focus:ring-cyan-500 hover:border-cyan-400 dark:hover:border-cyan-400 hover:ring hover:ring-cyan-400/30 rounded-lg shadow-sm transition-all duration-300"
                        :disabled="!!document"
                    >
                        <option v-for="(label, key) in documentTypes" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <!-- Date -->
                <div>
                    <InputLabel value="Date" />
                    <TextInput
                        v-model="form.document_date"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.document_date" class="mt-2" />
                </div>

                <!-- Control Number (auto-generated on create, read-only on edit) -->
                <div v-if="document">
                    <InputLabel :value="referenceLabel" />
                    <div class="mt-1 px-4 py-2 bg-gray-100 dark:bg-gray-800/50 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-400 font-mono text-sm shadow-sm transition-colors">
                        {{ form.control_number || 'Auto-assigned' }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Control number is auto-assigned and cannot be changed.
                    </p>
                </div>
            </div>
        </div>

        <!-- Recipient Info -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recipient Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative">
                    <InputLabel value="Client Name" />
                    <TextInput 
                        v-model="form.recipient_name" 
                        type="text" 
                        class="mt-1 block w-full" 
                        placeholder="Existing Client or New Name"
                        autocomplete="off"
                        @focus="showClientSuggestions = true"
                        @blur="closeSuggestionsDelayed"
                    />
                    
                    <!-- Auto-Suggest Dropdown -->
                    <transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <ul 
                            v-if="showClientSuggestions && filteredClients.length > 0"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-[0_4px_20px_-4px_rgba(0,0,0,0.5)] dark:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.7)] overflow-hidden"
                        >
                            <li 
                                v-for="(client, index) in filteredClients" 
                                :key="index"
                                @click="selectClient(client)"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/80 text-gray-800 dark:text-gray-200 transition-colors"
                            >
                                <div class="font-medium truncate">{{ client.recipient_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate flex items-center gap-2" v-if="client.recipient_email || client.recipient_phone">
                                    <span v-if="client.recipient_email" class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        {{ client.recipient_email }}
                                    </span>
                                    <span v-if="client.recipient_phone" class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                        {{ client.recipient_phone }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </transition>

                    <InputError :message="form.errors.recipient_name" class="mt-2" />
                </div>
                <div>
                    <InputLabel value="Email (Optional)" />
                    <TextInput v-model="form.recipient_email" type="email" class="mt-1 block w-full" />
                    <InputError :message="form.errors.recipient_email" class="mt-2" />
                </div>
                <div>
                    <InputLabel value="Phone (Optional)" />
                    <TextInput v-model="form.recipient_phone" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.recipient_phone" class="mt-2" />
                </div>
                <div>
                    <InputLabel value="Address (Optional)" />
                    <TextInput v-model="form.recipient_address" type="text" class="mt-1 block w-full" />
                    <InputError :message="form.errors.recipient_address" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
             <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Line Items</h3>
                <button
                    type="button"
                    @click="addItem"
                    class="px-3 py-1 text-sm font-medium text-cyan-600 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-900/30 rounded hover:bg-cyan-100 dark:hover:bg-cyan-900/50 transition-colors"
                >
                    + Add Item
                </button>
            </div>

            <!-- Mobile Line Items -->
            <div class="block md:hidden space-y-4">
                <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-700">
                     <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-500">Item {{ index + 1 }}</span>
                        <button
                            type="button"
                            @click="removeItem(index)"
                            class="text-red-400 hover:text-red-600 transition-colors p-1"
                            v-if="form.items.length > 1"
                        >
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                         <div>
                            <InputLabel :value="`Item Name`" class="mb-1" />
                            <TextInput v-model="item.name" type="text" class="w-full text-sm" placeholder="Item Name" />
                            <InputError :message="form.errors[`items.${index}.name`]" class="mt-1" />
                            
                            <InputLabel :value="`Description (Optional)`" class="mb-1 mt-2" />
                            <TextInput v-model="item.description" type="text" class="w-full text-sm text-gray-500" placeholder="Description" />
                            <InputError :message="form.errors[`items.${index}.description`]" class="mt-1" />
                         </div>
                         
                         <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel :value="`Qty`" class="mb-1" />
                                <TextInput v-model="item.quantity" type="number" min="1" class="w-full text-sm" />
                            </div>
                            <div>
                                <InputLabel :value="`Unit Cost`" class="mb-1" />
                                <TextInput v-model="item.unit_cost" type="number" step="0.01" class="w-full text-sm" />
                            </div>
                         </div>
                         
                         <div>
                             <InputLabel :value="`Remarks (Optional)`" class="mb-1" />
                             <TextInput v-model="item.remarks" type="text" class="w-full text-sm" />
                         </div>
                         
                         <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-600">
                             <span class="text-sm font-medium text-gray-500">Total</span>
                             <span class="font-mono text-gray-900 dark:text-white font-bold">
                                ₱{{ ((item.quantity || 0) * (item.unit_cost || 0)).toFixed(2) }}
                             </span>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Line Items Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 text-sm">
                            <th class="py-2 px-2 w-12">#</th>
                            <th class="py-2 px-2 w-1/3 min-w-[200px]">Description</th>
                            <th class="py-2 px-2 w-24">Qty</th>
                            <th class="py-2 px-2 w-32">Unit Cost</th>
                            <th class="py-2 px-2 w-32">Total</th>
                            <th class="py-2 px-2">Remarks</th>
                            <th class="py-2 px-2 w-12">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="(item, index) in form.items" :key="index" class="group">
                            <td class="py-3 px-2 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="py-3 px-2">
                                <TextInput v-model="item.name" type="text" class="w-full text-sm mb-1" placeholder="Item Name" />
                                <InputError :message="form.errors[`items.${index}.name`]" class="mt-1" />
                                <TextInput v-model="item.description" type="text" class="w-full text-xs text-gray-500" placeholder="Description (Optional)" />
                                <InputError :message="form.errors[`items.${index}.description`]" class="mt-1" />
                            </td>
                            <td class="py-3 px-2">
                                <TextInput v-model="item.quantity" type="number" min="1" class="w-full text-sm" />
                            </td>
                            <td class="py-3 px-2">
                                <TextInput v-model="item.unit_cost" type="number" step="0.01" class="w-full text-sm" />
                            </td>
                            <td class="py-3 px-2 font-mono text-sm text-right text-gray-700 dark:text-gray-300">
                                ₱{{ ((item.quantity || 0) * (item.unit_cost || 0)).toFixed(2) }}
                            </td>
                            <td class="py-3 px-2">
                                <TextInput v-model="item.remarks" type="text" class="w-full text-sm" placeholder="Optional" />
                            </td>
                            <td class="py-3 px-2 text-center">
                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    class="text-red-400 hover:text-red-600 transition-colors p-1"
                                    title="Remove Item"
                                    v-if="form.items.length > 1"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="mt-6 flex justify-end">
                <div class="w-full md:w-1/3 space-y-3">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span class="font-mono">₱{{ subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                        <span>Discount</span>
                        <TextInput v-model="form.discount" type="number" step="0.01" class="w-32 text-right !h-8 !py-0 !text-sm" />
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-600 pt-3">
                        <span>Total Amount</span>
                        <span class="font-mono">₱{{ grandTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
             <Link
                :href="route('documents.index')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-colors"
            >
                Cancel
            </Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ document ? 'Update Document' : 'Create Document' }}
            </PrimaryButton>
        </div>


        <!-- Loading Overlay -->
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="form.processing" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl flex flex-col items-center max-w-sm w-full mx-4 border border-gray-100 dark:border-gray-700">
                    <!-- Spinner -->
                    <div class="relative w-16 h-16 mb-4">
                        <div class="absolute inset-0 border-4 border-gray-200 dark:border-gray-700 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-cyan-500 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">
                        {{ document ? 'Updating Document...' : 'Creating Document...' }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-center text-sm">
                        Please wait while we process your request.
                    </p>
                </div>
            </div>
        </Transition>
    </form>
</template>
