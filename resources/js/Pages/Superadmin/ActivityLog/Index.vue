<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ activities: Object });

// ─── Filter client-side ───────────────────────────────────────────────────────
const search = ref('');
const filterDesc = ref('all');

const descOptions = [
    { value: 'all', label: 'Semua' },
    { value: 'created', label: 'Dibuat' },
    { value: 'updated', label: 'Diperbarui' },
    { value: 'deleted', label: 'Dihapus' },
    { value: 'login', label: 'Login' },
];

const filteredData = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.activities.data.filter((log) => {
        const matchDesc = filterDesc.value === 'all' || log.description === filterDesc.value;
        const matchQ = !q
            || log.causer_name.toLowerCase().includes(q)
            || (log.subject_type ?? '').toLowerCase().includes(q)
            || log.description.toLowerCase().includes(q);
        return matchDesc && matchQ;
    });
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
const getBadgeColor = (description) => {
    switch (description) {
        case 'created': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'updated': return 'bg-[#3E6FD9]/10 text-[#3E6FD9] border-[#3E6FD9]/20';
        case 'deleted': return 'bg-red-100 text-red-700 border-red-200';
        case 'login':   return 'bg-purple-100 text-purple-700 border-purple-200';
        default:        return 'bg-gray-100 text-gray-600 border-gray-200';
    }
};

const formatDescription = (desc) => {
    if (desc === 'created') return '+ Dibuat';
    if (desc === 'updated') return '✎ Diperbarui';
    if (desc === 'deleted') return '✕ Dihapus';
    if (desc === 'login')   return '⬤ Login';
    return desc;
};

const roleBadge = (role) => {
    if (role === 'admin') return 'bg-[#3E6FD9]/10 text-[#3E6FD9]';
    if (role === 'pembimbing') return 'bg-emerald-100 text-emerald-700';
    if (role === 'pelatih') return 'bg-amber-100 text-amber-700';
    if (role === 'superadmin') return 'bg-purple-100 text-purple-700';
    return 'bg-gray-100 text-gray-500';
};

const roleLabel = (role) => {
    if (role === 'admin') return 'Admin';
    if (role === 'pembimbing') return 'Pembina';
    if (role === 'pelatih') return 'Pelatih';
    if (role === 'superadmin') return 'SuperAdmin';
    return role ?? '-';
};

// ─── Bersihkan Log ────────────────────────────────────────────────────────────
const form = useForm({});
const bersihkanLog = () => {
    if (confirm('Hapus SEMUA riwayat log aktivitas? Aksi ini tidak dapat dibatalkan.')) {
        form.post(route('superadmin.activity-log.clear'), { preserveScroll: true });
    }
};

// ─── Detail Modal ─────────────────────────────────────────────────────────────
const detailLog = ref(null);
</script>

<template>
    <Head title="Activity Log" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <span>Activity Log</span>
                <button @click="bersihkanLog" :disabled="form.processing"
                    class="flex items-center gap-2 rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ form.processing ? 'Membersihkan...' : 'Bersihkan Log' }}
                </button>
            </div>
        </template>

        <div class="space-y-5">
            <!-- Filter Bar -->
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 space-y-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Cari pengguna, objek, atau aksi..."
                            class="w-full rounded-xl border-gray-200 py-2.5 pl-9 pr-4 text-sm text-[#1B2333] shadow-sm focus:border-[#3E6FD9] focus:ring-2 focus:ring-[#3E6FD9]/25" />
                    </div>
                    <!-- Filter Desc -->
                    <div class="flex gap-2 flex-wrap">
                        <button v-for="opt in descOptions" :key="opt.value"
                            @click="filterDesc = opt.value"
                            :class="[
                                'rounded-full px-3 py-1.5 text-xs font-semibold transition',
                                filterDesc === opt.value
                                    ? 'bg-[#0B1B36] text-white'
                                    : 'bg-[#F4F7FC] text-[#5B6472] hover:bg-gray-200'
                            ]">
                            {{ opt.label }}
                        </button>
                    </div>
                </div>
                <p class="text-xs text-[#5B6472]">
                    Menampilkan <span class="font-semibold text-[#0B1B36]">{{ filteredData.length }}</span>
                    dari <span class="font-semibold text-[#0B1B36]">{{ activities.data.length }}</span> log di halaman ini
                </p>
            </div>

            <!-- Log Table -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#F4F7FC]">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Waktu</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Pengguna</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Aksi</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Objek Target</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#5B6472]">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="log in filteredData" :key="log.id" class="transition hover:bg-[#F4F7FC]/50">
                                <td class="px-5 py-3 text-xs whitespace-nowrap text-[#5B6472]">{{ log.created_at }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-sm text-[#0B1B36]">{{ log.causer_name }}</div>
                                    <span v-if="log.causer_role" :class="['mt-0.5 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold', roleBadge(log.causer_role)]">
                                        {{ roleLabel(log.causer_role) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span :class="['rounded-full border px-2.5 py-0.5 text-xs font-semibold', getBadgeColor(log.description)]">
                                        {{ formatDescription(log.description) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-[#5B6472]">
                                    <span v-if="log.subject_type" class="font-medium text-[#0B1B36]">{{ log.subject_type }}</span>
                                    <span v-if="log.subject_id" class="text-xs text-[#5B6472]"> #{{ log.subject_id }}</span>
                                    <span v-if="!log.subject_type" class="text-gray-300">—</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <button v-if="log.properties?.attributes || log.properties?.old"
                                        @click="detailLog = log"
                                        class="rounded-lg px-3 py-1 text-xs font-medium text-[#3E6FD9] transition hover:bg-[#3E6FD9]/10">
                                        Lihat
                                    </button>
                                    <span v-else class="text-xs text-gray-300">—</span>
                                </td>
                            </tr>
                            <tr v-if="filteredData.length === 0">
                                <td colspan="5" class="px-5 py-14 text-center text-sm text-[#5B6472]">
                                    Tidak ada log yang cocok dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="activities.links.length > 3" class="flex justify-center gap-1 flex-wrap">
                <template v-for="(link, i) in activities.links" :key="i">
                    <Link v-if="link.url" :href="link.url" v-html="link.label"
                        :class="[
                            'rounded-xl border px-3.5 py-2 text-sm font-medium transition',
                            link.active
                                ? 'border-[#3E6FD9] bg-[#3E6FD9] text-white'
                                : 'border-gray-200 bg-white text-[#5B6472] hover:bg-[#F4F7FC]'
                        ]" />
                    <span v-else v-html="link.label"
                        class="rounded-xl border border-gray-100 bg-gray-50 px-3.5 py-2 text-sm text-gray-300" />
                </template>
            </div>
        </div>

        <!-- Detail Modal -->
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="detailLog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @click.self="detailLog = null">
                <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                    <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                        <h3 class="font-['Poppins'] text-base font-semibold text-[#0B1B36]">Detail Perubahan</h3>
                        <button @click="detailLog = null" class="rounded-lg p-1.5 text-[#5B6472] hover:bg-gray-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div v-if="detailLog.properties?.old">
                            <p class="mb-2 text-xs font-semibold text-red-600 uppercase tracking-wide">Sebelum</p>
                            <pre class="rounded-xl bg-red-50 p-3 text-xs text-red-700 overflow-x-auto">{{ JSON.stringify(detailLog.properties.old, null, 2) }}</pre>
                        </div>
                        <div v-if="detailLog.properties?.attributes">
                            <p class="mb-2 text-xs font-semibold text-emerald-600 uppercase tracking-wide">Sesudah</p>
                            <pre class="rounded-xl bg-emerald-50 p-3 text-xs text-emerald-700 overflow-x-auto">{{ JSON.stringify(detailLog.properties.attributes, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
