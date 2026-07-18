<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const pesan = ref('');
const tipe = ref('success');

const flash = computed(() => page.props.flash);
const munculkan = () => {
    show.value = true;
    setTimeout(() => (show.value = false), 4000); // hilang otomatis 4 detik
};
watch(flash, (val) => {
    if (val?.success) {
        pesan.value = val.success;
        tipe.value = 'success';
        munculkan();
    } else if (val?.error) {
        pesan.value = val.error;
        tipe.value = 'error';
        munculkan();
    }
}, { deep: true, immediate: true });


</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="show" class="fixed top-5 right-5 z-50 max-w-sm">
            <div :class="[
                'rounded-xl shadow-lg px-4 py-3 text-sm font-medium flex items-center gap-2',
                tipe === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'
            ]">
                <span>{{ tipe === 'success' ? '✅' : '⚠️' }}</span>
                <span>{{ pesan }}</span>
                <button @click="show = false" class="ml-2 opacity-70 hover:opacity-100">✕</button>
            </div>
        </div>
    </Transition>
</template>
