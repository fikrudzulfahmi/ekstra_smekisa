<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);
</script>

<template>
    <Head title="Dashboard Pelatih" />
    <AuthenticatedLayout>
        <template #header>Dashboard Pelatih</template>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-black/5">
                <h1 class="font-['Poppins'] text-xl font-semibold text-[#0B1B36]">
                    Selamat datang{{ userName ? `, ${userName}` : '' }} 👋
                </h1>
                <p class="mt-2 text-sm text-[#5B6472]">
                    Buat kegiatan baru atau lihat history kegiatan ekstrakurikuler Anda.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <Link :href="route('pelatih.kegiatan.create')"
                        class="rounded-full bg-[#0B1B36] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                        + Buat Kegiatan
                    </Link>
                    <Link :href="route('pelatih.kegiatan.index')"
                        class="rounded-full border border-gray-200 px-6 py-2.5 text-sm font-semibold text-[#1B2333] transition hover:bg-[#F4F7FC]">
                        History Kegiatan
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
