<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();
const flash = computed(() => page.props.flash);

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    customClass: {
        popup: 'rounded-xl shadow-xl border border-gray-100',
        title: 'text-sm font-semibold font-["Poppins"]'
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

watch(flash, (val) => {
    if (val?.success) {
        Toast.fire({
            icon: 'success',
            title: val.success
        });
    } else if (val?.error) {
        Toast.fire({
            icon: 'error',
            title: val.error
        });
    }
}, { deep: true, immediate: true });
</script>

<template>
    <!-- Template kosong karena rendering UI ditangani sepenuhnya oleh SweetAlert2 -->
    <div></div>
</template>
