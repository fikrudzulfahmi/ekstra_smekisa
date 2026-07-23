<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    siswa: Array,
    daftarKelas: Array,
    daftarEkstra: Array,
    tahunAktif: Object,
    filters: Object,
});

const syncForm = useForm({ kelas_id: '' });

const doSync = () => {
    if (!syncForm.kelas_id) {
        alert('Pilih kelas dulu untuk sinkronisasi.');
        return;
    }
    syncForm.post(route('admin.siswa.sync'), { preserveScroll: true });
};

// Form Tambah Manual
const showTambahModal = ref(false);
const formTambah = useForm({
    nis: '',
    nisn: '',
    nama: '',
    jk: 'L',
    kelas_id: '',
    ekstra_id: '',
});

const submitTambah = () => {
    formTambah.post(route('admin.siswa.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showTambahModal.value = false;
            formTambah.reset();
        },
    });
};

// Filter kelas
const filterKelas = ref(props.filters?.kelas_id ?? '');
watch(filterKelas, (val) => {
    router.get(route('admin.siswa.index'), { kelas_id: val }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

// Ubah ekstra siswa langsung dari dropdown di tabel
const ubahEkstra = (siswa, event) => {
    router.patch(route('admin.siswa.ekstra', siswa.id), {
        ekstra_id: event.target.value || null,
    }, { preserveScroll: true });
};

const hapus = (item) => {
    if (confirm(`Hapus siswa ${item.nama}?`)) {
        router.delete(route('admin.siswa.destroy', item.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Data Siswa" />

    <AuthenticatedLayout>
        <template #header>Data Siswa</template>

        <div class="space-y-6">
            <!-- Info Tahun Aktif -->
            <div v-if="!tahunAktif" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                Belum ada tahun pelajaran aktif. Set dulu di menu Tahun Pelajaran.
            </div>
            <div v-else class="rounded-2xl border border-[#3E6FD9]/20 bg-[#3E6FD9]/5 p-4 text-sm text-[#1B2333]">
                Tahun Pelajaran Aktif: <b class="text-[#0B1B36]">{{ tahunAktif.nama }}</b> — semua data siswa di bawah ini terikat tahun ini.
            </div>

            <!-- Panel Sinkronisasi -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Sinkronisasi Siswa dari Data Induk</h3>
                <p class="mt-1 text-sm text-[#5B6472]">
                    Pilih kelas, lalu tarik data siswa dari aplikasi data induk melalui API.
                </p>
                <div class="mt-4 flex flex-col items-start gap-3 sm:flex-row sm:items-end">
                    <div class="w-full max-w-xs">
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Kelas</label>
                        <select v-model="syncForm.kelas_id"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">-- Pilih Kelas --</option>
                            <option v-for="k in daftarKelas" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                    </div>
                    <button @click="doSync" :disabled="syncForm.processing"
                        class="rounded-full bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50">
                        {{ syncForm.processing ? 'Menyinkron...' : 'Sinkronisasi' }}
                    </button>
                </div>
            </div>

            <!-- Filter & Tabel -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-4">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-[#1B2333]">Filter Kelas:</label>
                        <select v-model="filterKelas"
                            class="rounded-xl border-gray-200 py-2 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in daftarKelas" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <span class="text-sm text-[#5B6472]">Total: {{ siswa.length }} siswa</span>
                    </div>
                    
                    <button @click="showTambahModal = true"
                        class="rounded-full bg-[#0B1B36] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                        + Tambah Manual
                    </button>
                </div>

                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-[#F4F7FC]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">NIS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">JK</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstra</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in siswa" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                            <td class="px-4 py-3 text-sm text-[#5B6472]">{{ item.nis || '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#0B1B36]">{{ item.nama }}</td>
                            <td class="px-4 py-3 text-sm text-[#1B2333]">{{ item.jk || '-' }}</td>
                            <td class="px-4 py-3 text-sm text-[#1B2333]">{{ item.kelas?.nama }}</td>
                            <td class="px-4 py-3 text-sm">
                                <select :value="item.ekstra_id ?? ''" @change="ubahEkstra(item, $event)"
                                    class="rounded-lg border-gray-200 py-1.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                                    <option value="">- Belum ikut -</option>
                                    <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="hapus(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="siswa.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-[#5B6472]">
                                Belum ada data siswa. Lakukan sinkronisasi di atas.
                            </td>
                        </tr>
                    </tbody>
                </table></div>
            </div>
        </div>

        <!-- Modal Tambah Manual -->
        <Modal :show="showTambahModal" @close="showTambahModal = false" maxWidth="md">
            <div class="p-6">
                <h2 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36] mb-5">Tambah Siswa Manual</h2>
                
                <form @submit.prevent="submitTambah" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">NIS</label>
                        <input type="text" v-model="formTambah.nis" required placeholder="Contoh: 12345"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <span class="text-xs text-red-500" v-if="formTambah.errors.nis">{{ formTambah.errors.nis }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">NISN (Opsional)</label>
                        <input type="text" v-model="formTambah.nisn" placeholder="Contoh: 0051234567"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <span class="text-xs text-red-500" v-if="formTambah.errors.nisn">{{ formTambah.errors.nisn }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Nama Lengkap</label>
                        <input type="text" v-model="formTambah.nama" required placeholder="Nama Siswa"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <span class="text-xs text-red-500" v-if="formTambah.errors.nama">{{ formTambah.errors.nama }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Jenis Kelamin</label>
                        <select v-model="formTambah.jk" required
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                        <span class="text-xs text-red-500" v-if="formTambah.errors.jk">{{ formTambah.errors.jk }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Kelas</label>
                        <select v-model="formTambah.kelas_id" required
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="" disabled>-- Pilih Kelas --</option>
                            <option v-for="k in daftarKelas" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <span class="text-xs text-red-500" v-if="formTambah.errors.kelas_id">{{ formTambah.errors.kelas_id }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Ekstrakurikuler (Opsional)</label>
                        <select v-model="formTambah.ekstra_id"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                            <option value="">-- Belum Ikut --</option>
                            <option v-for="e in daftarEkstra" :key="e.id" :value="e.id">{{ e.nama }}</option>
                        </select>
                        <span class="text-xs text-red-500" v-if="formTambah.errors.ekstra_id">{{ formTambah.errors.ekstra_id }}</span>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showTambahModal = false"
                            class="rounded-full px-5 py-2.5 text-sm font-medium text-[#5B6472] transition hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" :disabled="formTambah.processing"
                            class="rounded-full bg-[#0B1B36] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#122A52] disabled:opacity-50">
                            {{ formTambah.processing ? 'Menyimpan...' : 'Simpan Siswa' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
