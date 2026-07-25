<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ kegiatan: Array });

const search = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const showAll = ref(false);

const LIMIT = 5;

// Parsing tanggal secara lokal agar tidak terjadi offset UTC
const parseLocalDate = (t) => {
    if (!t) return new Date();
    const s = t.substring(0, 10); // ambil "YYYY-MM-DD"
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d); // konstruktor lokal, bukan UTC
};

const formatTanggal = (t) =>
    parseLocalDate(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const hapus = (item) => {
    if (confirm(`Hapus kegiatan "${item.materi}" tanggal ${formatTanggal(item.tanggal)}?`)) {
        router.delete(route('pelatih.kegiatan.destroy', item.id), { preserveScroll: true });
    }
};

const isFilterActive = computed(() => search.value.trim() !== '' || dateFrom.value !== '' || dateTo.value !== '');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.kegiatan.filter((item) => {
        const matchQ = !q || item.materi.toLowerCase().includes(q) || (item.deskripsi ?? '').toLowerCase().includes(q) || item.ekstra.nama.toLowerCase().includes(q);
        const tgl = item.tanggal?.substring(0, 10);
        const matchFrom = !dateFrom.value || tgl >= dateFrom.value;
        const matchTo = !dateTo.value || tgl <= dateTo.value;
        return matchQ && matchFrom && matchTo;
    });
});

const displayed = computed(() => {
    if (isFilterActive.value || showAll.value) return filtered.value;
    return filtered.value.slice(0, LIMIT);
});

const resetFilter = () => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    showAll.value = false;
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

        <div class="space-y-4">
            <!-- Filter & Search Bar -->
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <!-- Search -->
                    <div class="flex-1">
                        <label class="mb-1 block text-xs font-medium text-[#5B6472]">Cari kegiatan</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input v-model="search" type="text" placeholder="Materi, deskripsi, atau ekstra..."
                                class="w-full rounded-xl border-gray-200 py-2.5 pl-9 pr-4 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        </div>
                    </div>
                    <!-- Date From -->
                    <div class="sm:w-36">
                        <label class="mb-1 block text-xs font-medium text-[#5B6472]">Dari tanggal</label>
                        <input v-model="dateFrom" type="date"
                            class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <!-- Date To -->
                    <div class="sm:w-36">
                        <label class="mb-1 block text-xs font-medium text-[#5B6472]">Sampai tanggal</label>
                        <input v-model="dateTo" type="date"
                            class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <!-- Reset -->
                    <button v-if="isFilterActive" @click="resetFilter" type="button"
                        class="flex shrink-0 items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-[#5B6472] transition hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </button>
                </div>
                <!-- Result count -->
                <p class="mt-2 text-xs text-[#5B6472]">
                    Menampilkan <span class="font-semibold text-[#0B1B36]">{{ displayed.length }}</span>
                    dari <span class="font-semibold text-[#0B1B36]">{{ filtered.length }}</span> kegiatan
                    <template v-if="!isFilterActive && !showAll && filtered.length > LIMIT">
                        ({{ filtered.length - LIMIT }} lainnya disembunyikan)
                    </template>
                </p>
            </div>

            <!-- List -->
            <div class="space-y-4">
                <div v-for="item in displayed" :key="item.id"
                    class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#3E6FD9]/10 px-2.5 py-0.5 text-xs font-medium text-[#3E6FD9]">{{ item.ekstra.nama }}</span>
                            <span class="text-sm text-[#5B6472] whitespace-nowrap">{{ formatTanggal(item.tanggal) }}</span>
                        </div>
                        <h3 class="mt-2 font-['Poppins'] font-semibold text-[#0B1B36]">{{ item.materi }}</h3>
                        <p v-if="item.deskripsi" class="mt-0.5 text-sm text-[#5B6472] line-clamp-2">{{ item.deskripsi }}</p>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="rounded-md bg-emerald-50 px-2 py-1 font-medium text-emerald-600">Hadir: {{ item.hadir_count }}</span>
                            <span class="rounded-md bg-yellow-50 px-2 py-1 font-medium text-yellow-600">Izin: {{ item.izin_count }}</span>
                            <span class="rounded-md bg-orange-50 px-2 py-1 font-medium text-orange-600">Sakit: {{ item.sakit_count }}</span>
                            <span class="rounded-md bg-red-50 px-2 py-1 font-medium text-red-600">Alpha: {{ item.alpha_count }}</span>
                            <span class="rounded-md bg-gray-50 px-2 py-1 font-medium text-[#5B6472]">Total: {{ item.total_count }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div v-if="item.foto" class="shrink-0">
                            <img :src="`/storage/${item.foto}`" alt="Foto Kegiatan"
                                class="h-20 w-20 rounded-xl border border-gray-100 object-cover" />
                        </div>

                        <div class="flex flex-1 justify-end sm:flex-col sm:flex-none shrink-0 gap-2">
                            <Link :href="route('pelatih.kegiatan.edit', item.id)"
                                class="rounded-lg bg-blue-50 px-3 py-1.5 text-center text-sm font-medium text-[#3E6FD9] transition hover:bg-blue-100">Edit</Link>
                            <button @click="hapus(item)" class="rounded-lg bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-100">Hapus</button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="displayed.length === 0"
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F4F7FC]">
                        <svg class="h-6 w-6 text-[#6C82AC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 7h6m-6 4h6" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm text-[#5B6472]">
                        {{ isFilterActive ? 'Tidak ada kegiatan yang cocok dengan filter.' : 'Belum ada kegiatan. Klik "Buat Kegiatan" untuk memulai.' }}
                    </p>
                </div>

                <!-- Lihat Lainnya / Sembunyikan -->
                <div v-if="!isFilterActive && filtered.length > LIMIT" class="flex justify-center">
                    <button @click="showAll = !showAll" type="button"
                        class="flex items-center gap-2 rounded-full border border-[#3E6FD9]/30 bg-white px-6 py-2.5 text-sm font-medium text-[#3E6FD9] shadow-sm transition hover:bg-[#3E6FD9]/5 hover:border-[#3E6FD9]">
                        <svg :class="['h-4 w-4 transition-transform', showAll ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        {{ showAll ? 'Sembunyikan' : `Lihat ${filtered.length - LIMIT} lainnya` }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
