<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <textarea
        class="border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:border-cyan-500 focus:ring-cyan-500 dark:focus:border-cyan-500 dark:focus:ring-cyan-500 hover:border-cyan-400 dark:hover:border-cyan-400 hover:ring hover:ring-cyan-400/30 rounded-lg shadow-sm transition-all duration-300"
        v-model="model"
        ref="input"
    ></textarea>
</template>
