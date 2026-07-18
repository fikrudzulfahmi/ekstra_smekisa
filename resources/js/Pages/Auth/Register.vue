<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />

        <div class="mb-8">
            <h2 class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">Buat Akun Baru</h2>
            <p class="mt-2 text-sm text-[#5B6472]">Daftar untuk mengakses Sistem Presensi Ekstrakurikuler SMEKISA.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="Nama" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Kata Sandi" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1.5"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton
                class="w-full"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                Daftar
            </PrimaryButton>

            <p class="text-center text-sm text-[#5B6472]">
                Sudah punya akun?
                <Link :href="route('login')" class="font-medium text-[#3E6FD9] hover:text-[#0B1B36]">
                    Masuk di sini
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
