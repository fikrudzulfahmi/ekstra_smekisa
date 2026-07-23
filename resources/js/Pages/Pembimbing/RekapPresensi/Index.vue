<script setup>
import { ref, watch } from 'vue';
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

// If filterEkstra is not set in URL but we have a default, set it
if (!props.filters?.ekstra_id && props.daftarEkstra.length > 0 && filterEkstra.value === '') {
    filterEkstra.value = props.daftarEkstra[0].id;
    router.get(route('pembimbing.rekap-presensi.index'), { ekstra_id: filterEkstra.value }, {
        preserveState: true, replace: true,
    });
}

watch(filterEkstra, (val) => {
    router.get(route('pembimbing.rekap-presensi.index'), { ekstra_id: val }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const formatTanggal = (tgl) =>
    new Date(tgl).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

const persentaseHadir = (item) => {
    if (item.total_count === 0) return 0;
    return Math.round((item.hadir_count / item.total_count) * 100);
};

const cetak = () => window.print();

const fotoModal = ref(null);
const bukaFoto = (path) => { fotoModal.value = `/storage/${path}`; };
const tutupFoto = () => { fotoModal.value = null; };
</script>

<template>
    <Head title="Laporan Rekap Kegiatan" />

    <AuthenticatedLayout>
        <template #header>Laporan Rekap Kegiatan</template>

        <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-[#3E6FD9]/20 bg-[#3E6FD9]/5 p-4 text-sm text-[#1B2333]">
                Tahun Pelajaran Aktif: <b class="text-[#0B1B36]">{{ tahunAktif?.nama ?? '-' }}</b>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                <div class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-black/5">
                    <div class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">{{ ringkasan.total_kegiatan }}</div>
                    <div class="mt-0.5 text-xs text-[#5B6472]">Total Kegiatan</div>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-emerald-700">{{ ringkasan.total_hadir }}</div>
                    <div class="mt-0.5 text-xs text-emerald-600">Total Hadir</div>
                </div>
                <div class="rounded-2xl bg-yellow-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-yellow-700">{{ ringkasan.total_izin }}</div>
                    <div class="mt-0.5 text-xs text-yellow-600">Total Izin</div>
                </div>
                <div class="rounded-2xl bg-orange-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-orange-700">{{ ringkasan.total_sakit }}</div>
                    <div class="mt-0.5 text-xs text-orange-600">Total Sakit</div>
                </div>
                <div class="rounded-2xl bg-red-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-red-700">{{ ringkasan.total_alpha }}</div>
                    <div class="mt-0.5 text-xs text-red-600">Total Alpha</div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 p-4">
                    <label class="text-sm font-medium text-[#1B2333]">Ekstrakurikuler:</label>
                    <select v-model="filterEkstra"
                        class="rounded-xl border-gray-200 py-2 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                        <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                    </select>
                    <button @click="cetak"
                        class="ml-auto rounded-full bg-[#F4F7FC] px-4 py-2 text-sm font-medium text-[#1B2333] transition hover:bg-gray-200 print:hidden">
                        🖨️ Cetak
                    </button>
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
                            <tr v-for="item in kegiatan" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
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
                            <tr v-if="kegiatan.length === 0">
                                <td colspan="10" class="px-4 py-10 text-center text-sm text-[#5B6472]">
                                    Belum ada data kegiatan untuk ditampilkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="fotoModal" @click="tutupFoto"
            class="fixed inset-0 z-50 flex cursor-pointer items-center justify-center bg-black/70 p-4 print:hidden">
            <img :src="fotoModal" alt="Foto Kegiatan" class="max-h-[90vh] max-w-[90vw] rounded-2xl shadow-2xl" />
        </div>
    </AuthenticatedLayout>
</template>
