<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ penilaian: Array });

const formatTanggal = (t) =>
    new Date(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const warnaRata = (rata) => {
    if (rata >= 80) return 'bg-emerald-100 text-emerald-700';
    if (rata >= 60) return 'bg-yellow-100 text-yellow-700';
    return 'bg-red-100 text-red-700';
};

const hapus = (item) => {
    if (confirm(`Hapus penilaian "${item.judul}" tanggal ${formatTanggal(item.tanggal)}?`)) {
        router.delete(route('pelatih.penilaian.destroy', item.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="History Penilaian" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>History Penilaian</span>
                <Link :href="route('pelatih.penilaian.create')"
                    class="rounded-full bg-[#0B1B36] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                    + Buat Penilaian
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
            <div v-for="item in penilaian" :key="item.id"
                class="flex items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#3E6FD9]/10 px-2.5 py-0.5 text-xs font-medium text-[#3E6FD9]">{{ item.ekstra.nama }}</span>
                        <span class="text-sm text-[#5B6472]">{{ formatTanggal(item.tanggal) }}</span>
                    </div>
                    <h3 class="mt-1 font-['Poppins'] font-semibold text-[#0B1B36]">{{ item.judul }}</h3>
                    <p v-if="item.deskripsi" class="mt-0.5 text-sm text-[#5B6472]">{{ item.deskripsi }}</p>
                    <p class="mt-2 text-xs text-[#5B6472]">{{ item.jumlah_siswa }} siswa dinilai</p>
                </div>

                <div class="shrink-0 text-center">
                    <span :class="['inline-flex rounded-full px-3 py-1.5 text-sm font-semibold', warnaRata(item.rata_rata)]">
                        {{ item.rata_rata !== null ? Number(item.rata_rata).toFixed(1) : '-' }}
                    </span>
                    <p class="mt-1 text-xs text-[#5B6472]">rata-rata</p>
                </div>

                <div class="flex shrink-0 gap-2">
                    <Link :href="route('pelatih.penilaian.edit', item.id)"
                        class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Edit</Link>
                    <button @click="hapus(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                </div>
            </div>

            <div v-if="penilaian.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F4F7FC]">
                    <svg class="h-6 w-6 text-[#6C82AC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="mt-4 text-sm text-[#5B6472]">Belum ada penilaian. Klik "Buat Penilaian" untuk memulai.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
