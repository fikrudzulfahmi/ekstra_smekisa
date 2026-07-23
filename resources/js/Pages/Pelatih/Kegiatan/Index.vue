<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ kegiatan: Array });

const formatTanggal = (t) =>
    new Date(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const hapus = (item) => {
    if (confirm(`Hapus kegiatan "${item.materi}" tanggal ${formatTanggal(item.tanggal)}?`)) {
        router.delete(route('pelatih.kegiatan.destroy', item.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="History Kegiatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>History Kegiatan</span>
                <Link :href="route('pelatih.kegiatan.create')"
                    class="rounded-full bg-[#0B1B36] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                    + Buat Kegiatan
                </Link>
            </div>
        </template>

        <div>
            <div v-for="item in kegiatan" :key="item.id"
                class="flex items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#3E6FD9]/10 px-2.5 py-0.5 text-xs font-medium text-[#3E6FD9]">{{ item.ekstra.nama }}</span>
                        <span class="text-sm text-[#5B6472]">{{ formatTanggal(item.tanggal) }}</span>
                    </div>
                    <h3 class="mt-1 font-['Poppins'] font-semibold text-[#0B1B36]">{{ item.materi }}</h3>
                    <p v-if="item.deskripsi" class="mt-0.5 text-sm text-[#5B6472]">{{ item.deskripsi }}</p>

                    <div class="mt-2 flex flex-wrap gap-3 text-xs">
                        <span class="text-emerald-600">Hadir: {{ item.hadir_count }}</span>
                        <span class="text-yellow-600">Izin: {{ item.izin_count }}</span>
                        <span class="text-orange-600">Sakit: {{ item.sakit_count }}</span>
                        <span class="text-red-600">Alpha: {{ item.alpha_count }}</span>
                        <span class="text-[#5B6472]">Total: {{ item.total_count }}</span>
                    </div>
                </div>

                <div v-if="item.foto" class="shrink-0">
                    <img :src="`/storage/${item.foto}`" alt="Foto Kegiatan"
                        class="h-20 w-20 rounded-xl border border-gray-100 object-cover" />
                </div>

                <div class="flex shrink-0 gap-2">
                    <Link :href="route('pelatih.kegiatan.edit', item.id)"
                        class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Edit</Link>
                    <button @click="hapus(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                </div>
            </div>

            <div v-if="kegiatan.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F4F7FC]">
                    <svg class="h-6 w-6 text-[#6C82AC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 7h6m-6 4h6" />
                    </svg>
                </div>
                <p class="mt-4 text-sm text-[#5B6472]">Belum ada kegiatan. Klik "Buat Kegiatan" untuk memulai.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
