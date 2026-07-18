<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    ekstra: Array,
});

const form = useForm({ id: null, nama: '', deskripsi: '' });
const isEditing = ref(false);

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.ekstra.update', form.id), { onSuccess: resetForm });
    } else {
        form.post(route('admin.ekstra.store'), { onSuccess: resetForm });
    }
};

const editItem = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
    form.deskripsi = item.deskripsi ?? '';
};

const resetForm = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
};

const hapus = (item) => {
    if (confirm(`Hapus ekstrakurikuler ${item.nama}?`)) {
        router.delete(route('admin.ekstra.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Data Ekstrakurikuler" />

    <AuthenticatedLayout>
        <template #header>Data Ekstrakurikuler</template>

        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Form -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                    {{ isEditing ? 'Edit' : 'Tambah' }} Ekstrakurikuler
                </h3>
                <form @submit.prevent="submit" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Nama Ekstra</label>
                        <input
                            v-model="form.nama"
                            type="text"
                            placeholder="Contoh: Pramuka"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm placeholder:text-gray-400 focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"
                        />
                        <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Deskripsi (opsional)</label>
                        <textarea
                            v-model="form.deskripsi"
                            rows="2"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
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
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-[#F4F7FC]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Deskripsi</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in ekstra" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                            <td class="px-6 py-4 text-sm font-medium text-[#0B1B36]">{{ item.nama }}</td>
                            <td class="px-6 py-4 text-sm text-[#5B6472]">{{ item.deskripsi || '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="editItem(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Edit</button>
                                    <button @click="hapus(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="ekstra.length === 0">
                            <td colspan="3" class="px-6 py-10 text-center text-sm text-[#5B6472]">Belum ada data ekstrakurikuler.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
