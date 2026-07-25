<script setup>
import { reactive, computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import imageCompression from 'browser-image-compression';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    kegiatan: Object,
    presensiGrup: Object,
    rekap: Object,
});

const form = useForm({
    tanggal: props.kegiatan.tanggal?.substring(0, 10),
    materi: props.kegiatan.materi,
    deskripsi: props.kegiatan.deskripsi ?? '',
    foto: null,
    presensi: [],
});

const statusMap = reactive({});
Object.values(props.presensiGrup).flat().forEach((p) => {
    statusMap[p.id] = p.status;
});

const STATUS = ['hadir', 'izin', 'sakit', 'alpha'];

const submit = () => {
    form.presensi = Object.entries(statusMap).map(([id, status]) => ({
        id: Number(id),
        status,
    }));
    form.transform((data) => ({ ...data, _method: 'put' }))
        .post(route('pelatih.kegiatan.update', props.kegiatan.id));
};

const previewUrl = computed(() =>
    form.foto ? URL.createObjectURL(form.foto) : null
);

const isCompressing = ref(false);

const handleImageUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar');
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
        const newFile = new File([compressedFile], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
            type: 'image/jpeg',
        });
        form.foto = newFile;
    } catch (error) {
        console.error('Error compressing image:', error);
        alert('Gagal mengompres gambar. Coba gambar lain atau matikan format HEIC pada kamera.');
    } finally {
        isCompressing.value = false;
    }
};

const warnaStatus = (status) => ({
    hadir: 'bg-emerald-100 text-emerald-700',
    izin: 'bg-yellow-100 text-yellow-700',
    sakit: 'bg-orange-100 text-orange-700',
    alpha: 'bg-red-100 text-red-700',
}[status]);
</script>

<template>
    <Head title="Edit Kegiatan" />

    <AuthenticatedLayout>
        <template #header>Edit Kegiatan — {{ kegiatan.ekstra.nama }}</template>

        <div class="space-y-6">
            <!-- Rekap Kehadiran -->
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <h3 class="mb-3 text-sm font-medium text-[#5B6472]">Rekap Kehadiran Kegiatan Ini</h3>
                <div class="grid grid-cols-5 gap-3 text-center">
                    <div class="rounded-xl bg-emerald-50 p-3">
                        <div class="font-['Poppins'] text-2xl font-bold text-emerald-600">{{ rekap.hadir }}</div>
                        <div class="text-xs text-[#5B6472]">Hadir</div>
                    </div>
                    <div class="rounded-xl bg-yellow-50 p-3">
                        <div class="font-['Poppins'] text-2xl font-bold text-yellow-600">{{ rekap.izin }}</div>
                        <div class="text-xs text-[#5B6472]">Izin</div>
                    </div>
                    <div class="rounded-xl bg-orange-50 p-3">
                        <div class="font-['Poppins'] text-2xl font-bold text-orange-600">{{ rekap.sakit }}</div>
                        <div class="text-xs text-[#5B6472]">Sakit</div>
                    </div>
                    <div class="rounded-xl bg-red-50 p-3">
                        <div class="font-['Poppins'] text-2xl font-bold text-red-600">{{ rekap.alpha }}</div>
                        <div class="text-xs text-[#5B6472]">Alpha</div>
                    </div>
                    <div class="rounded-xl bg-[#F4F7FC] p-3">
                        <div class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">{{ rekap.total }}</div>
                        <div class="text-xs text-[#5B6472]">Total</div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Detail -->
                <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Detail Kegiatan</h3>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Tanggal</label>
                        <input v-model="form.tanggal" type="date"
                            class="w-full max-w-xs rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.tanggal" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Materi</label>
                        <input v-model="form.materi" type="text"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.materi" class="mt-1 text-sm text-red-600">{{ form.errors.materi }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="3"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Foto Kegiatan</label>

                        <div v-if="kegiatan.foto && !form.foto" class="mb-2">
                            <p class="mb-1 text-xs text-[#5B6472]">Foto saat ini:</p>
                            <img :src="`/storage/${kegiatan.foto}`" alt="Foto saat ini"
                                class="h-32 rounded-xl border border-gray-200 object-cover" />
                        </div>

                        <div class="rounded-xl border border-gray-200 p-3 bg-gray-50/50">
                            <label class="mb-2 block text-xs font-medium text-gray-500">Upload File / Ambil Gambar</label>
                            <input type="file" accept="image/jpeg, image/png, image/webp"
                                @change="handleImageUpload"
                                class="block w-full text-sm text-[#5B6472] file:mr-4 file:rounded-full file:border-0 file:bg-[#3E6FD9]/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-[#3E6FD9] hover:file:bg-[#3E6FD9]/20" />
                        </div>
                        <p class="mt-2 text-xs text-[#5B6472]">Kosongkan jika tidak ingin mengganti foto. Di HP ukuran otomatis dikompres.</p>
                        <div v-if="isCompressing" class="mt-2 text-sm text-[#F2A93B] font-medium animate-pulse">
                            Sedang memproses dan mengompres foto...
                        </div>
                        <div v-if="form.errors.foto" class="mt-1 text-sm text-red-600">{{ form.errors.foto }}</div>

                        <div v-if="form.foto" class="mt-2">
                            <p class="mb-1 text-xs text-[#5B6472]">Foto baru:</p>
                            <img :src="previewUrl" alt="Preview" class="h-32 rounded-xl border border-gray-200 object-cover" />
                        </div>
                    </div>
                </div>

                <!-- Presensi per kelas -->
                <div class="space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Presensi Siswa</h3>
                    <div v-for="(list, namaKelas) in presensiGrup" :key="namaKelas">
                        <h4 class="mb-2 rounded-lg bg-[#F4F7FC] px-3 py-2 font-semibold text-[#1B2333]">
                            Kelas {{ namaKelas }} ({{ list.length }} siswa)
                        </h4>
                        <div class="space-y-2">
                            <div v-for="p in list" :key="p.id"
                                class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-3 py-2">
                                <span class="text-sm font-medium text-[#1B2333]">{{ p.siswa.nama }}</span>
                                <div class="flex gap-1">
                                    <button v-for="st in STATUS" :key="st" type="button"
                                        @click="statusMap[p.id] = st"
                                        :class="[
                                            'rounded-lg px-3 py-1 text-xs capitalize transition',
                                            statusMap[p.id] === st
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

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :disabled="form.processing || isCompressing" class="px-8 py-3">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
