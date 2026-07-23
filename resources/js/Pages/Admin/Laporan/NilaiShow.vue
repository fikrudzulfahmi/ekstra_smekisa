<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    penilaian: Object,
    nilaiGrup: Object,
});

const formatTanggal = (t) =>
    new Date(t).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const rataRata = computed(() => {
    let total = 0;
    let count = 0;
    Object.values(props.nilaiGrup).flat().forEach((d) => {
        if (d.nilai !== null && d.nilai !== '') {
            total += Number(d.nilai);
            count++;
        }
    });
    if (count === 0) return 0;
    return (total / count).toFixed(1);
});
</script>

<template>
    <Head title="Detail Penilaian" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>Detail Penilaian — {{ penilaian.ekstra.nama }}</span>
                <Link :href="route('admin.laporan.nilai')" class="text-sm font-medium text-[#5B6472] hover:underline">
                    &larr; Kembali
                </Link>
            </div>
        </template>

        <div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="font-['Poppins'] text-xl font-semibold text-[#0B1B36]">{{ penilaian.judul }}</h2>
                        <p class="text-sm text-[#5B6472]">{{ formatTanggal(penilaian.tanggal) }}</p>
                        <p class="mt-2 text-sm text-[#1B2333]">{{ penilaian.deskripsi }}</p>
                        <p class="mt-4 text-sm text-[#5B6472]">Oleh Pelatih: {{ penilaian.pelatih.nama }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-[#5B6472]">Rata-rata</p>
                        <p class="font-['Poppins'] text-3xl font-bold text-[#0B1B36]">{{ rataRata }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Daftar Nilai</h3>
                <div v-for="(list, namaKelas) in nilaiGrup" :key="namaKelas">
                    <h4 class="mb-2 rounded-lg bg-[#F4F7FC] px-3 py-2 font-semibold text-[#1B2333]">
                        Kelas {{ namaKelas }} ({{ list.length }} siswa)
                    </h4>
                    <div class="space-y-2">
                        <div v-for="(d, index) in list" :key="index"
                            class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2">
                            <span class="text-sm font-medium text-[#1B2333]">{{ d.nama }}</span>
                            <span class="w-24 rounded-lg bg-gray-50 px-3 py-1.5 text-center text-sm font-semibold text-[#0B1B36] ring-1 ring-inset ring-gray-200">
                                {{ d.nilai }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
