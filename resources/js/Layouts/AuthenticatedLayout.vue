<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const initials = computed(() => {
    if (!user.value?.name) return '';
    return user.value.name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const sidebarOpen = ref(false);
</script>

<template>
    <div class="flex min-h-screen bg-[#FAFAFA] font-['Inter'] relative selection:bg-[#F2A93B] selection:text-[#0B1B36]">
        <!-- Latar Belakang Pola Grid -->
        <div class="absolute inset-0 z-0 bg-grid opacity-30 pointer-events-none"></div>

        <!-- Sidebar -->
        <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" class="z-30 relative" />

        <!-- Konten Utama -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Topbar -->
            <header class="sticky top-0 z-20 border-b border-black/5 bg-white/80 backdrop-blur-md print:hidden">
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            class="rounded-lg p-2 text-[#5B6472] transition hover:bg-[#F4F7FC] lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Buka menu"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>
                        <div class="min-w-0 font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                            <slot name="header" />
                        </div>
                    </div>

                    <Dropdown align="right" width="52">
                        <template #trigger>
                            <button class="flex items-center gap-3 rounded-full py-1 pl-1 pr-3 transition hover:bg-[#F4F7FC]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#0B1B36] text-xs font-semibold text-[#F2A93B]">
                                    {{ initials }}
                                </span>
                                <span class="hidden text-sm font-medium text-[#1B2333] sm:block">
                                    {{ user?.name }}
                                </span>
                                <svg class="hidden h-4 w-4 text-[#5B6472] sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profil
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Keluar
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Isi halaman -->
            <main class="flex-1">
                <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Notifikasi Flash -->
        <FlashMessage />
    </div>
</template>
<style scoped>
.bg-grid {
    background-size: 40px 40px;
    background-image: 
        linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
    mask-image: linear-gradient(to bottom, white 30%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, white 30%, transparent 100%);
}
</style>
