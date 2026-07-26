<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    kegiatan: Array,
    daftarEkstra: Array,
    tahunAktif: Object,
    filters: Object,
    ringkasan: Object,
});

const filterEkstra = ref(props.filters?.ekstra_id ?? '');
watch(filterEkstra, (val) => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('admin.laporan.index'), { ekstra_id: val }, {
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

const formatTanggal = (tgl) =>
    parseLocalDate(tgl).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

const persentaseHadir = (item) => {
    if (item.total_count === 0) return 0;
    return Math.round((item.hadir_count / item.total_count) * 100);
};

const cetak = () => window.print();

const fotoModal = ref(null);
const bukaFoto = (path) => { fotoModal.value = `/storage/${path}`; };
const tutupFoto = () => { fotoModal.value = null; };

const isFilterActive = computed(() => search.value.trim() !== '' || dateFrom.value !== '' || dateTo.value !== '');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.kegiatan.filter((item) => {
        const matchQ = !q
            || item.materi.toLowerCase().includes(q)
            || (item.ekstra?.nama ?? '').toLowerCase().includes(q)
            || (item.pelatih?.nama ?? '').toLowerCase().includes(q);
        const tgl = item.tanggal?.substring(0, 10);
        const matchFrom = !dateFrom.value || tgl >= dateFrom.value;
        const matchTo = !dateTo.value || tgl <= dateTo.value;
        return matchQ && matchFrom && matchTo;
    });
});

const ringkasanFiltered = computed(() => ({
    total_kegiatan: filtered.value.length,
    total_hadir: filtered.value.reduce((s, i) => s + (i.hadir_count ?? 0), 0),
    total_izin: filtered.value.reduce((s, i) => s + (i.izin_count ?? 0), 0),
    total_sakit: filtered.value.reduce((s, i) => s + (i.sakit_count ?? 0), 0),
    total_alpha: filtered.value.reduce((s, i) => s + (i.alpha_count ?? 0), 0),
}));

const resetFilter = () => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
};
</script>

<template>
    <Head title="Laporan Rekap Kegiatan" />

    <AuthenticatedLayout>
        <template #header>Laporan Rekap Kegiatan</template>

        <div class="space-y-6">
            <!-- Info Tahun Aktif -->
            <div class="rounded-2xl border border-[#3E6FD9]/20 bg-[#3E6FD9]/5 p-4 text-sm text-[#1B2333]">
                Tahun Pelajaran Aktif: <b class="text-[#0B1B36]">{{ tahunAktif?.nama ?? '-' }}</b>
            </div>

            <!-- Kartu Ringkasan (dinamis mengikuti filter) -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                <div class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-black/5">
                    <div class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">{{ isFilterActive ? ringkasanFiltered.total_kegiatan : ringkasan.total_kegiatan }}</div>
                    <div class="mt-0.5 text-xs text-[#5B6472]">Total Kegiatan</div>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-emerald-700">{{ isFilterActive ? ringkasanFiltered.total_hadir : ringkasan.total_hadir }}</div>
                    <div class="mt-0.5 text-xs text-emerald-600">Total Hadir</div>
                </div>
                <div class="rounded-2xl bg-yellow-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-yellow-700">{{ isFilterActive ? ringkasanFiltered.total_izin : ringkasan.total_izin }}</div>
                    <div class="mt-0.5 text-xs text-yellow-600">Total Izin</div>
                </div>
                <div class="rounded-2xl bg-orange-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-orange-700">{{ isFilterActive ? ringkasanFiltered.total_sakit : ringkasan.total_sakit }}</div>
                    <div class="mt-0.5 text-xs text-orange-600">Total Sakit</div>
                </div>
                <div class="rounded-2xl bg-red-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-red-700">{{ isFilterActive ? ringkasanFiltered.total_alpha : ringkasan.total_alpha }}</div>
                    <div class="mt-0.5 text-xs text-red-600">Total Alpha</div>
                </div>
            </div>

            <!-- Filter & Tabel -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <!-- Filter Bar -->
                <div class="border-b border-gray-100 p-4 space-y-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <!-- Filter Ekstra -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Filter Ekstra</label>
                            <select v-model="filterEkstra"
                                class="rounded-xl border-gray-200 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                                <option value="">Semua Ekstra</option>
                                <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Cari kegiatan</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input v-model="search" type="text" placeholder="Materi, ekstra, atau pelatih..."
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

                        <!-- Cetak -->
                        <button @click="cetak"
                            class="ml-auto rounded-full bg-[#F4F7FC] px-4 py-2.5 text-sm font-medium text-[#1B2333] transition hover:bg-gray-200 print:hidden shrink-0">
                            🖨️ Cetak
                        </button>
                    </div>

                    <!-- Result count -->
                    <p class="text-xs text-[#5B6472]">
                        Menampilkan <span class="font-semibold text-[#0B1B36]">{{ filtered.length }}</span>
                        dari <span class="font-semibold text-[#0B1B36]">{{ kegiatan.length }}</span> kegiatan
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#F4F7FC]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstra</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Foto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Materi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Pelatih</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-emerald-600">H</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-yellow-600">I</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-orange-600">S</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-red-600">A</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#5B6472]">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in filtered" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                                <td class="px-4 py-3 text-sm text-[#5B6472]">{{ formatTanggal(item.tanggal) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="rounded-full bg-[#3E6FD9]/10 px-2.5 py-0.5 text-xs font-medium text-[#3E6FD9]">{{ item.ekstra?.nama }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <img v-if="item.foto" :src="`/storage/${item.foto}`" alt="Foto"
                                        class="inline-block h-10 w-10 cursor-pointer rounded-lg object-cover transition hover:opacity-80"
                                        @click="bukaFoto(item.foto)" />
                                    <span v-else class="text-xs text-gray-300">-</span>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-[#0B1B36]">{{ item.materi }}</td>
                                <td class="px-4 py-3 text-sm text-[#5B6472]">{{ item.pelatih?.nama }}</td>
                                <td class="px-4 py-3 text-center text-sm font-semibold text-emerald-600">{{ item.hadir_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-yellow-600">{{ item.izin_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-orange-600">{{ item.sakit_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-red-600">{{ item.alpha_count }}</td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span :class="[
                                        'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                        persentaseHadir(item) >= 75 ? 'bg-emerald-100 text-emerald-700' :
                                        persentaseHadir(item) >= 50 ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-red-100 text-red-700'
                                    ]">
                                        {{ persentaseHadir(item) }}%
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="filtered.length === 0">
                                <td colspan="10" class="px-4 py-10 text-center text-sm text-[#5B6472]">
                                    {{ isFilterActive ? 'Tidak ada data yang cocok dengan filter.' : 'Belum ada data kegiatan untuk ditampilkan.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Preview Foto -->
        <div v-if="fotoModal" @click="tutupFoto"
            class="fixed inset-0 z-50 flex cursor-pointer items-center justify-center bg-black/70 p-4 print:hidden">
            <img :src="fotoModal" alt="Foto Kegiatan" class="max-h-[90vh] max-w-[90vw] rounded-2xl shadow-2xl" />
        </div>
    </AuthenticatedLayout>
</template>
