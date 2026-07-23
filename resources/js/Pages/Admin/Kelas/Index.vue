<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    kelas: Array,
});

const form = useForm({ id: null, nama: '' });
const isEditing = ref(false);

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.kelas.update', form.id), { onSuccess: resetForm });
    } else {
        form.post(route('admin.kelas.store'), { onSuccess: resetForm });
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

const hapus = (item) => {
    if (confirm(`Hapus kelas ${item.nama}?`)) {
        router.delete(route('admin.kelas.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Data Kelas" />

    <AuthenticatedLayout>
        <template #header>Data Kelas</template>

        <div>
            <!-- Form -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                    {{ isEditing ? 'Edit' : 'Tambah' }} Kelas
                </h3>
                <p class="mt-1 text-sm text-[#5B6472]">
                    Tulis nama kelas <b class="text-[#1B2333]">persis sama</b> dengan rombel di aplikasi data induk. Contoh: <code class="rounded bg-[#F4F7FC] px-1.5 py-0.5 text-xs">10 TKJ 1</code>
                </p>
                <form @submit.prevent="submit" class="mt-5 flex flex-col items-start gap-3 sm:flex-row">
                    <div class="w-full flex-1">
                        <input
                            v-model="form.nama"
                            type="text"
                            placeholder="Contoh: 10 TKJ 1"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm placeholder:text-gray-400 focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"
                        />
                        <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}</div>
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
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-[#F4F7FC]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama Kelas</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in kelas" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                            <td class="px-6 py-4 text-sm font-medium text-[#0B1B36]">{{ item.nama }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="editItem(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Edit</button>
                                    <button @click="hapus(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="kelas.length === 0">
                            <td colspan="2" class="px-6 py-10 text-center text-sm text-[#5B6472]">Belum ada data kelas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
