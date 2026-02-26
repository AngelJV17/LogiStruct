<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
// Importación de iconos Lucide
import {
    LayoutGrid,
    HardHat,
    Users,
    Building2,
    Network,
    Settings2,
    ChevronLeft,
    Menu,
    X
} from 'lucide-vue-next';

const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);

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
                    <X :size="24" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-7 custom-scrollbar mb-16">
                <div class="space-y-1">
                    <p v-if="!isCollapsed || isMobileMenuOpen"
                        class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Menú</p>

                    <Link :href="route('dashboard')" :class="navLinkClasses(route().current('dashboard'))"
                        @click="closeMobileMenu">
                        <LayoutGrid :size="20" />
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Dashboard</span>
                    </Link>

                    <Link :href="route('projects.index')" :class="navLinkClasses(route().current('projects.*'))"
                        @click="closeMobileMenu">
                        <HardHat :size="20" />
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Proyectos</span>
                    </Link>

                    <Link :href="route('workers.index')" :class="navLinkClasses(route().current('workers.*'))"
                        @click="closeMobileMenu">
                        <Users :size="20" />
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Trabajadores</span>
                    </Link>
                </div>

                <div class="space-y-1">
                    <p v-if="!isCollapsed || isMobileMenuOpen"
                        class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-3">Entidades</p>

                    <Link :href="route('companies.index')" :class="navLinkClasses(route().current('companies.*'))"
                        @click="closeMobileMenu">
                        <Building2 :size="20" />
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Empresas</span>
                    </Link>

                    <Link :href="route('consortia.index')" :class="navLinkClasses(route().current('consortia.*'))"
                        @click="closeMobileMenu">
                        <Network :size="20" />
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
                        <Settings2 :size="20" />
                        <span v-if="!isCollapsed || isMobileMenuOpen"
                            class="text-sm font-semibold whitespace-nowrap">Parámetros</span>
                    </Link>
                </div>
            </nav>

            <div
                class="hidden lg:flex h-16 border-t border-slate-100 bg-slate-50/50 items-center justify-center shrink-0 absolute bottom-0 w-full left-0">
                <button @click="isCollapsed = !isCollapsed"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 transition-all duration-300">
                    <ChevronLeft :class="{ 'rotate-180': isCollapsed }"
                        class="w-4 h-4 transition-transform duration-500" />
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-8 shrink-0 sticky top-0 z-20">
                <div class="flex items-center gap-4">
                    <button @click="isMobileMenuOpen = true"
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                        <Menu :size="24" />
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