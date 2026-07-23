<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>Dashboard</template>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-black/5">
                <p class="text-[#1B2333]">Anda berhasil masuk.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
