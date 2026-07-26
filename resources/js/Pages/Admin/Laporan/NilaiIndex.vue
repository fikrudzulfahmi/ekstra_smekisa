<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ 
    penilaian: Array,
    daftarEkstra: Array,
    filters: Object,
});

const filterEkstra = ref(props.filters?.ekstra_id ?? '');

if (!props.filters?.ekstra_id && props.daftarEkstra.length > 0 && filterEkstra.value === '') {
    filterEkstra.value = props.daftarEkstra[0].id;
    router.get(route('admin.laporan.nilai'), { ekstra_id: filterEkstra.value }, {
        preserveState: true, replace: true,
    });
}

watch(filterEkstra, (val) => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('admin.laporan.nilai'), { ekstra_id: val }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

// Client-side search & date range
const search = ref('');
const dateFrom = ref('');
const dateTo = ref('');

const parseLocalDate = (t) => {
    if (!t) return new Date();
    const s = t.substring(0, 10);
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d);
};

const formatTanggal = (t) =>
    parseLocalDate(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const warnaRata = (rata) => {
    if (rata >= 80) return 'bg-emerald-100 text-emerald-700';
    if (rata >= 60) return 'bg-yellow-100 text-yellow-700';
    return 'bg-red-100 text-red-700';
};

const isFilterActive = computed(() => search.value.trim() !== '' || dateFrom.value !== '' || dateTo.value !== '');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.penilaian.filter((item) => {
        const matchQ = !q
            || item.judul.toLowerCase().includes(q)
            || (item.deskripsi ?? '').toLowerCase().includes(q)
            || (item.ekstra?.nama ?? '').toLowerCase().includes(q)
            || (item.pelatih?.nama ?? '').toLowerCase().includes(q);
        const tgl = item.tanggal?.substring(0, 10);
        const matchFrom = !dateFrom.value || tgl >= dateFrom.value;
        const matchTo = !dateTo.value || tgl <= dateTo.value;
        return matchQ && matchFrom && matchTo;
    });
});

const resetFilter = () => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
};
</script>

<template>
    <Head title="Rekap Penilaian" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>Rekap Penilaian</span>
                <Link :href="route('admin.laporan.nilai.per-kelas')" class="text-sm font-medium text-[#3E6FD9] hover:underline print:hidden">
                    Rekap per Kelas &rarr;
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Bar -->
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 space-y-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <!-- Filter Ekstra -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#5B6472]">Ekstrakurikuler</label>
                        <select v-model="filterEkstra"
                            class="rounded-xl border-gray-200 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="flex-1">
                        <label class="mb-1 block text-xs font-medium text-[#5B6472]">Cari penilaian</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input v-model="search" type="text" placeholder="Judul, deskripsi, ekstra, atau pelatih..."
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
                <p class="text-xs text-[#5B6472]">
                    Menampilkan <span class="font-semibold text-[#0B1B36]">{{ filtered.length }}</span>
                    dari <span class="font-semibold text-[#0B1B36]">{{ penilaian.length }}</span> penilaian
                </p>
            </div>

            <!-- List -->
            <div v-for="item in filtered" :key="item.id"
                class="flex items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-[#3E6FD9]/10 px-2.5 py-0.5 text-xs font-medium text-[#3E6FD9]">{{ item.ekstra.nama }}</span>
                        <span class="text-sm text-[#5B6472]">{{ formatTanggal(item.tanggal) }}</span>
                    </div>
                    <h3 class="mt-1 font-['Poppins'] font-semibold text-[#0B1B36]">{{ item.judul }}</h3>
                    <p v-if="item.deskripsi" class="mt-0.5 text-sm text-[#5B6472]">{{ item.deskripsi }}</p>
                    <p class="mt-2 text-xs text-[#5B6472]">Pelatih: {{ item.pelatih.nama }} &bull; {{ item.jumlah_siswa }} siswa dinilai</p>
                </div>

                <div class="shrink-0 text-center">
                    <span :class="['inline-flex rounded-full px-3 py-1.5 text-sm font-semibold', warnaRata(item.rata_rata)]">
                        {{ item.rata_rata !== null ? Number(item.rata_rata).toFixed(1) : '-' }}
                    </span>
                    <p class="mt-1 text-xs text-[#5B6472]">rata-rata</p>
                </div>

                <div class="flex shrink-0 gap-2">
                    <Link :href="route('admin.laporan.nilai.show', item.id)"
                        class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Lihat Detail</Link>
                </div>
            </div>

            <div v-if="filtered.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center">
                <p class="mt-4 text-sm text-[#5B6472]">
                    {{ isFilterActive ? 'Tidak ada penilaian yang cocok dengan filter.' : 'Belum ada penilaian untuk ekstrakurikuler ini.' }}
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
