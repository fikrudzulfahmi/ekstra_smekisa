<script setup>
import { ref, watch } from 'vue';
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
    router.get(route('admin.laporan.nilai'), { ekstra_id: val }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const formatTanggal = (t) =>
    new Date(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const warnaRata = (rata) => {
    if (rata >= 80) return 'bg-emerald-100 text-emerald-700';
    if (rata >= 60) return 'bg-yellow-100 text-yellow-700';
    return 'bg-red-100 text-red-700';
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

        <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 flex flex-wrap items-center gap-3">
                <label class="text-sm font-medium text-[#1B2333]">Ekstrakurikuler:</label>
                <select v-model="filterEkstra"
                    class="rounded-xl border-gray-200 py-2 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                    <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                </select>
            </div>

            <div v-for="item in penilaian" :key="item.id"
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

            <div v-if="penilaian.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center">
                <p class="mt-4 text-sm text-[#5B6472]">Belum ada penilaian untuk ekstrakurikuler ini.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
