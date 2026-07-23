<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    tahunPelajaran: Array,
});

const form = useForm({ id: null, nama: '' });
const isEditing = ref(false);

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.tahun-pelajaran.update', form.id), { onSuccess: resetForm });
    } else {
        form.post(route('admin.tahun-pelajaran.store'), { onSuccess: resetForm });
    }
};

const editItem = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
};

const resetForm = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
};

const setAktif = (item) => {
    router.patch(route('admin.tahun-pelajaran.aktif', item.id));
};

const hapus = (item) => {
    if (confirm(`Hapus tahun pelajaran ${item.nama}?`)) {
        router.delete(route('admin.tahun-pelajaran.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Tahun Pelajaran" />

    <AuthenticatedLayout>
        <template #header>Data Tahun Pelajaran</template>

        <div class="space-y-6">
            <!-- Form -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                    {{ isEditing ? 'Edit' : 'Tambah' }} Tahun Pelajaran
                </h3>
                <form @submit.prevent="submit" class="mt-5 flex flex-col items-start gap-3 sm:flex-row">
                    <div class="w-full flex-1">
                        <input
                            v-model="form.nama"
                            type="text"
                            placeholder="Contoh: 2025/2026"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm placeholder:text-gray-400 focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">
                            {{ form.errors.nama }}
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-3">
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ isEditing ? 'Update' : 'Simpan' }}
                        </PrimaryButton>
                        <button v-if="isEditing" type="button" @click="resetForm"
                            class="rounded-full border border-gray-200 px-6 py-2.5 text-sm font-semibold text-[#5B6472] transition hover:bg-gray-50">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">`n<table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-[#F4F7FC]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in tahunPelajaran" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                            <td class="px-6 py-4 text-sm font-medium text-[#0B1B36]">{{ item.nama }}</td>
                            <td class="px-6 py-4">
                                <span v-if="item.is_aktif"
                                    class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Aktif
                                </span>
                                <button v-else @click="setAktif(item)"
                                    class="text-xs font-medium text-[#3E6FD9] hover:underline">
                                    Jadikan Aktif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="editItem(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Edit</button>
                                    <button v-if="!item.is_aktif" @click="hapus(item)"
                                        class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="tahunPelajaran.length === 0">
                            <td colspan="3" class="px-6 py-10 text-center text-sm text-[#5B6472]">
                                Belum ada data tahun pelajaran.
                            </td>
                        </tr>
                    </tbody>
                </table>`n</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
