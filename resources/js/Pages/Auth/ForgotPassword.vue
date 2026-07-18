<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi" />

        <div class="mb-8">
            <h2 class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">Lupa Kata Sandi?</h2>
            <p class="mt-2 text-sm text-[#5B6472]">
                Tidak masalah. Masukkan email Anda dan kami akan kirimkan tautan
                untuk membuat kata sandi baru.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <PrimaryButton
                class="w-full"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                Kirim Tautan Reset Kata Sandi
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
