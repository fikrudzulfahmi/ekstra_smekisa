<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    data: Array,
    filters: Object,
});

const filterStart = ref(props.filters.start_date);
const filterEnd = ref(props.filters.end_date);

watch([filterStart, filterEnd], ([startVal, endVal]) => {
    router.get(route('admin.laporan-hr.index'), { start_date: startVal, end_date: endVal }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const cetakLaporan = () => {
    const url = route('admin.laporan-hr.cetak', {
        start_date: filterStart.value,
        end_date: filterEnd.value,
    });
    window.open(url, '_blank');
};
</script>

<template>
    <Head title="Laporan HR Pelatih" />

    <AuthenticatedLayout>
        <template #header>Laporan HR Pelatih Ekstra</template>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-[#1B2333]">Tanggal Mulai</label>
                            <input v-model="filterStart" type="date"
                                class="w-full rounded-xl border-gray-200 py-2 px-3 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-[#1B2333]">Tanggal Akhir</label>
                            <input v-model="filterEnd" type="date"
                                class="w-full rounded-xl border-gray-200 py-2 px-3 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        </div>
                    </div>
                    
                    <button @click="cetakLaporan" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0B1B36] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1B2333]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#F4F7FC]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">No.</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama Pelatih</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstrakurikuler</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Jml Kegiatan</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nominal HR</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, index) in data" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                                <td class="px-6 py-4 text-sm text-[#5B6472]">{{ index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-[#0B1B36]">{{ item.nama_pelatih }}</td>
                                <td class="px-6 py-4 text-sm text-[#5B6472]">{{ item.nama_ekstra }}</td>
                                <td class="px-6 py-4 text-center text-sm font-medium text-[#0B1B36]">{{ item.jumlah_kegiatan }}</td>
                                <td class="px-6 py-4 text-right text-sm text-[#5B6472]">Rp {{ new Intl.NumberFormat('id-ID').format(item.nominal_hr) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-[#0B1B36]">Rp {{ new Intl.NumberFormat('id-ID').format(item.total) }}</td>
                            </tr>
                            <tr v-if="data.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-[#5B6472]">Tidak ada kegiatan di rentang tanggal ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
