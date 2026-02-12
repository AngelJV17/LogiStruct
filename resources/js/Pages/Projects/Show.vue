<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ChevronLeft, MapPin, Briefcase,
    LayoutGrid, ClipboardList, Building2,
    CircleDollarSign, CalendarDays, FileText, Info
} from 'lucide-vue-next';

const props = defineProps({ project: Object });

// Estado para controlar la pestaña activa
const activeTab = ref('info');

const tabs = [
    { id: 'info', name: 'Información', icon: Info },
    { id: 'location', name: 'Ubicación', icon: MapPin },
    { id: 'financial', name: 'Financiero', icon: CircleDollarSign },
    { id: 'schedule', name: 'Cronograma', icon: CalendarDays },
    { id: 'docs', name: 'Documentos', icon: FileText },
];

// Función para formatear moneda
const currency = (value) => {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(value || 0);
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-slate-50 min-h-screen text-slate-900 font-sans antialiased pb-20">

            <div class="relative h-[350px] w-full bg-slate-200">
                <img :src="project.cover_image ? `/storage/${project.cover_image}` : 'https://images.unsplash.com/photo-1541888946141-000021cd1b0f?q=80&w=2940&auto=format&fit=crop'"
                    class="w-full h-full object-cover object-center" alt="Portada del proyecto" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-50/90 via-transparent to-transparent"></div>
                <div class="absolute top-6 left-10 z-10">
                    <Link :href="route('projects.index')"
                        class="flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm border border-slate-200 rounded-xl text-slate-700 hover:bg-white transition-all shadow-sm font-bold text-xs uppercase tracking-wider">
                        <ChevronLeft :size="16" /> Volver
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-10">
                <div
                    class="relative -mt-32 z-20 bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/40 border border-slate-100 overflow-hidden">

                    <div class="p-10 pb-6">
                        <div class="flex items-center gap-3 mb-6">
                            <span
                                class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-md">
                                {{ project.type?.name || 'Infraestructura' }}
                            </span>
                            <span
                                class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold border border-slate-200 rounded-md uppercase">
                                ID: {{ project.project_code }}
                            </span>
                            <span
                                class="ml-auto flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-md border border-emerald-100">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                {{ project.status?.name }}
                            </span>
                        </div>

                        <div class="max-w-4xl space-y-3">
                            <h1 class="text-2xl font-black text-slate-900 leading-tight tracking-tight uppercase">
                                {{ project.project_name }}
                            </h1>
                            <p class="text-slate-500 font-medium italic border-l-4 border-indigo-500 pl-4">
                                {{ project.short_name }}
                            </p>
                        </div>
                    </div>

                    <div class="px-10 border-b border-slate-100 bg-slate-50/50">
                        <nav class="flex gap-8 overflow-x-auto no-scrollbar">
                            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                                activeTab === tab.id
                                    ? 'border-indigo-600 text-indigo-600'
                                    : 'border-transparent text-slate-400 hover:text-slate-600'
                            ]"
                                class="flex items-center gap-2 py-4 border-b-2 transition-all duration-300 whitespace-nowrap">
                                <component :is="tab.icon" :size="18"
                                    :class="activeTab === tab.id ? 'text-indigo-600' : 'text-slate-400'" />
                                <span class="text-xs font-black uppercase tracking-widest">{{ tab.name }}</span>
                            </button>
                        </nav>
                    </div>

                    <div class="p-10 transition-all duration-500">
                        <div v-if="activeTab === 'info'"
                            class="grid grid-cols-1 md:grid-cols-2 gap-10 animate-in fade-in slide-in-from-bottom-2">
                            <div class="space-y-6">
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Responsables
                                    del Proyecto</h3>

                                <div v-if="project.consortium" class="space-y-4">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="p-2 bg-indigo-600 text-white rounded-lg">
                                            <Briefcase :size="18" />
                                        </div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase">{{
                                            project.consortium.name }}</h4>
                                    </div>

                                    <div class="grid gap-3 pl-4 border-l-2 border-slate-100">
                                        <div v-for="item in project.consortium.companies" :key="item.id"
                                            class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl shadow-sm hover:border-indigo-200 transition-colors">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 overflow-hidden shrink-0">
                                                    <img v-if="item.url_logo" :src="`/storage/${item.url_logo}`"
                                                        class="w-full h-full object-cover" />
                                                    <Building2 v-else class="text-slate-300" :size="20" />
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-black text-slate-700 uppercase leading-none mb-1">
                                                        {{ item.name }}</p>
                                                    <p class="text-[10px] font-medium text-slate-400">RUC: {{ item.ruc
                                                        || 'N/A' }}</p>
                                                </div>
                                            </div>
                                            <div v-if="item.pivot?.participation_percentage" class="text-right">
                                                <span
                                                    class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md border border-indigo-100">
                                                    {{ item.pivot.participation_percentage }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="project.company" class="space-y-4">
                                    <div
                                        class="flex items-center gap-4 p-5 bg-white border border-slate-100 rounded-[2rem] shadow-sm">
                                        <div
                                            class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center border border-slate-100 overflow-hidden shrink-0">
                                            <img v-if="project.company.logo" :src="`/storage/${project.company.logo}`"
                                                class="w-full h-full object-cover" />
                                            <Building2 v-else class="text-slate-300" :size="32" />
                                        </div>
                                        <div>
                                            <h4
                                                class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">
                                                Empresa Ejecutora</h4>
                                            <p class="text-sm font-black text-slate-800 uppercase leading-tight">{{
                                                project.company.name }}</p>
                                            <p class="text-xs font-medium text-slate-400 mt-1">RUC: {{
                                                project.company.ruc }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-else
                                    class="p-8 border-2 border-dashed border-slate-100 rounded-[2rem] text-center flex flex-col items-center gap-2">
                                    <Building2 class="text-slate-200" :size="40" />
                                    <p class="text-xs font-bold text-slate-300 uppercase italic tracking-widest">Sin
                                        responsable asignado</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'location'" class="space-y-6 animate-in fade-in slide-in-from-left-2">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600">
                                    <MapPin :size="24" />
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        Localización Geográfica</h4>
                                    <p class="text-lg font-bold text-slate-700">{{ project.district?.name }}, {{
                                        project.province?.name }}, {{ project.department?.name }}</p>
                                    <p
                                        class="text-slate-500 mt-2 bg-slate-100 px-4 py-2 rounded-lg inline-block text-sm uppercase">
                                        {{ project.address || 'Sin dirección específica' }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'financial'"
                            class="grid grid-cols-2 gap-6 animate-in fade-in zoom-in-95">
                            <div class="p-6 border border-slate-100 rounded-[2rem] bg-slate-50/50">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Monto
                                    Contractual</h4>
                                <p class="text-3xl font-black text-slate-900">{{ currency(project.contractual_amount) }}
                                </p>
                            </div>
                            <div class="p-6 border border-indigo-100 rounded-[2rem] bg-indigo-50/30">
                                <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Monto
                                    Proyectado</h4>
                                <p class="text-3xl font-black text-indigo-600">{{ currency(project.project_amount) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="activeTab === 'schedule'"
                            class="grid grid-cols-3 gap-6 animate-in fade-in slide-in-from-right-2">
                            <div class="text-center p-6 bg-white border border-slate-100 rounded-3xl">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Fecha
                                    Inicio</span>
                                <span class="text-sm font-black text-slate-700 uppercase">{{ project.start_date || 'N/A'
                                }}</span>
                            </div>
                            <div class="text-center p-6 bg-white border border-slate-100 rounded-3xl">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Fin
                                    Contractual</span>
                                <span class="text-sm font-black text-red-500 uppercase">{{ project.end_date_contractual
                                    || 'N/A' }}</span>
                            </div>
                            <div class="text-center p-6 bg-indigo-600 text-white rounded-3xl">
                                <span class="text-[10px] font-bold text-indigo-200 uppercase block mb-2">Fin Real
                                    Estimado</span>
                                <span class="text-sm font-black uppercase">{{ project.end_date_real || 'N/A' }}</span>
                            </div>
                        </div>

                        <div v-if="activeTab === 'docs'" class="text-center py-10 animate-in fade-in">
                            <div
                                class="mx-auto w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4">
                                <FileText :size="32" />
                            </div>
                            <h3 class="text-slate-800 font-bold">Módulo de Documentos</h3>
                            <p class="text-slate-500 text-sm">Próximamente: Gestión de expedientes y resoluciones.</p>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>