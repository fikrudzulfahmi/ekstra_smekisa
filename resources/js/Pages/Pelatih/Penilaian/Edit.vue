<script setup>
import { reactive, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    penilaian: Object,
    nilaiGrup: Object,
});

const form = useForm({
    tanggal: props.penilaian.tanggal?.substring(0, 10),
    judul: props.penilaian.judul,
    deskripsi: props.penilaian.deskripsi ?? '',
    nilai: [],
});

// key = id baris detail_penilaian (bukan siswa_id), sesuai kontrak update() di controller
const nilaiMap = reactive({});
Object.values(props.nilaiGrup).flat().forEach((d) => {
    nilaiMap[d.id] = d.nilai;
});

const rataRata = computed(() => {
    const nilai = Object.values(nilaiMap).filter((n) => n !== '' && n !== null).map(Number);
    if (nilai.length === 0) return 0;
    return (nilai.reduce((a, b) => a + b, 0) / nilai.length).toFixed(1);
});

const submit = () => {
    form.nilai = Object.entries(nilaiMap).map(([id, nilai]) => ({
        id: Number(id),
        nilai: Number(nilai),
    }));

    form.transform((data) => ({ ...data, _method: 'put' }))
        .post(route('pelatih.penilaian.update', props.penilaian.id));
};
</script>

<template>
    <Head title="Edit Penilaian" />

    <AuthenticatedLayout>
        <template #header>Edit Penilaian — {{ penilaian.ekstra.nama }}</template>

        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Ringkasan -->
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-[#5B6472]">Rata-rata nilai saat ini</p>
                    <p class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">{{ rataRata }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Detail -->
                <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Detail Penilaian</h3>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Tanggal Penilaian</label>
                        <input v-model="form.tanggal" type="date"
                            class="w-full max-w-xs rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.tanggal" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Judul Penilaian</label>
                        <input v-model="form.judul" type="text"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.judul" class="mt-1 text-sm text-red-600">{{ form.errors.judul }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="3"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"></textarea>
                    </div>
                </div>

                <!-- Nilai per kelas -->
                <div class="space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Nilai Siswa</h3>
                    <div v-for="(list, namaKelas) in nilaiGrup" :key="namaKelas">
                        <h4 class="mb-2 rounded-lg bg-[#F4F7FC] px-3 py-2 font-semibold text-[#1B2333]">
                            Kelas {{ namaKelas }} ({{ list.length }} siswa)
                        </h4>
                        <div class="space-y-2">
                            <div v-for="d in list" :key="d.id"
                                class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2">
                                <span class="text-sm font-medium text-[#1B2333]">{{ d.nama }}</span>
                                <input v-model="nilaiMap[d.id]" type="number" min="0" max="100"
                                    class="w-24 rounded-lg border-gray-200 px-3 py-1.5 text-center text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :disabled="form.processing" class="px-8 py-3">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
