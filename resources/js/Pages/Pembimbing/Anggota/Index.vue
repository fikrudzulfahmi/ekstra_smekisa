<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    siswa: Array,
    daftarKelas: Array,
    daftarEkstra: Array,
    tahunAktif: Object,
    filters: Object,
    pembimbingEkstraIds: Array,
});

const filterKelas = ref(props.filters?.kelas_id ?? '');
const filterStatus = ref(props.filters?.status ?? '');

watch([filterKelas, filterStatus], ([kelasVal, statusVal]) => {
    router.get(route('pembimbing.anggota.index'), { kelas_id: kelasVal, status: statusVal }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const isBisaDiedit = (item) => {
    const ids = props.pembimbingEkstraIds || [];
    return item.ekstra_id === null || ids.includes(item.ekstra_id);
};

const ubahEkstra = (item, event) => {
    router.patch(route('pembimbing.anggota.ekstra', item.id), {
        ekstra_id: event.target.value || null,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Anggota Ekstrakurikuler" />

    <AuthenticatedLayout>
        <template #header>Anggota Ekstrakurikuler</template>

        <div class="space-y-6">
            <div v-if="!tahunAktif" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                Belum ada tahun pelajaran aktif.
            </div>
            
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 p-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-[#1B2333]">Filter Kelas:</label>
                        <select v-model="filterKelas"
                            class="rounded-xl border-gray-200 py-2 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in daftarKelas" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <label class="text-sm font-medium text-[#1B2333]">Status:</label>
                        <select v-model="filterStatus"
                            class="rounded-xl border-gray-200 py-2 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">Semua Siswa</option>
                            <option value="anggota_saya">Hanya Anggota Saya</option>
                            <option value="belum_ikut">Belum Ikut Ekstra</option>
                            <option value="ekstra_lain">Ekstra Pembimbing Lain</option>
                        </select>
                    </div>
                    <span class="ml-auto text-sm text-[#5B6472]">Total: {{ siswa.length }} siswa</span>
                </div>

                <div class="overflow-x-auto">`n<table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-[#F4F7FC]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">NIS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">JK</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstra Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in siswa" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                            <td class="px-4 py-3 text-sm text-[#5B6472]">{{ item.nis || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#0B1B36]">{{ item.nama }}</td>
                            <td class="px-4 py-3 text-sm text-[#1B2333]">{{ item.jk || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-[#1B2333]">{{ item.kelas?.nama }}</td>
                            <td class="px-4 py-3 text-sm">
                                <select v-if="isBisaDiedit(item)" :value="item.ekstra_id ?? ''" @change="ubahEkstra(item, $event)"
                                    class="rounded-lg border-gray-200 py-1.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                                    <option value="">- Belum ikut -</option>
                                    <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                                </select>
                                <span v-else class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-[#5B6472]">
                                    {{ item.ekstra?.nama }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="siswa.length === 0">
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-[#5B6472]">
                                Belum ada siswa.
                            </td>
                        </tr>
                    </tbody>
                </table>`n</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
