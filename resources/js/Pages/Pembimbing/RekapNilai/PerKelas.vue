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
});

const terapkan = () => {
    if (!filter.kelas_id) {
        alert('Pilih kelas terlebih dahulu.');
        return;
    }
    router.get(route('pembimbing.rekap-nilai.per-kelas'), { ...filter }, {
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
    window.open(
        route('pembimbing.rekap-nilai.per-kelas.export', { ...filter }),
        '_blank'
    );
};
</script>

<template>
    <Head title="Rekap Nilai per Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>Rekap Nilai per Kelas</span>
                <Link :href="route('pembimbing.rekap-nilai.index')" class="text-sm font-medium text-[#3E6FD9] hover:underline print:hidden">
                    Rekap per Kegiatan &rarr;
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 print:hidden">
                <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Kelas</label>
                        <select v-model="filter.kelas_id"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">-- Pilih Kelas --</option>
                            <option v-for="k in daftarKelas" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                    </div>
                    <div>
                        <button @click="terapkan"
                            class="w-full rounded-full bg-[#0B1B36] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                            Tampilkan
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="namaKelas" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Rekap Nilai — Kelas {{ namaKelas }}</h3>
                        <p class="mt-0.5 text-sm text-[#5B6472]">
                            Tahun Pelajaran {{ tahunAktif?.nama }}
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

            <div v-if="namaKelas" class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#F4F7FC]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstrakurikuler</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-emerald-600">Rata-rata Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(s, i) in laporan" :key="s.id" class="transition hover:bg-[#F4F7FC]/60">
                                <td class="px-4 py-3 text-sm text-[#5B6472]">{{ i + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-[#0B1B36]">{{ s.nama }}</td>
                                <td class="px-4 py-3 text-sm text-[#5B6472]">{{ s.ekstra?.nama || '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-emerald-600">
                                    {{ s.rata_rata_nilai !== null ? Number(s.rata_rata_nilai).toFixed(1) : '-' }}
                                </td>
                            </tr>
                            <tr v-if="laporan.length === 0">
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-[#5B6472]">
                                    Tidak ada siswa di kelas ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center print:hidden">
                <p class="mt-4 text-sm text-[#5B6472]">Pilih kelas, lalu klik "Tampilkan".</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
