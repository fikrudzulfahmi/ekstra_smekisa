<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    pelatih: Array,
    daftarEkstra: Array,
});

const form = useForm({
    id: null,
    nama: '',
    email: '',
    password: '',
    no_hp: '',
    ekstra_ids: [],
});

const isEditing = ref(false);

const submit = () => {
    if (isEditing.value) {
        form.put(route('pembimbing.pelatih.update', form.id), { onSuccess: resetForm });
    } else {
        form.post(route('pembimbing.pelatih.store'), { onSuccess: resetForm });
    }
};

const editItem = (item) => {
    isEditing.value = true;
    form.id = item.id;
    form.nama = item.nama;
    form.email = item.user.email;
    form.password = '';
    form.no_hp = item.no_hp ?? '';
    form.ekstra_ids = item.ekstra.map((e) => e.id);
};

const resetForm = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
};

const hapus = (item) => {
    if (confirm(`Hapus pelatih ${item.nama}? Ini juga akan menghapus akun login-nya.`)) {
        router.delete(route('pembimbing.pelatih.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Data Pelatih" />

    <AuthenticatedLayout>
        <template #header>Data Pelatih</template>

        <div class="space-y-6">
            <!-- Form -->
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                    {{ isEditing ? 'Edit' : 'Tambah' }} Pelatih
                </h3>
                <form @submit.prevent="submit" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Nama</label>
                        <input v-model="form.nama" type="text"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">No. HP</label>
                        <input v-model="form.no_hp" type="text"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Email (untuk login)</label>
                        <input v-model="form.email" type="email"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">
                            Password
                            <span v-if="isEditing" class="text-xs text-[#5B6472]">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input v-model="form.password" type="password"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>

                    <!-- Pilih Ekstra -->
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-[#1B2333]">Ekstra yang Dilatih</label>
                        <div class="flex flex-wrap gap-3">
                            <label v-for="e in daftarEkstra" :key="e.id"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 transition hover:bg-[#F4F7FC]">
                                <input type="checkbox" :value="e.id" v-model="form.ekstra_ids"
                                    class="rounded border-gray-300 text-[#3E6FD9] focus:ring-[#3E6FD9]/40" />
                                <span class="text-sm text-[#1B2333]">{{ e.nama }}</span>
                            </label>
                            <p v-if="daftarEkstra.length === 0" class="text-sm text-[#5B6472]">
                                Belum ada data ekstra. Tambahkan dulu di menu Ekstra.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 md:col-span-2">
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
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-[#F4F7FC]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Ekstra</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in pelatih" :key="item.id" class="transition hover:bg-[#F4F7FC]/60">
                            <td class="px-6 py-4 text-sm font-medium text-[#0B1B36]">{{ item.nama }}</td>
                            <td class="px-6 py-4 text-sm text-[#5B6472]">{{ item.user.email }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span v-for="e in item.ekstra" :key="e.id"
                                    class="mb-1 mr-1 inline-block rounded-full bg-[#3E6FD9]/10 px-2.5 py-0.5 text-xs font-medium text-[#3E6FD9]">
                                    {{ e.nama }}
                                </span>
                                <span v-if="item.ekstra.length === 0" class="text-xs text-[#5B6472]">-</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="editItem(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">Edit</button>
                                    <button @click="hapus(item)" class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="pelatih.length === 0">
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-[#5B6472]">Belum ada data pelatih.</td>
                        </tr>
                    </tbody>
                </table></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
