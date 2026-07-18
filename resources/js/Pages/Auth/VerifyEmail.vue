<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-6">
            <h2 class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">Verifikasi Email Anda</h2>
            <p class="mt-3 text-sm leading-relaxed text-[#5B6472]">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi
                alamat email Anda dengan mengklik tautan yang baru saja kami
                kirimkan. Jika belum menerima email, kami akan senang hati
                mengirimkannya lagi.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
        >
            Tautan verifikasi baru telah dikirim ke alamat email yang Anda
            daftarkan.
        </div>

        <form @submit.prevent="submit" class="flex items-center justify-between gap-4">
            <PrimaryButton
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                Kirim Ulang Email Verifikasi
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm font-medium text-[#5B6472] hover:text-[#0B1B36]"
            >
                Keluar
            </Link>
        </form>
    </GuestLayout>
</template>
