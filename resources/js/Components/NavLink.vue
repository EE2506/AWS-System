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
            active: 'border-red-500 dark:border-red-400 text-red-100 dark:text-red-100 focus:border-red-700',
            inactive: 'hover:border-red-300 dark:hover:border-red-500',
        },
        emerald: {
            active: 'border-emerald-400 dark:border-emerald-500 text-emerald-100 dark:text-emerald-100 focus:border-emerald-700',
            inactive: 'hover:border-emerald-300 dark:hover:border-emerald-500',
        },
    };
    return accents[props.accent] || accents.emerald;
});

const classes = computed(() =>
    props.active
        ? `inline-flex items-center px-1 pt-1 border-b-2 ${accentClasses.value.active} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out`
        : `inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-300 dark:text-gray-300 hover:text-white dark:hover:text-white ${accentClasses.value.inactive} focus:outline-none focus:text-white dark:focus:text-white transition duration-150 ease-in-out`,
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
