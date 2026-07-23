<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    daftarEkstra: Array,
    tahunAktif: Object,
    today: String,
});

const form = useForm({
    ekstra_id: '',
    tanggal: props.today,
    materi: '',
    deskripsi: '',
    foto: null,
    presensi: [],
});

const previewUrl = computed(() =>
    form.foto ? URL.createObjectURL(form.foto) : null
);

watch(() => form.foto, (newVal, oldVal) => {
    if (oldVal) URL.revokeObjectURL(URL.createObjectURL(oldVal));
});

// Data siswa dikelompokkan per kelas
const grupSiswa = ref({});
const loadingSiswa = ref(false);
const statusMap = reactive({});

const STATUS = ['hadir', 'izin', 'sakit', 'alpha'];

const loadSiswa = async () => {
    if (!form.ekstra_id) {
        grupSiswa.value = {};
        return;
    }
    loadingSiswa.value = true;
    try {
        const { data } = await axios.get(route('pelatih.kegiatan.siswa'), {
            params: { ekstra_id: form.ekstra_id },
        });
        grupSiswa.value = data;

        Object.keys(statusMap).forEach((k) => delete statusMap[k]);
        Object.values(data).flat().forEach((s) => {
            statusMap[s.id] = 'hadir';
        });
    } catch (e) {
        alert('Gagal memuat data siswa.');
    } finally {
        loadingSiswa.value = false;
    }
};

const submit = () => {
    form.presensi = Object.entries(statusMap).map(([siswa_id, status]) => ({
        siswa_id: Number(siswa_id),
        status,
    }));
    form.post(route('pelatih.kegiatan.store'), {
        forceFormData: true,
    });
};

const warnaStatus = (status) => ({
    hadir: 'bg-emerald-100 text-emerald-700',
    izin: 'bg-yellow-100 text-yellow-700',
    sakit: 'bg-orange-100 text-orange-700',
    alpha: 'bg-red-100 text-red-700',
}[status]);
</script>

<template>
    <Head title="Buat Kegiatan" />

    <AuthenticatedLayout>
        <template #header>Buat Kegiatan Ekstra</template>

        <div class="space-y-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Detail Kegiatan -->
                <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Detail Kegiatan</h3>

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
                            <label class="mb-1 block text-sm font-medium text-[#1B2333]">Tanggal</label>
                            <input v-model="form.tanggal" type="date"
                                class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <div v-if="form.errors.tanggal" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Materi</label>
                        <input v-model="form.materi" type="text" placeholder="Contoh: Latihan baris-berbaris"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm placeholder:text-gray-400 focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.materi" class="mt-1 text-sm text-red-600">{{ form.errors.materi }}</div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Deskripsi (opsional)</label>
                        <textarea v-model="form.deskripsi" rows="3"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Foto Kegiatan (opsional)</label>
                        <input type="file" accept="image/*" capture="environment"
                            @change="form.foto = $event.target.files[0]"
                            class="block w-full text-sm text-[#5B6472] file:mr-4 file:rounded-full file:border-0 file:bg-[#3E6FD9]/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-[#3E6FD9] hover:file:bg-[#3E6FD9]/20" />
                        <p class="mt-1 text-xs text-[#5B6472]">
                            Di HP akan membuka kamera. Di komputer bisa pilih file. Maks 5MB.
                        </p>
                        <div v-if="form.errors.foto" class="mt-1 text-sm text-red-600">{{ form.errors.foto }}</div>

                        <div v-if="form.foto" class="mt-2">
                            <img :src="previewUrl" alt="Preview" class="h-32 rounded-xl border border-gray-200 object-cover" />
                        </div>
                    </div>
                </div>

                <!-- Presensi Grouping per Kelas -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="mb-4 font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Presensi Siswa</h3>

                    <div v-if="loadingSiswa" class="py-8 text-center text-sm text-[#5B6472]">Memuat siswa...</div>

                    <div v-else-if="!form.ekstra_id" class="py-8 text-center text-sm text-[#5B6472]">
                        Pilih ekstrakurikuler dulu untuk memuat daftar siswa.
                    </div>

                    <div v-else-if="Object.keys(grupSiswa).length === 0" class="py-8 text-center text-sm text-[#5B6472]">
                        Belum ada siswa yang terdaftar di ekstra ini.
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="(siswaList, namaKelas) in grupSiswa" :key="namaKelas">
                            <h4 class="mb-2 rounded-lg bg-[#F4F7FC] px-3 py-2 font-semibold text-[#1B2333]">
                                Kelas {{ namaKelas }} ({{ siswaList.length }} siswa)
                            </h4>
                            <div class="space-y-2">
                                <div v-for="s in siswaList" :key="s.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2">
                                    <span class="text-sm font-medium text-[#1B2333]">{{ s.nama }}</span>
                                    <div class="flex gap-1">
                                        <button v-for="st in STATUS" :key="st" type="button"
                                            @click="statusMap[s.id] = st"
                                            :class="[
                                                'rounded-lg px-3 py-1 text-xs capitalize transition',
                                                statusMap[s.id] === st
                                                    ? warnaStatus(st) + ' font-semibold ring-2 ring-offset-1 ring-current'
                                                    : 'bg-gray-50 text-[#5B6472] hover:bg-gray-100'
                                            ]">
                                            {{ st }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="flex justify-end">
                    <PrimaryButton type="submit" :disabled="form.processing || Object.keys(grupSiswa).length === 0" class="px-8 py-3">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Kegiatan' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
