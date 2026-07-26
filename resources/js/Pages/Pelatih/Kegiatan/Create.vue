<script setup>
import { ref, reactive, computed, watch, useTemplateRef } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import imageCompression from 'browser-image-compression';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { showErrorAlert } from '@/utils/sweetalert';

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

const isCompressing = ref(false);
const inputKamera = useTemplateRef('inputKamera');
const inputFile = useTemplateRef('inputFile');

const handleImageUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showErrorAlert('File harus berupa gambar');
        return;
    }

    isCompressing.value = true;
    try {
        const options = {
            maxSizeMB: 1,
            maxWidthOrHeight: 1280,
            useWebWorker: true,
            fileType: 'image/jpeg',
        };
        
        const compressedFile = await imageCompression(file, options);
        // Pertahankan nama file asli tetapi ubah ekstensinya menjadi .jpg
        const newFile = new File([compressedFile], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
            type: 'image/jpeg',
        });
        form.foto = newFile;
    } catch (error) {
        console.error('Error compressing image:', error);
        showErrorAlert('Gagal mengompres gambar. Coba gambar lain atau matikan format HEIC pada kamera.');
    } finally {
        isCompressing.value = false;
    }
};

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
        showErrorAlert('Gagal memuat data siswa.');
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
                        <label class="mb-2 block text-sm font-medium text-[#1B2333]">Foto Kegiatan (opsional)</label>

                        <!-- Hidden inputs -->
                        <input ref="inputKamera" type="file" accept="image/jpeg, image/png, image/webp" capture="environment"
                            @change="handleImageUpload" class="hidden" />
                        <input ref="inputFile" type="file" accept="image/jpeg, image/png, image/webp"
                            @change="handleImageUpload" class="hidden" />

                        <!-- Tombol pilihan -->
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="inputKamera.click()"
                                class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[#3E6FD9]/40 bg-[#3E6FD9]/5 px-4 py-4 text-center transition hover:border-[#3E6FD9] hover:bg-[#3E6FD9]/10 active:scale-95">
                                <svg class="h-7 w-7 text-[#3E6FD9]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-xs font-semibold text-[#3E6FD9]">Buka Kamera</span>
                            </button>

                            <button type="button" @click="inputFile.click()"
                                class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-center transition hover:border-gray-400 hover:bg-gray-100 active:scale-95">
                                <svg class="h-7 w-7 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs font-semibold text-gray-600">Pilih dari Galeri</span>
                            </button>
                        </div>

                        <p class="mt-2 text-xs text-[#5B6472]">Ukuran foto akan dikompres otomatis.</p>
                        <div v-if="isCompressing" class="mt-2 text-sm text-[#F2A93B] font-medium animate-pulse">
                            Sedang memproses dan mengompres foto...
                        </div>
                        <div v-if="form.errors.foto" class="mt-1 text-sm text-red-600">{{ form.errors.foto }}</div>

                        <div v-if="form.foto" class="mt-3">
                            <p class="mb-1 text-xs text-[#5B6472]">Preview:</p>
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
                    <PrimaryButton type="submit" :disabled="form.processing || Object.keys(grupSiswa).length === 0 || isCompressing" class="px-8 py-3">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Kegiatan' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
