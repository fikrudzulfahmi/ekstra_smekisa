<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    data: Array,
    periode: String,
    tahunPelajaran: String,
    tanggalCetak: String,
    settings: Object,
});

onMounted(() => {
    // Otomatis memanggil dialog print sesaat setelah halaman dimuat
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head title="Cetak Laporan HR Pelatih" />

    <div class="print-container">
        <!-- Header Laporan -->
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold">Laporan HR Pelatih Ekstra</h1>
            <h2 class="text-lg font-semibold">Tahun Pelajaran {{ tahunPelajaran }}</h2>
        </div>

        <div class="mb-4">
            <span class="font-semibold">Periode : </span> {{ periode }}
        </div>

        <!-- Tabel Laporan -->
        <table class="w-full border-collapse border border-black mb-12">
            <thead>
                <tr>
                    <th class="border border-black px-2 py-2 text-center w-10">No.</th>
                    <th class="border border-black px-4 py-2 text-left">Nama</th>
                    <th class="border border-black px-4 py-2 text-left">Ekstrakurikuler</th>
                    <th class="border border-black px-2 py-2 text-center">Jumlah Kegiatan</th>
                    <th class="border border-black px-4 py-2 text-right">Nominal HR</th>
                    <th class="border border-black px-4 py-2 text-right">Jumlah</th>
                    <th class="border border-black px-4 py-2 text-center w-40">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in data" :key="index">
                    <td class="border border-black px-2 py-1.5 text-center text-sm">{{ index + 1 }}</td>
                    <td class="border border-black px-4 py-1.5 text-sm">{{ item.nama_pelatih }}</td>
                    <td class="border border-black px-4 py-1.5 text-sm">{{ item.nama_ekstra }}</td>
                    <td class="border border-black px-2 py-1.5 text-center text-sm">{{ item.jumlah_kegiatan }}</td>
                    <td class="border border-black px-4 py-1.5 text-right text-sm">{{ new Intl.NumberFormat('id-ID').format(item.nominal_hr) }}</td>
                    <td class="border border-black px-4 py-1.5 text-right text-sm">{{ new Intl.NumberFormat('id-ID').format(item.total) }}</td>
                    <td class="border border-black px-2 py-1.5 text-left align-top relative">
                        <span class="text-xs absolute top-1 left-2">{{ index + 1 }}.</span>
                    </td>
                </tr>
                <tr v-if="data.length === 0">
                    <td colspan="7" class="border border-black px-4 py-4 text-center italic text-sm">Tidak ada data pelatih</td>
                </tr>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="flex justify-end pr-10">
            <div class="text-center w-64">
                <p class="mb-1">Blitar, {{ tanggalCetak }}</p>
                <p class="mb-20">{{ settings.hr_signer_title }}</p>
                
                <p class="font-bold underline">{{ settings.hr_signer_name }}</p>
            </div>
        </div>
    </div>
</template>

<style>
/* Reset basic styles for printing */
body {
    background: white;
    font-family: 'Times New Roman', Times, serif; /* Sesuai format dokumen resmi */
    color: black;
}
.print-container {
    width: 100%;
    max-width: 21cm; /* A4 width */
    margin: 0 auto;
    padding: 2cm 1cm;
}
/* Menyembunyikan elemen UI inertia yang tidak perlu jika ada */
@media print {
    @page {
        size: A4 portrait;
        margin: 1cm;
    }
    body {
        margin: 0;
        padding: 0;
    }
}
</style>
