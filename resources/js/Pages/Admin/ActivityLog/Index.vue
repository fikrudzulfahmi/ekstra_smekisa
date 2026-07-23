<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    activities: Object,
});

const getBadgeColor = (description) => {
    switch (description) {
        case 'created':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'updated':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'deleted':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'login':
            return 'bg-purple-100 text-purple-800 border-purple-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const formatDescription = (desc) => {
    if (desc === 'created') return 'Ditambahkan';
    if (desc === 'updated') return 'Diperbarui';
    if (desc === 'deleted') return 'Dihapus';
    if (desc === 'login') return 'Login Sistem';
    return desc;
};

const form = useForm({});

const bersihkanLog = () => {
    if (confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat log aktivitas? Aksi ini tidak dapat dibatalkan.')) {
        form.post(route('admin.activity-log.clear'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Activity Log" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activity Log (Riwayat Aktivitas)</h2>
                <button 
                    @click="bersihkanLog"
                    :disabled="form.processing"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors"
                >
                    <span v-if="form.processing">Membersihkan...</span>
                    <span v-else>Bersihkan Log</span>
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 border">Waktu</th>
                                    <th class="px-4 py-3 border">Pengguna</th>
                                    <th class="px-4 py-3 border">Aksi</th>
                                    <th class="px-4 py-3 border">Objek Target</th>
                                    <th class="px-4 py-3 border">Detail (Log)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in activities.data" :key="log.id" class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 border whitespace-nowrap">
                                        {{ log.created_at }}
                                    </td>
                                    <td class="px-4 py-3 border font-medium text-gray-900">
                                        {{ log.causer_name }}
                                    </td>
                                    <td class="px-4 py-3 border">
                                        <span :class="['px-2 py-1 text-xs font-semibold rounded-full border', getBadgeColor(log.description)]">
                                            {{ formatDescription(log.description) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border">
                                        <span v-if="log.subject_type" class="font-semibold text-gray-700">{{ log.subject_type }} #{{ log.subject_id }}</span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 border max-w-xs truncate" :title="JSON.stringify(log.properties, null, 2)">
                                        <div v-if="log.properties.attributes" class="text-xs">
                                            <span class="text-green-600 font-semibold">New:</span> 
                                            {{ JSON.stringify(log.properties.attributes).substring(0, 50) }}...
                                        </div>
                                        <div v-if="log.properties.old" class="text-xs mt-1">
                                            <span class="text-red-600 font-semibold">Old:</span> 
                                            {{ JSON.stringify(log.properties.old).substring(0, 50) }}...
                                        </div>
                                        <div v-if="!log.properties.attributes && !log.properties.old" class="text-xs text-gray-500 italic">
                                            Tidak ada detail perubahan (atau log manual)
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="activities.data.length === 0">
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada aktivitas terekam.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center" v-if="activities.links.length > 3">
                        <div class="flex space-x-1">
                            <template v-for="(link, i) in activities.links" :key="i">
                                <Link 
                                    v-if="link.url"
                                    :href="link.url" 
                                    v-html="link.label"
                                    class="px-3 py-1 border rounded"
                                    :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                />
                                <span 
                                    v-else 
                                    v-html="link.label" 
                                    class="px-3 py-1 border rounded bg-gray-100 text-gray-400 cursor-not-allowed"
                                ></span>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
