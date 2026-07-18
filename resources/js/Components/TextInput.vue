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
    <input
        class="w-full rounded-xl border-gray-200 bg-white px-4 py-2.5 text-sm text-[#1B2333] shadow-sm transition placeholder:text-gray-400 focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"
        v-model="model"
        ref="input"
    />
</template>
