<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-[#1B2333] shadow-sm transition duration-150 ease-in-out hover:bg-[#F4F7FC] focus:outline-none focus:ring-2 focus:ring-[#3E6FD9]/40 focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
