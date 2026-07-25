<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    hr_signer_name: props.settings.hr_signer_name ?? 'Sugianto, S.Pd.I',
    hr_signer_title: props.settings.hr_signer_title ?? 'Waka Kesiswaan',
});

const submit = () => {
    form.post(route('admin.setting.update'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AuthenticatedLayout>
        <template #header>Pengaturan Sistem</template>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 max-w-2xl">
                <h3 class="mb-4 font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                    Pengaturan Cetak Laporan HR
                </h3>
                
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Nama Penandatangan (Kanan Bawah)</label>
                        <input
                            v-model="form.hr_signer_name"
                            type="text"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"
                        />
                        <div v-if="form.errors.hr_signer_name" class="mt-1 text-sm text-red-600">{{ form.errors.hr_signer_name }}</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[#1B2333]">Jabatan Penandatangan</label>
                        <input
                            v-model="form.hr_signer_title"
                            type="text"
                            class="w-full rounded-xl border-gray-200 px-4 py-2.5 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25"
                        />
                        <div v-if="form.errors.hr_signer_title" class="mt-1 text-sm text-red-600">{{ form.errors.hr_signer_title }}</div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
