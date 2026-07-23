<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    backups: Array,
});

const formBackup = useForm({});

const buatBackup = () => {
    formBackup.post(route('admin.backup.create'), {
        preserveScroll: true,
    });
};

const unduh = (path) => {
    window.location.href = route('admin.backup.download') + '?path=' + encodeURIComponent(path);
};

const hapus = (path) => {
    if (confirm('Hapus file backup ini?')) {
        router.delete(route('admin.backup.destroy'), {
            data: { path },
            preserveScroll: true,
        });
    }
};

const restoreFile = ref(null);
const formRestore = useForm({
    sql_file: null,
});

const handleFileUpload = (event) => {
    formRestore.sql_file = event.target.files[0];
};

const doRestore = () => {
    if (!formRestore.sql_file) {
        alert('Pilih file SQL terlebih dahulu.');
        return;
    }
    
    if (confirm('PERHATIAN! Proses restore akan MENIMPA seluruh database saat ini dengan data dari file backup. Anda yakin ingin melanjutkan?')) {
        formRestore.post(route('admin.backup.restore'), {
            preserveScroll: true,
            onSuccess: () => {
                restoreFile.value.value = '';
                formRestore.reset();
            }
        });
    }
};
</script>

<template>
    <Head title="Backup & Restore" />

    <AuthenticatedLayout>
        <template #header>Backup & Restore Database</template>

        <div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Card Backup -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 flex flex-col items-center text-center justify-center space-y-4">
                    <div class="h-16 w-16 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Backup Database</h3>
                        <p class="mt-1 text-sm text-[#5B6472]">
                            Buat cadangan data aplikasi Anda saat ini. Proses ini akan menghasilkan file zip berisi database.
                        </p>
                    </div>
                    <button @click="buatBackup" :disabled="formBackup.processing"
                        class="rounded-full bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50">
                        {{ formBackup.processing ? 'Memproses Backup...' : 'Buat Backup Sekarang' }}
                    </button>
                </div>

                <!-- Card Restore -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 flex flex-col items-center text-center justify-center space-y-4">
                    <div class="h-16 w-16 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Restore Database</h3>
                        <p class="mt-1 text-sm text-[#5B6472]">
                            Pulihkan database dari file `.sql` mentah (extracted dari file zip backup).
                        </p>
                    </div>
                    
                    <form @submit.prevent="doRestore" class="flex flex-col items-center gap-3 w-full">
                        <input type="file" ref="restoreFile" @change="handleFileUpload" accept=".sql"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" />
                        
                        <button type="submit" :disabled="formRestore.processing || !formRestore.sql_file"
                            class="w-full rounded-full bg-red-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50">
                            {{ formRestore.processing ? 'Memulihkan...' : 'Restore Database' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Backup -->
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-black/5 overflow-hidden mt-8">
                <div class="border-b border-gray-100 p-5 bg-[#F4F7FC]">
                    <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Riwayat Backup</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama File</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ukuran</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Tanggal</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in backups" :key="item.path" class="transition hover:bg-[#F4F7FC]/60">
                                <td class="px-5 py-3 text-sm font-medium text-[#0B1B36]">{{ item.name }}</td>
                                <td class="px-5 py-3 text-sm text-[#5B6472]">{{ item.size }}</td>
                                <td class="px-5 py-3 text-sm text-[#5B6472]">{{ item.date }}</td>
                                <td class="px-5 py-3 text-right">
                                    <button @click="unduh(item.path)" class="rounded-lg px-3 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10 mr-2">Unduh</button>
                                    <button @click="hapus(item.path)" class="rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="backups.length === 0">
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-[#5B6472]">
                                    Belum ada file backup yang tersedia.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </AuthenticatedLayout>
</template>
