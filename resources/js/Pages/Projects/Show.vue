<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ChevronLeft, MapPin, Briefcase,
    Building2, CircleDollarSign, CalendarDays,
    FileText, Info, Hash, CheckCircle2,
    ArrowRight, Globe, Layers
} from 'lucide-vue-next';

const props = defineProps({ project: Object });

// Estado para controlar la pestaña activa
const activeTab = ref('info');

const tabs = [
    { id: 'info', name: 'General', icon: Info },
    { id: 'location', name: 'Ubicación', icon: MapPin },
    { id: 'financial', name: 'Financiero', icon: CircleDollarSign },
    { id: 'schedule', name: 'Plazos', icon: CalendarDays },
    { id: 'docs', name: 'Expediente', icon: FileText },
];

// Formateador de Moneda
const currency = (value) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2
    }).format(value || 0);
};

// Formateador de Fechas (opcional, si las fechas vienen como string ISO)
const formatDate = (dateString) => {
    if (!dateString) return 'No definido';
    return new Date(dateString).toLocaleDateString('es-PE', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
</script>

<template>

    <Head :title="`Proyecto: ${project.short_name}`" />

    <AuthenticatedLayout>
        <div class="bg-[#f8fafc] min-h-screen pb-20 font-sans antialiased">

            <div class="relative h-[300px] md:h-[400px] w-full bg-slate-900 overflow-hidden">
                <img :src="project.cover_image ? `/storage/${project.cover_image}` : 'https://images.unsplash.com/photo-1503387762-592dee58c460?q=80&w=2600&auto=format&fit=crop'"
                    class="w-full h-full object-cover opacity-60 scale-105" alt="Cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-[#f8fafc] via-transparent to-black/20"></div>

                <div class="absolute top-6 left-6 md:top-8 md:left-10 z-30">
                    <Link :href="route('projects.index')"
                        class="group flex items-center gap-2 px-4 py-2 md:px-5 md:py-2.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl md:rounded-2xl text-white hover:bg-white hover:text-slate-900 transition-all duration-300 shadow-xl font-bold text-[10px] md:text-xs uppercase tracking-widest">
                        <ChevronLeft :size="18" class="group-hover:-translate-x-1 transition-transform" />
                        <span class="hidden xs:inline">Panel de Control</span>
                        <span class="xs:hidden">Volver</span>
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-10">
                <div
                    class="relative -mt-32 md:-mt-48 z-20 bg-white rounded-[2rem] md:rounded-[3rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.15)] border border-slate-200/60 overflow-hidden">

                    <div class="p-6 md:p-12 pb-6 md:pb-8">
                        <div class="flex flex-wrap items-center gap-3 mb-6 md:mb-8">
                            <span
                                class="px-3 py-1 md:px-4 md:py-1.5 bg-indigo-600 text-white text-[9px] md:text-[10px] font-black uppercase tracking-[0.2em] rounded-full shadow-lg shadow-indigo-200">
                                {{ project.type?.name || 'Obra Pública' }}
                            </span>
                            <span
                                class="px-3 py-1 md:px-4 md:py-1.5 bg-slate-100 text-slate-500 text-[9px] md:text-[10px] font-bold border border-slate-200 rounded-full flex items-center gap-2">
                                <Hash :size="12" /> {{ project.project_code }}
                            </span>
                            <div class="w-full sm:w-auto sm:ml-auto mt-2 sm:mt-0">
                                <span
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 text-[10px] md:text-[11px] font-black rounded-full border border-emerald-100 uppercase tracking-wider">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    {{ project.status?.name || 'Activo' }}
                                </span>
                            </div>
                        </div>

                        <div class="max-w-5xl">
                            <h1
                                class="text-2xl md:text-3xl lg:text-4xl font-black text-slate-900 leading-[1.1] tracking-tight mb-4 uppercase">
                                {{ project.project_name }}
                            </h1>
                            <div class="flex items-center gap-3 text-indigo-600 font-semibold tracking-wide">
                                <Layers :size="20" class="shrink-0" />
                                <span class="text-base md:text-lg opacity-80">{{ project.short_name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 md:px-10 border-b border-slate-100 bg-slate-50/50">
                        <nav class="flex gap-6 md:gap-10 overflow-x-auto no-scrollbar whitespace-nowrap">
                            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                class="flex items-center gap-2.5 py-4 md:py-5 border-b-2 transition-all duration-300 group"
                                :class="[activeTab === tab.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600']">
                                <component :is="tab.icon" :size="20"
                                    :class="activeTab === tab.id ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-500'" />
                                <span class="text-[10px] md:text-[11px] font-black uppercase tracking-widest">{{
                                    tab.name }}</span>
                            </button>
                        </nav>
                    </div>

                    <div class="p-6 md:p-12 min-h-[400px]">

                        <div v-if="activeTab === 'info'"
                            class="grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div class="lg:col-span-7 space-y-8">
                                <div>
                                    <h3
                                        class="flex items-center gap-2 text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6">
                                        <Briefcase :size="14" /> Responsable de Ejecución
                                    </h3>

                                    <div v-if="project.consortium"
                                        class="bg-white border border-slate-200 rounded-[2rem] md:rounded-[2.5rem] p-1.5 md:p-2 shadow-sm">
                                        <div
                                            class="flex flex-col sm:flex-row items-center gap-5 p-6 bg-slate-50/80 rounded-[1.5rem] md:rounded-[2rem] border border-white mb-2 text-center sm:text-left">
                                            <div
                                                class="w-16 h-16 shrink-0 bg-white border border-slate-200 rounded-2xl p-2 flex items-center justify-center shadow-sm">
                                                <img v-if="project.consortium.url_logo"
                                                    :src="`/storage/${project.consortium.url_logo}`"
                                                    class="max-h-full max-w-full object-contain" />
                                                <Briefcase v-else class="text-indigo-500" :size="30" />
                                            </div>
                                            <div>
                                                <h4
                                                    class="text-lg md:text-xl font-black text-slate-900 leading-tight uppercase">
                                                    {{ project.consortium.name }}</h4>
                                                <p
                                                    class="text-[10px] md:text-xs font-bold text-indigo-600 mt-1 uppercase tracking-widest">
                                                    Consorcio Ejecutor</p>
                                            </div>
                                        </div>

                                        <div class="grid gap-2 p-1 md:p-2">
                                            <div v-for="item in project.consortium.companies" :key="item.id"
                                                class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl hover:border-indigo-200 transition-all group gap-4">
                                                <div class="flex items-center gap-4">
                                                    <div
                                                        class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center border border-slate-100 shrink-0 overflow-hidden">
                                                        <img v-if="item.url_logo" :src="`/storage/${item.url_logo}`"
                                                            class="w-full h-full object-cover" />
                                                        <Building2 v-else class="text-slate-400" :size="20" />
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-[11px] font-black text-slate-800 uppercase leading-none mb-1">
                                                            {{ item.name }}</p>
                                                        <p class="text-[10px] font-medium text-slate-400">RUC: {{
                                                            item.ruc || 'Sin registro' }}</p>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 self-start sm:self-center">
                                                    {{ item.pivot?.participation_percentage }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else-if="project.company"
                                        class="flex flex-col sm:flex-row items-center gap-6 p-8 bg-white border border-slate-200 rounded-[2rem] md:rounded-[2.5rem] shadow-sm text-center sm:text-left">
                                        <div
                                            class="w-20 h-20 rounded-3xl bg-slate-50 flex items-center justify-center border border-slate-100 shadow-inner p-3">
                                            <img v-if="project.company.url_logo"
                                                :src="`/storage/${project.company.url_logo}`"
                                                class="max-h-full max-w-full object-contain" />
                                            <Building2 v-else class="text-slate-300" :size="40" />
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-1">
                                                Empresa Contratista</p>
                                            <h4
                                                class="text-xl md:text-2xl font-black text-slate-900 uppercase leading-tight">
                                                {{ project.company.name }}</h4>
                                            <p class="text-sm font-bold text-slate-400 mt-1">RUC: {{ project.company.ruc
                                                }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-5">
                                <div
                                    class="bg-indigo-900 rounded-[2rem] md:rounded-[3rem] p-8 md:p-10 text-white relative overflow-hidden shadow-2xl shadow-indigo-200">
                                    <div class="relative z-10 space-y-6">
                                        <h3 class="text-indigo-300 text-[10px] font-black uppercase tracking-[0.3em]">
                                            Resumen Ejecutivo</h3>
                                        <div class="space-y-4">
                                            <div class="flex justify-between items-end border-b border-indigo-800 pb-4">
                                                <span
                                                    class="text-[10px] md:text-xs font-bold text-indigo-300 uppercase">Monto
                                                    Contractual</span>
                                                <span class="text-lg md:text-xl font-black">{{
                                                    currency(project.contractual_amount) }}</span>
                                            </div>
                                            <div class="flex justify-between items-end border-b border-indigo-800 pb-4">
                                                <span
                                                    class="text-[10px] md:text-xs font-bold text-indigo-300 uppercase">Plazo
                                                    Ejecución</span>
                                                <span class="text-lg md:text-xl font-black">365 Días</span>
                                            </div>
                                        </div>
                                        <p class="text-indigo-100/70 text-sm leading-relaxed italic">"Este proyecto
                                            forma parte del plan estratégico..."</p>
                                    </div>
                                    <Building2 :size="200"
                                        class="absolute -right-10 -bottom-10 opacity-10 hidden md:block" />
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'location'"
                            class="max-w-4xl animate-in fade-in slide-in-from-left-4 duration-500">
                            <div class="flex flex-col sm:flex-row items-start gap-8">
                                <div
                                    class="w-20 h-20 bg-indigo-600 rounded-3xl flex items-center justify-center text-white shadow-xl shrink-0">
                                    <MapPin :size="40" />
                                </div>
                                <div class="space-y-6">
                                    <div>
                                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">
                                            Referencia Geográfica</h4>
                                        <p class="text-2xl md:text-3xl font-black text-slate-800 leading-tight">
                                            {{ project.district?.name }}, {{ project.province?.name }}
                                        </p>
                                        <p class="text-xl font-bold text-indigo-600 opacity-80">{{
                                            project.department?.name }}</p>
                                    </div>
                                    <div
                                        class="inline-flex items-center gap-3 px-6 py-4 bg-slate-100 rounded-2xl border border-slate-200">
                                        <Globe :size="20" class="text-slate-400 shrink-0" />
                                        <span
                                            class="text-sm font-bold text-slate-600 uppercase tracking-wide break-words">
                                            {{ project.address || 'Dirección no especificada' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'financial'"
                            class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in zoom-in-95 duration-500">
                            <div
                                class="group p-8 md:p-10 border-2 border-slate-100 rounded-[2rem] md:rounded-[3rem] bg-white hover:border-indigo-600 transition-all duration-500">
                                <div class="flex justify-between items-start mb-8">
                                    <div
                                        class="p-4 bg-slate-100 rounded-2xl text-slate-500 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                        <CircleDollarSign :size="32" />
                                    </div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Base
                                        Inicial</span>
                                </div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Presupuesto
                                    Contractual</h4>
                                <p class="text-3xl md:text-4xl font-black text-slate-900">{{
                                    currency(project.contractual_amount) }}</p>
                            </div>
                            <div
                                class="group p-8 md:p-10 border-2 border-indigo-100 rounded-[2rem] md:rounded-[3rem] bg-indigo-50/30 hover:bg-indigo-50 transition-all duration-500">
                                <div class="flex justify-between items-start mb-8">
                                    <div class="p-4 bg-indigo-600 rounded-2xl text-white">
                                        <CheckCircle2 :size="32" />
                                    </div>
                                    <span
                                        class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Valorizado</span>
                                </div>
                                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-2">
                                    Presupuesto Proyectado</h4>
                                <p class="text-3xl md:text-4xl font-black text-indigo-600">{{
                                    currency(project.project_amount) }}</p>
                            </div>
                        </div>

                        <div v-if="activeTab === 'schedule'"
                            class="animate-in fade-in slide-in-from-right-4 duration-500">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                                <div
                                    class="p-8 bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] shadow-sm text-center relative overflow-hidden group">
                                    <span
                                        class="relative z-10 text-[10px] font-bold text-slate-400 uppercase block mb-4 tracking-tighter">Apertura
                                        de Obra</span>
                                    <span class="relative z-10 text-lg font-black text-slate-800 uppercase">{{
                                        formatDate(project.start_date) }}</span>
                                    <CalendarDays :size="80"
                                        class="absolute -right-4 -bottom-4 text-slate-50 opacity-50" />
                                </div>
                                <div
                                    class="p-8 bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] shadow-sm text-center relative overflow-hidden group">
                                    <span
                                        class="relative z-10 text-[10px] font-bold text-slate-400 uppercase block mb-4 tracking-tighter">Término
                                        Contractual</span>
                                    <span class="relative z-10 text-lg font-black text-red-500 uppercase">{{
                                        formatDate(project.end_date_contractual) }}</span>
                                    <CalendarDays :size="80"
                                        class="absolute -right-4 -bottom-4 text-slate-50 opacity-50" />
                                </div>
                                <div
                                    class="p-8 bg-indigo-600 rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-indigo-100 text-center relative overflow-hidden">
                                    <span
                                        class="relative z-10 text-[10px] font-bold text-indigo-200 uppercase block mb-4 tracking-tighter">Entrega
                                        Estimada</span>
                                    <span class="relative z-10 text-lg font-black text-white uppercase">{{
                                        formatDate(project.end_date_real) }}</span>
                                    <CalendarDays :size="80" class="absolute -right-4 -bottom-4 text-indigo-500/50" />
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'docs'"
                            class="text-center py-12 md:py-20 animate-in fade-in duration-700">
                            <div
                                class="mx-auto w-20 h-20 md:w-24 md:h-24 bg-slate-50 rounded-[2rem] border border-slate-100 flex items-center justify-center text-slate-300 mb-6 shadow-inner">
                                <FileText :size="48" />
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-slate-800 uppercase tracking-tight">
                                Expediente Digital</h3>
                            <p class="text-slate-500 mt-2 max-w-sm mx-auto font-medium px-4">El módulo de gestión
                                documental se encuentra en proceso de sincronización.</p>
                            <button
                                class="mt-8 px-8 py-3 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-full hover:bg-indigo-600 transition-all">Solicitar
                                Acceso</button>
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

/* Animaciones suaves */
.animate-in {
    animation-duration: 0.4s;
}
</style>