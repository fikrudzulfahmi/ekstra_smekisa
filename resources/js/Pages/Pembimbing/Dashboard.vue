<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    jumlahEkstra: Number,
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);
</script>

<template>
    <Head title="Dashboard Pembimbing" />
    <AuthenticatedLayout>
        <template #header>Dashboard Pembimbing</template>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-black/5">
                <h1 class="font-['Poppins'] text-xl font-semibold text-[#0B1B36]">
                    Selamat datang{{ userName ? `, ${userName}` : '' }} 👋
                </h1>
                <p class="mt-2 text-sm text-[#5B6472]">
                    Kelola anggota, pantau presensi, dan rekap nilai untuk {{ jumlahEkstra }} ekstrakurikuler yang Anda bimbing.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <Link :href="route('pembimbing.anggota.index')"
                        class="rounded-full bg-[#0B1B36] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                        Kelola Anggota
                    </Link>
                    <Link :href="route('pembimbing.rekap-presensi.index')"
                        class="rounded-full border border-gray-200 px-6 py-2.5 text-sm font-semibold text-[#1B2333] transition hover:bg-[#F4F7FC]">
                        Rekap Presensi
                    </Link>
                    <Link :href="route('pembimbing.rekap-nilai.index')"
                        class="rounded-full border border-gray-200 px-6 py-2.5 text-sm font-semibold text-[#1B2333] transition hover:bg-[#F4F7FC]">
                        Rekap Nilai
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
