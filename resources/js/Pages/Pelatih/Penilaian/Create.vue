<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    daftarEkstra: Array,
    today: String,
});

const form = useForm({
    ekstra_id: '',
    tanggal: props.today,
    judul: '',
    deskripsi: '',
    nilai: [],
});

const grupSiswa = ref({});
const loadingSiswa = ref(false);
const nilaiMap = reactive({});

const loadSiswa = async () => {
    if (!form.ekstra_id) {
        grupSiswa.value = {};
        return;
    }
    loadingSiswa.value = true;
    try {
        const { data } = await axios.get(route('pelatih.penilaian.siswa'), {
            params: { ekstra_id: form.ekstra_id },
        });
        grupSiswa.value = data;

        Object.keys(nilaiMap).forEach((k) => delete nilaiMap[k]);
        Object.values(data).flat().forEach((s) => {
            nilaiMap[s.id] = null;
        });
    } catch (e) {
        alert('Gagal memuat data siswa.');
    } finally {
        loadingSiswa.value = false;
    }
};

const jumlahSiswa = computed(() => Object.keys(nilaiMap).length);
const jumlahTerisi = computed(
    () => Object.values(nilaiMap).filter((v) => v !== null && v !== '').length
);

const isiSemuaSama = ref(null);
const terapkanKeSemua = () => {
    if (isiSemuaSama.value === null || isiSemuaSama.value === '') return;
    Object.keys(nilaiMap).forEach((id) => {
        nilaiMap[id] = isiSemuaSama.value;
    });
};

const submit = () => {
    form.nilai = Object.entries(nilaiMap).map(([siswa_id, nilai]) => ({
        siswa_id: Number(siswa_id),
        nilai: nilai === '' || nilai === null ? null : Number(nilai),
    }));

    if (form.nilai.some((n) => n.nilai === null)) {
        alert('Semua siswa harus diisi nilainya (0–100) sebelum disimpan.');
        return;
    }

    form.post(route('pelatih.penilaian.store'));
};
</script>

<template>
    <Head title="Buat Penilaian" />

    <AuthenticatedLayout>
        <template #header>Buat Penilaian</template>

        <div>
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Detail Penilaian -->
                <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Detail Penilaian</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-[#1B2333]">Ekstrakurikuler</label>
                            <select v-model="form.ekstra_id" @change="loadSiswa"
                                class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                                <option value="">-- Pilih Ekstra --</option>
                                <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                            </select>
                            <div v-if="form.errors.ekstra_id" class="mt-1 text-sm text-red-600">{{ form.errors.ekstra_id }}</div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-[#1B2333]">Tanggal Penilaian</label>
                            <input v-model="form.tanggal" type="date"
                                class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <div v-if="form.errors.tanggal" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Judul Penilaian</label>
                        <input v-model="form.judul" type="text" placeholder="Contoh: Ujian Kenaikan Sabuk"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm placeholder:text-gray-400 focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.judul" class="mt-1 text-sm text-red-600">{{ form.errors.judul }}</div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Deskripsi (opsional)</label>
                        <textarea v-model="form.deskripsi" rows="3"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"></textarea>
                    </div>
                </div>

                <!-- Input Nilai per Siswa -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Nilai Siswa</h3>
                        <span v-if="jumlahSiswa > 0" class="text-sm text-[#5B6472]">
                            Terisi {{ jumlahTerisi }} / {{ jumlahSiswa }}
                        </span>
                    </div>

                    <div v-if="loadingSiswa" class="py-8 text-center text-sm text-[#5B6472]">Memuat siswa...</div>

                    <div v-else-if="!form.ekstra_id" class="py-8 text-center text-sm text-[#5B6472]">
                        Pilih ekstrakurikuler dulu untuk memuat daftar siswa.
                    </div>

                    <div v-else-if="Object.keys(grupSiswa).length === 0" class="py-8 text-center text-sm text-[#5B6472]">
                        Belum ada siswa yang terdaftar di ekstra ini.
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Isi nilai yang sama untuk semua siswa sekaligus -->
                        <div class="flex flex-wrap items-center gap-2 rounded-xl bg-[#F4F7FC] p-3">
                            <label class="text-sm text-[#5B6472]">Isi nilai sama untuk semua siswa:</label>
                            <input v-model="isiSemuaSama" type="number" min="0" max="100" placeholder="0-100"
                                class="w-24 rounded-lg border-gray-200 px-3 py-1.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <button type="button" @click="terapkanKeSemua"
                                class="rounded-lg bg-[#3E6FD9]/10 px-3 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/20">
                                Terapkan
                            </button>
                        </div>

                        <div v-for="(siswaList, namaKelas) in grupSiswa" :key="namaKelas">
                            <h4 class="mb-2 rounded-lg bg-[#F4F7FC] px-3 py-2 font-semibold text-[#1B2333]">
                                Kelas {{ namaKelas }} ({{ siswaList.length }} siswa)
                            </h4>
                            <div class="space-y-2">
                                <div v-for="s in siswaList" :key="s.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2">
                                    <span class="text-sm font-medium text-[#1B2333]">{{ s.nama }}</span>
                                    <input v-model="nilaiMap[s.id]" type="number" min="0" max="100" placeholder="0-100"
                                        class="w-24 rounded-lg border-gray-200 px-3 py-1.5 text-center text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :disabled="form.processing || Object.keys(grupSiswa).length === 0" class="px-8 py-3">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Penilaian' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
