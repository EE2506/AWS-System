<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    accent: {
        type: String,
        default: 'emerald', // 'red' for admin, 'emerald' for users
    },
});

const accentClasses = computed(() => {
    const accents = {
        red: {
            active: 'border-red-500 dark:border-red-400 text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/50 focus:text-red-800 dark:focus:text-red-200 focus:bg-red-100 dark:focus:bg-red-900 focus:border-red-700 dark:focus:border-red-300',
            inactive: 'hover:border-red-300 dark:hover:border-red-600',
        },
        emerald: {
            active: 'border-emerald-400 dark:border-emerald-500 text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/50 focus:text-emerald-800 dark:focus:text-emerald-200 focus:bg-emerald-100 dark:focus:bg-emerald-900 focus:border-emerald-700 dark:focus:border-emerald-300',
            inactive: 'hover:border-emerald-300 dark:hover:border-emerald-600',
        },
    };
    return accents[props.accent] || accents.emerald;
});

const classes = computed(() =>
    props.active
        ? `block w-full ps-3 pe-4 py-2 border-l-4 ${accentClasses.value.active} text-start text-base font-medium focus:outline-none transition duration-150 ease-in-out`
        : `block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 ${accentClasses.value.inactive} focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 transition duration-150 ease-in-out`,
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
