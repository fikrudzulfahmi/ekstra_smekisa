<script setup>
import { reactive } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    laporan: Array,
    daftarKelas: Array,
    tahunAktif: Object,
    namaKelas: String,
    filters: Object,
});

const filter = reactive({
    kelas_id: props.filters?.kelas_id ?? '',
    tgl_mulai: props.filters?.tgl_mulai ?? '',
    tgl_selesai: props.filters?.tgl_selesai ?? '',
});

const terapkan = () => {
    if (!filter.kelas_id) {
        alert('Pilih kelas dulu.');
        return;
    }
    router.get(route('admin.laporan.per-kelas'), { ...filter }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const cetak = () => window.print();

const exportExcel = () => {
    if (!filter.kelas_id) {
        alert('Pilih kelas dulu.');
        return;
    }
    // Buka di tab baru supaya halaman laporan tidak ikut ter-reload
    window.open(
        route('admin.laporan.per-kelas.export', { ...filter }),
        '_blank'
    );
};

const persentase = (item) => {
    if (item.total_count === 0) return 0;
    return Math.round((item.hadir_count / item.total_count) * 100);
};

const formatTgl = (t) =>
    new Date(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Laporan Kehadiran per Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>Laporan Kehadiran per Kelas</span>
                <Link :href="route('admin.laporan.index')" class="text-sm font-medium text-[#3E6FD9] hover:underline print:hidden">
                    Laporan per Kegiatan &rarr;
                </Link>
            </div>
        </template>

        <div>
            <!-- Panel Filter -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 print:hidden">
                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Kelas</label>
                        <select v-model="filter.kelas_id"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">-- Pilih Kelas --</option>
                            <option v-for="k in daftarKelas" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Dari Tanggal</label>
                        <input v-model="filter.tgl_mulai" type="date"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Sampai Tanggal</label>
                        <input v-model="filter.tgl_selesai" type="date"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <div>
                        <button @click="terapkan"
                            class="w-full rounded-full bg-[#0B1B36] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                            Tampilkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Header Laporan -->
            <div v-if="namaKelas" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Rekap Kehadiran — Kelas {{ namaKelas }}</h3>
                        <p class="mt-0.5 text-sm text-[#5B6472]">
                            Periode: {{ formatTgl(filters.tgl_mulai) }} s/d {{ formatTgl(filters.tgl_selesai) }}
                            &bull; Tahun Pelajaran {{ tahunAktif?.nama }}
                        </p>
                    </div>
                    <div class="flex gap-2 print:hidden">
                        <button @click="exportExcel"
                            class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                            📊 Export Excel
                        </button>
                        <button @click="cetak"
                            class="rounded-full bg-[#F4F7FC] px-4 py-2 text-sm font-medium text-[#1B2333] transition hover:bg-gray-200">
                            🖨️ Cetak
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabel Rekap per Siswa -->
            <div v-if="namaKelas" class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#F4F7FC]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstra</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-emerald-600">Hadir</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-yellow-600">Izin</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-orange-600">Sakit</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-red-600">Alpha</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#5B6472]">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(s, i) in laporan" :key="s.id" class="transition hover:bg-[#F4F7FC]/60">
                                <td class="px-4 py-3 text-sm text-[#5B6472]">{{ i + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-[#0B1B36]">{{ s.nama }}</td>
                                <td class="px-4 py-3 text-sm text-[#5B6472]">{{ s.ekstra?.nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm font-semibold text-emerald-600">{{ s.hadir_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-yellow-600">{{ s.izin_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-orange-600">{{ s.sakit_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-red-600">{{ s.alpha_count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-[#1B2333]">{{ s.total_count }}</td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span :class="[
                                        'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                        persentase(s) >= 75 ? 'bg-emerald-100 text-emerald-700' :
                                        persentase(s) >= 50 ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-red-100 text-red-700'
                                    ]">{{ persentase(s) }}%</span>
                                </td>
                            </tr>
                            <tr v-if="laporan.length === 0">
                                <td colspan="9" class="px-4 py-10 text-center text-sm text-[#5B6472]">
                                    Tidak ada siswa di kelas ini pada periode tersebut.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Placeholder saat belum pilih kelas -->
            <div v-else class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center print:hidden">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F4F7FC]">
                    <svg class="h-6 w-6 text-[#6C82AC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 7h6m-6 4h6" />
                    </svg>
                </div>
                <p class="mt-4 text-sm text-[#5B6472]">Pilih kelas dan rentang tanggal, lalu klik "Tampilkan".</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>