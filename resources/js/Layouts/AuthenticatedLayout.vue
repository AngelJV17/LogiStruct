<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false); // Nuevo estado para móviles

const navLinkClasses = (active) => [
    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group relative',
    active
        ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100'
        : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'
];

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};
</script>

<template>
    <div class="flex h-screen w-full bg-[#F8FAFC] overflow-hidden antialiased text-slate-900">

        <div v-if="isMobileMenuOpen" @click="closeMobileMenu"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300"></div>

        <aside :class="[
            isCollapsed ? 'lg:w-20' : 'lg:w-64',
            isMobileMenuOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0'
        ]"
            class="fixed inset-y-0 left-0 lg:static flex flex-col bg-white border-r border-slate-200 transition-all duration-300 ease-in-out shrink-0 z-50 overflow-hidden">
            <div class="h-16 flex items-center px-6 border-b border-slate-100 bg-white shrink-0 justify-between">
                <Link :href="route('dashboard')" class="flex items-center gap-3" @click="closeMobileMenu">
                    <ApplicationLogo class="h-8 w-8 shrink-0 fill-current text-indigo-600" />
                    <span v-if="!isCollapsed || isMobileMenuOpen"
                        class="font-black text-slate-800 tracking-tight text-xl">SIS-CORP</span>
                </Link>
                <button @click="closeMobileMenu" class="lg:hidden p-2 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-7 custom-scrollbar mb-16">
                <div class="space-y-1">
                    <p v-if="!isCollapsed || isMobileMenuOpen"
                        class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Menú</p>

                    <Link :href="route('dashboard')" :class="navLinkClasses(route().current('dashboard'))"
                        @click="closeMobileMenu">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Dashboard</span>
                    </Link>

                    <Link :href="route('projects.index')" :class="navLinkClasses(route().current('projects.*'))"
                        @click="closeMobileMenu">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Proyectos</span>
                    </Link>
                </div>

                <div class="space-y-1">
                    <p v-if="!isCollapsed || isMobileMenuOpen"
                        class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Entidades</p>

                    <Link :href="route('companies.index')" :class="navLinkClasses(route().current('companies.*'))"
                        @click="closeMobileMenu">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Empresas</span>
                    </Link>

                    <Link :href="route('consortia.index')" :class="navLinkClasses(route().current('consortia.*'))"
                        @click="closeMobileMenu">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Consorcios</span>
                    </Link>
                </div>

                <div class="space-y-1">
                    <p v-if="!isCollapsed || isMobileMenuOpen"
                        class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3 mt-4">
                        Configuración</p>

                    <Link :href="route('parameters.index')" :class="navLinkClasses(route().current('parameters.*'))"
                        @click="closeMobileMenu">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Parámetros</span>
                    </Link>
                </div>
            </nav>

            <div
                class="hidden lg:flex h-16 border-t border-slate-100 bg-slate-50/50 items-center justify-center shrink-0 absolute bottom-0 w-full left-0">
                <button @click="isCollapsed = !isCollapsed"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-all duration-300">
                    <svg :class="{ 'rotate-180': isCollapsed }" class="w-4 h-4 transition-transform duration-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-8 shrink-0 sticky top-0 z-20">

                <div class="flex items-center gap-4">
                    <button @click="isMobileMenuOpen = true"
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex flex-col leading-tight">
                        <h1
                            class="text-slate-800 font-black text-xs sm:text-sm uppercase tracking-widest truncate max-w-[150px] sm:max-w-none">
                            <slot name="header" />
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:gap-6">
                    <div class="hidden sm:block">
                        <slot name="header-actions" />
                    </div>

                    <div class="hidden sm:block h-6 w-[1px] bg-slate-200"></div>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                class="flex items-center gap-3 group px-2 py-1 rounded-lg hover:bg-slate-50 transition-colors">
                                <div
                                    class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-md shadow-indigo-100 uppercase">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <div class="hidden md:block text-left leading-none">
                                    <p class="text-sm font-bold text-slate-700">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Admin</p>
                                </div>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Mi Perfil </DropdownLink>
                            <div class="border-t border-slate-100"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button"
                                class="text-red-600 font-medium">
                                Cerrar Sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto scroll-smooth bg-[#F1F5F9] p-4 lg:p-0">
                <slot />
            </main>
        </div>
    </div>
</template>