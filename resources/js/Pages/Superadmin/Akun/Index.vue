<script setup>
import { ref, computed, reactive } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { showConfirm } from '@/utils/sweetalert';

const props = defineProps({ users: Array });

// ─── Filter ───────────────────────────────────────────────────────────────────
const search = ref('');
const filterRole = ref('all');

const roleOptions = [
    { value: 'all', label: 'Semua Role' },
    { value: 'admin', label: 'Admin' },
    { value: 'pembimbing', label: 'Pembina' },
    { value: 'pelatih', label: 'Pelatih' },
];

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.users.filter((u) => {
        const matchRole = filterRole.value === 'all' || u.role === filterRole.value;
        const matchQ = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
        return matchRole && matchQ;
    });
});

const roleBadge = (role) => {
    if (role === 'admin') return 'bg-[#3E6FD9]/10 text-[#3E6FD9]';
    if (role === 'pembimbing') return 'bg-emerald-100 text-emerald-700';
    if (role === 'pelatih') return 'bg-amber-100 text-amber-700';
    return 'bg-gray-100 text-gray-600';
};

const roleLabel = (role) => {
    if (role === 'admin') return 'Admin';
    if (role === 'pembimbing') return 'Pembina';
    if (role === 'pelatih') return 'Pelatih';
    return role;
};

// ─── Modal state ──────────────────────────────────────────────────────────────
const modalMode = ref(null); // null | 'create' | 'edit' | 'password'
const selectedUser = ref(null);

// ─── Form Buat Akun Baru ──────────────────────────────────────────────────────
const formCreate = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'admin',
});

const bukaModalBuat = () => {
    formCreate.reset();
    modalMode.value = 'create';
};

const simpanAkun = () => {
    formCreate.post(route('superadmin.akun.store'), {
        preserveScroll: true,
        onSuccess: () => { modalMode.value = null; },
    });
};

// ─── Form Edit Akun ───────────────────────────────────────────────────────────
const formEdit = useForm({ name: '', email: '' });

const bukaModalEdit = (user) => {
    selectedUser.value = user;
    formEdit.name = user.name;
    formEdit.email = user.email;
    modalMode.value = 'edit';
};

const simpanEdit = () => {
    formEdit.put(route('superadmin.akun.update', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => { modalMode.value = null; },
    });
};

// ─── Form Reset Password ──────────────────────────────────────────────────────
const formPassword = useForm({ password: '', password_confirmation: '' });

const bukaModalPassword = (user) => {
    selectedUser.value = user;
    formPassword.reset();
    modalMode.value = 'password';
};

const simpanPassword = () => {
    formPassword.patch(route('superadmin.akun.reset-password', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => { modalMode.value = null; },
    });
};

// ─── Hapus ────────────────────────────────────────────────────────────────────
const hapus = (user) => {
    showConfirm(`Hapus akun "${user.name}" (${user.email})? Aksi ini tidak dapat dibatalkan.`).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('superadmin.akun.destroy', user.id), { preserveScroll: true });
        }
    });
};

const tutupModal = () => { modalMode.value = null; };

// ─── Stats ────────────────────────────────────────────────────────────────────
const stats = computed(() => ({
    total: props.users.length,
    admin: props.users.filter(u => u.role === 'admin').length,
    pembimbing: props.users.filter(u => u.role === 'pembimbing').length,
    pelatih: props.users.filter(u => u.role === 'pelatih').length,
}));
</script>

<template>
    <Head title="Kelola Akun" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>Kelola Akun</span>
                <button @click="bukaModalBuat"
                    class="flex items-center gap-2 rounded-full bg-[#0B1B36] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#122A52]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Akun
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-black/5">
                    <div class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">{{ stats.total }}</div>
                    <div class="mt-0.5 text-xs text-[#5B6472]">Total Akun</div>
                </div>
                <div class="rounded-2xl bg-[#3E6FD9]/5 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-[#3E6FD9]">{{ stats.admin }}</div>
                    <div class="mt-0.5 text-xs text-[#3E6FD9]">Admin</div>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-emerald-700">{{ stats.pembimbing }}</div>
                    <div class="mt-0.5 text-xs text-emerald-600">Pembina</div>
                </div>
                <div class="rounded-2xl bg-amber-50 p-4 text-center">
                    <div class="font-['Poppins'] text-2xl font-bold text-amber-700">{{ stats.pelatih }}</div>
                    <div class="mt-0.5 text-xs text-amber-600">Pelatih</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Cari nama atau email..."
                            class="w-full rounded-xl border-gray-200 py-2.5 pl-9 pr-4 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <!-- Role Filter -->
                    <div class="flex gap-2 flex-wrap">
                        <button v-for="opt in roleOptions" :key="opt.value"
                            @click="filterRole = opt.value"
                            :class="[
                                'rounded-full px-4 py-2 text-xs font-semibold transition',
                                filterRole === opt.value
                                    ? 'bg-[#0B1B36] text-white'
                                    : 'bg-[#F4F7FC] text-[#5B6472] hover:bg-gray-200'
                            ]">
                            {{ opt.label }}
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-xs text-[#5B6472]">
                    Menampilkan <span class="font-semibold text-[#0B1B36]">{{ filtered.length }}</span> dari
                    <span class="font-semibold text-[#0B1B36]">{{ users.length }}</span> akun
                </p>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#F4F7FC]">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Nama</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Email</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Role</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="user in filtered" :key="user.id" class="transition hover:bg-[#F4F7FC]/50">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#0B1B36] text-xs font-bold text-[#F2A93B]">
                                            {{ user.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() }}
                                        </div>
                                        <span class="text-sm font-medium text-[#0B1B36]">{{ user.name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-[#5B6472]">{{ user.email }}</td>
                                <td class="px-5 py-3.5">
                                    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', roleBadge(user.role)]">
                                        {{ roleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="bukaModalEdit(user)"
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">
                                            Edit
                                        </button>
                                        <button @click="bukaModalPassword(user)"
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-50">
                                            Reset Password
                                        </button>
                                        <button @click="hapus(user)"
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filtered.length === 0">
                                <td colspan="4" class="px-5 py-12 text-center text-sm text-[#5B6472]">
                                    Tidak ada akun yang cocok dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ─── Modal Overlay ─────────────────────────────────────────────────── -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="modalMode" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="tutupModal">
                <!-- ── Modal Buat Akun ── -->
                <div v-if="modalMode === 'create'" class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Buat Akun Baru</h3>
                        <p class="mt-0.5 text-xs text-[#5B6472]">Isi data akun yang akan dibuat</p>
                    </div>
                    <form @submit.prevent="simpanAkun" class="space-y-4 p-6">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Nama Lengkap</label>
                            <input v-model="formCreate.name" type="text" required
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <p v-if="formCreate.errors.name" class="mt-1 text-xs text-red-500">{{ formCreate.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Email</label>
                            <input v-model="formCreate.email" type="email" required
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <p v-if="formCreate.errors.email" class="mt-1 text-xs text-red-500">{{ formCreate.errors.email }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Role</label>
                            <select v-model="formCreate.role"
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25">
                                <option value="admin">Admin</option>
                                <option value="pembimbing">Pembina</option>
                                <option value="pelatih">Pelatih</option>
                            </select>
                            <p v-if="formCreate.errors.role" class="mt-1 text-xs text-red-500">{{ formCreate.errors.role }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Password</label>
                            <input v-model="formCreate.password" type="password" required minlength="6"
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <p v-if="formCreate.errors.password" class="mt-1 text-xs text-red-500">{{ formCreate.errors.password }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Konfirmasi Password</label>
                            <input v-model="formCreate.password_confirmation" type="password" required
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="tutupModal"
                                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-[#5B6472] transition hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="formCreate.processing"
                                class="flex-1 rounded-xl bg-[#0B1B36] py-2.5 text-sm font-semibold text-white transition hover:bg-[#122A52] disabled:opacity-60">
                                {{ formCreate.processing ? 'Menyimpan...' : 'Buat Akun' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ── Modal Edit Akun ── -->
                <div v-else-if="modalMode === 'edit'" class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Edit Akun</h3>
                        <p class="mt-0.5 text-xs text-[#5B6472]">{{ selectedUser?.name }}</p>
                    </div>
                    <form @submit.prevent="simpanEdit" class="space-y-4 p-6">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Nama Lengkap</label>
                            <input v-model="formEdit.name" type="text" required
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <p v-if="formEdit.errors.name" class="mt-1 text-xs text-red-500">{{ formEdit.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Email</label>
                            <input v-model="formEdit.email" type="email" required
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <p v-if="formEdit.errors.email" class="mt-1 text-xs text-red-500">{{ formEdit.errors.email }}</p>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="tutupModal"
                                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-[#5B6472] transition hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="formEdit.processing"
                                class="flex-1 rounded-xl bg-[#3E6FD9] py-2.5 text-sm font-semibold text-white transition hover:bg-[#3060C0] disabled:opacity-60">
                                {{ formEdit.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ── Modal Reset Password ── -->
                <div v-else-if="modalMode === 'password'" class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">Reset Password</h3>
                        <p class="mt-0.5 text-xs text-[#5B6472]">{{ selectedUser?.name }} · {{ selectedUser?.email }}</p>
                    </div>
                    <form @submit.prevent="simpanPassword" class="space-y-4 p-6">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700">
                            ⚠️ Password baru akan langsung berlaku. Pastikan informasikan ke pengguna bersangkutan.
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Password Baru</label>
                            <input v-model="formPassword.password" type="password" required minlength="6"
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                            <p v-if="formPassword.errors.password" class="mt-1 text-xs text-red-500">{{ formPassword.errors.password }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-[#5B6472]">Konfirmasi Password</label>
                            <input v-model="formPassword.password_confirmation" type="password" required
                                class="w-full rounded-xl border-gray-200 px-3 py-2.5 text-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="tutupModal"
                                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-[#5B6472] transition hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="formPassword.processing"
                                class="flex-1 rounded-xl bg-amber-500 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600 disabled:opacity-60">
                                {{ formPassword.processing ? 'Mereset...' : 'Reset Password' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
