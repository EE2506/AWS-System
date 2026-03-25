<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarCollapsed = ref(true);
const mobileMenuOpen = ref(false);

const page = usePage();

// Determine if user is admin
const isAdmin = computed(() => {
    return page.props.auth.user?.roles?.some(role => role.name === 'admin') ?? false;
});

// Role label for display
const roleLabel = computed(() => isAdmin.value ? 'Administrator' : 'User');

// Toggle sidebar
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

// Toggle mobile menu
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Navigation items
const navItems = computed(() => {
    const items = [
        {
            name: 'Dashboard',
            href: route('dashboard'),
            active: route().current('dashboard'),
            icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        },
        {
            name: 'Documents',
            href: route('documents.index'),
            active: route().current('documents.*'),
            icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        },
    ];

    if (isAdmin.value) {
        items.push({
            name: 'Users',
            href: route('admin.users.index'),
            active: route().current('admin.users.*'),
            icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
        });
    }

    return items;
});

// Bottom nav items
const bottomNavItems = [
    {
        name: 'Profile',
        href: route('profile.edit'),
        active: route().current('profile.*'),
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
];
</script>

<template>
    <div class="min-h-screen bg-slate-900">
        <!-- Mobile menu overlay -->
        <div
            v-if="mobileMenuOpen"
            class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm lg:hidden"
            @click="toggleMobileMenu"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col shadow-xl transition-all duration-300 ease-in-out',
                sidebarCollapsed ? 'w-20' : 'w-72',
                mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                isAdmin ? 'bg-slate-800' : 'bg-slate-800'
            ]"
        >
            <!-- Sidebar Header with Logo -->
            <div 
                :class="[
                    'flex h-16 items-center justify-center border-b px-4',
                    isAdmin ? 'border-red-500/30 bg-gradient-to-r from-slate-800 via-red-900/30 via-50% to-slate-800' : 'border-emerald-500/30 bg-gradient-to-r from-slate-800 via-emerald-900/30 via-50% to-slate-800'
                ]"
            >
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <!-- Mini logo when collapsed -->
                    <img 
                        v-if="sidebarCollapsed" 
                        src="/images/logo-trans-mini.png" 
                        alt="AWS System" 
                        class="h-10 w-auto shrink-0"
                    />
                    <!-- Full logo when expanded -->
                    <ApplicationLogo v-else class="h-10 w-auto shrink-0" />
                </Link>
            </div>

            <!-- User Profile Section -->
            <div 
                :class="[
                    'border-b px-4 py-4',
                    isAdmin ? 'border-red-500/20 bg-slate-900/50' : 'border-emerald-500/20 bg-slate-900/50'
                ]"
            >
                <div class="flex items-center gap-3">
                    <!-- Avatar -->
                    <div 
                        :class="[
                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white font-semibold shadow-md',
                            isAdmin 
                                ? 'bg-gradient-to-br from-red-400 to-red-600' 
                                : 'bg-gradient-to-br from-emerald-400 to-emerald-600'
                        ]"
                    >
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    
                    <div v-if="!sidebarCollapsed" class="min-w-0 flex-1 transition-opacity duration-200">
                        <p class="truncate font-medium text-white">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <span 
                            :class="[
                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                isAdmin 
                                    ? 'bg-red-500/20 text-red-300' 
                                    : 'bg-emerald-500/20 text-emerald-300'
                            ]"
                        >
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <ul class="space-y-1">
                    <li v-for="item in navItems" :key="item.name" class="relative">
                        <Link
                            :href="item.href"
                            :class="[
                                'group flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition-all duration-200',
                                item.active
                                    ? isAdmin 
                                        ? 'bg-red-500/20 text-red-300 shadow-sm' 
                                        : 'bg-emerald-500/20 text-emerald-300 shadow-sm'
                                    : 'text-gray-300 hover:bg-slate-700 hover:text-white',
                                sidebarCollapsed ? 'justify-center' : ''
                            ]"
                        >
                            <svg
                                :class="[
                                'h-6 w-6 shrink-0 transition-transform duration-200 group-hover:scale-110',
                                    item.active
                                        ? isAdmin ? 'text-red-400' : 'text-emerald-400'
                                        : 'text-gray-400 group-hover:text-white'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="item.icon"
                                />
                            </svg>
                            
                            <span v-if="!sidebarCollapsed" class="whitespace-nowrap">
                                {{ item.name }}
                            </span>
                            
                            <!-- Active indicator dot when collapsed -->
                            <span 
                                v-if="sidebarCollapsed && item.active"
                                :class="[
                                    'absolute right-1 top-1/2 h-2 w-2 -translate-y-1/2 rounded-full',
                                    isAdmin ? 'bg-red-500' : 'bg-emerald-500'
                                ]"
                            ></span>
                        </Link>
                    </li>
                </ul>

                <!-- Divider -->
                <div class="my-4 border-t border-slate-600"></div>

                <!-- Bottom Navigation -->
                <ul class="space-y-1">
                    <li v-for="item in bottomNavItems" :key="item.name" class="relative">
                        <Link
                            :href="item.href"
                            :class="[
                                'group flex items-center gap-3 rounded-xl px-3 py-3 font-medium transition-all duration-200',
                                item.active
                                    ? isAdmin 
                                        ? 'bg-red-500/20 text-red-300 shadow-sm' 
                                        : 'bg-emerald-500/20 text-emerald-300 shadow-sm'
                                    : 'text-gray-300 hover:bg-slate-700 hover:text-white',
                                sidebarCollapsed ? 'justify-center' : ''
                            ]"
                        >
                            <svg
                                :class="[
                                    'h-6 w-6 shrink-0 transition-transform duration-200 group-hover:scale-110',
                                    item.active
                                        ? isAdmin ? 'text-red-400' : 'text-emerald-400'
                                        : 'text-gray-400 group-hover:text-white'
                                ]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="item.icon"
                                />
                            </svg>
                            
                            <span v-if="!sidebarCollapsed" class="whitespace-nowrap">
                                {{ item.name }}
                            </span>
                        </Link>
                    </li>

                    <!-- Logout Button -->
                    <li>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            :class="[
                                'group flex w-full items-center gap-3 rounded-xl px-3 py-3 font-medium text-gray-300 transition-all duration-200 hover:bg-red-500/20 hover:text-red-300',
                                sidebarCollapsed ? 'justify-center' : ''
                            ]"
                        >
                            <svg
                                class="h-6 w-6 shrink-0 text-gray-400 transition-transform duration-200 group-hover:scale-110 group-hover:text-red-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
                                />
                            </svg>
                            
                            <span v-if="!sidebarCollapsed" class="whitespace-nowrap">
                                Log Out
                            </span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Collapse Toggle Button -->
            <div class="hidden lg:block border-t border-slate-600 p-3">
                <button
                    @click="toggleSidebar"
                    class="flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-300 transition-all duration-200 hover:bg-slate-700 hover:text-white"
                >
                    <svg
                        :class="[
                            'h-5 w-5 transition-transform duration-300',
                            sidebarCollapsed ? 'rotate-180' : ''
                        ]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                        />
                    </svg>
                    <span v-if="!sidebarCollapsed">Collapse</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div 
            :class="[
                'transition-all duration-300 ease-in-out',
                sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'
            ]"
        >
            <!-- Top Bar (Mobile + Header) -->
            <header 
                :class="[
                    'sticky top-0 z-30 flex h-16 items-center gap-4 border-b px-4 shadow-sm sm:px-6',
                    isAdmin ? 'bg-slate-800 border-red-500/30' : 'bg-slate-800 border-emerald-500/30'
                ]"
            >
                <!-- Mobile menu button -->
                <button
                    @click="toggleMobileMenu"
                    class="lg:hidden inline-flex items-center justify-center rounded-lg p-2 text-gray-300 transition-colors hover:bg-slate-700 hover:text-white"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Page Title -->
                <div :class="['flex-1', isAdmin ? 'text-red-400' : 'text-emerald-400']">
                    <slot name="header" />
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center gap-3">
                    <!-- Role Badge (visible on larger screens) -->
                    <span 
                        :class="[
                            'hidden sm:inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold shadow-sm',
                            isAdmin 
                                ? 'bg-gradient-to-r from-red-500 to-red-600 text-white' 
                                : 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white'
                        ]"
                    >
                        {{ roleLabel }}
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
nav::-webkit-scrollbar {
    width: 4px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>
