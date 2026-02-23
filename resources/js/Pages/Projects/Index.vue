<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Plus, LayoutGrid, MapPin, Calendar, Pencil, Trash2, ExternalLink } from 'lucide-vue-next';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ProjectDrawer from './Partials/ProjectDrawer.vue';
import Pagination from '@/Components/Pagination.vue';
import TableFilters from '@/Components/TableFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
    projects: Object,
    types: Array,
    statuses: Array,
    companies: Array,
    consortia: Array,
    departments: Array,
    filters: Object,
});

// --- 1. ESTADOS Y REFERENCIAS ---
const page = usePage();
const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || '10');
const isDrawerOpen = ref(false);
const selectedProject = ref(null);

// --- 2. FORMULARIO ---
const form = useForm({
    id: null,
    project_code: '',
    project_name: '',
    short_name: '',
    type_id: '',
    status_id: '',
    contractual_amount: 0,
    projected_amount: 0,
    start_date: '',
    end_date_contractual: '',
    department_id: '',
    province_id: '',
    district_id: '',
    address: '',
    consortium_id: null,
    company_id: null,
    cover_image: null,
});

// --- 3. WATCHERS (BÚSQUEDA Y MENSAJES) ---

// Sincronización con la URL (Filtros)
watch([search, perPage], debounce(() => {
    router.get(route('projects.index'), {
        search: search.value,
        perPage: perPage.value
    }, { preserveState: true, replace: true });
}, 400));

// Alertas de Flash Messages
watch(() => page.props.flash, (flash) => {
    if (flash?.message) {
        Swal.fire({
            icon: flash.type || 'success',
            title: flash.type === 'error' ? '¡Atención!' : '¡Operación exitosa!',
            text: flash.message,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: { popup: 'rounded-[2rem]' }
        }).then(() => {
            // Limpiamos el mensaje para evitar que se repita al navegar
            page.props.flash.message = null;
        });
    }
}, { deep: true }); // 'deep' es vital para observar cambios dentro del objeto flash

// --- 4. ACCIONES ---

const openDrawer = (project = null) => {
    selectedProject.value = project;

    if (project) {
        // Mapeo exhaustivo para edición (Asegúrate que coincida con tu Resource/API)
        form.id = project.id;
        form.project_code = project.project_code;
        form.project_name = project.project_name;
        form.short_name = project.short_name;
        form.type_id = project.type_id;
        form.status_id = project.status_id;
        form.contractual_amount = project.amounts?.contractual || 0;
        form.projected_amount = project.amounts?.projected || 0;
        form.start_date = project.dates?.start_raw;
        form.end_date_contractual = project.dates?.end_contractual_raw;

        // Ubicación
        form.department_id = project.location?.ids?.department || '';
        form.province_id = project.location?.ids?.province || '';
        form.district_id = project.location?.ids?.district || '';
        form.address = project.location?.address_detail || '';

        // Responsables
        form.consortium_id = project.consortium_id;
        form.company_id = project.company_id;

        form.cover_image = null;
    } else {
        form.reset();
        form.id = null;
    }

    isDrawerOpen.value = true;
};

const handleSubmit = () => {
    // Si hay id, es PUT (update), si no, es POST (store)
    const isEditing = !!form.id;
    const url = isEditing
        ? route('projects.update', form.id)
        : route('projects.store');

    form.transform((data) => ({
        ...data,
        // Inertia necesita _method para spoofing de PUT cuando hay archivos
        _method: isEditing ? 'PUT' : 'POST',
    })).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
            form.reset();
        },
        onError: (errors) => {
            console.error("Errores de validación:", errors);
        }
    });
};

/** Eliminación de proyecto */
const deleteItem = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#f43f5e',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('projects.destroy', id));
        }
    });
};

/** Formateador de moneda */
const formatCurrency = (amount) => {
    return Number(amount || 0).toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};
</script>

<template>

    <Head title="Portafolio de Proyectos" />

    <AuthenticatedLayout>
        <template #header>Proyectos</template>

        <template #header-actions>
            <PrimaryButton @click="openDrawer()"
                class="rounded-lg h-10 px-4 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md gap-2 border-none">
                <Plus :size="18" />
                <span class="font-bold text-xs uppercase tracking-widest">Nuevo Proyecto</span>
            </PrimaryButton>
        </template>

        <div class="space-y-4">
            <div
                class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div class="flex-1">
                    <TableFilters v-model="search" v-model:perPage="perPage" />
                </div>
                <div
                    class="hidden md:flex items-center gap-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest shrink-0 whitespace-nowrap">
                    <span>Total Proyectos: {{ projects.meta.total }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3 px-4">
                        <thead>
                            <tr class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.1em]">
                                <th class="px-8 py-3">Información del Proyecto</th>
                                <th class="px-6 py-3">Ubicación</th>
                                <th class="px-6 py-3 text-center">Estado / Fechas</th>
                                <th class="px-6 py-3 text-right">Presupuesto</th>
                                <th class="px-8 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="project in projects.data" :key="project.id"
                                class="group transition-all duration-300 ease-in-out">

                                <td colspan="5" class="p-0">
                                    <div
                                        class="relative flex items-center w-full bg-white border border-slate-100 rounded-2xl transition-all duration-300 
                                                group-hover:border-indigo-300 group-hover:bg-indigo-50/40 group-hover:-translate-y-0.5
                                                group-hover:shadow-[inset_4px_0_0_0_#6366f1,0_8px_20px_-6px_rgba(79,70,229,0.15)]">

                                        <div class="w-1/4 px-8 py-5 flex items-center gap-4">
                                            <div class="relative shrink-0">
                                                <div
                                                    class="h-12 w-12 rounded-xl border border-slate-100 bg-white flex items-center justify-center overflow-hidden shadow-sm transition-transform group-hover:scale-110 group-hover:border-indigo-100">
                                                    <img v-if="project.cover_url" :src="project.cover_url"
                                                        class="h-full w-full object-cover" />
                                                    <LayoutGrid v-else class="text-slate-300" :size="20" />
                                                </div>
                                            </div>
                                            <div class="leading-tight truncate">
                                                <div
                                                    class="font-black text-slate-800 uppercase text-xs tracking-tight truncate group-hover:text-indigo-900 transition-colors">
                                                    {{ project.short_name }}
                                                </div>
                                                <div
                                                    class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-[9px] font-bold text-slate-500 mt-2 uppercase transition-colors group-hover:bg-white group-hover:text-indigo-600">
                                                    ID: {{ project.project_code }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-1/5 px-6 py-5">
                                            <div class="flex flex-col gap-1">
                                                <div
                                                    class="flex items-center gap-1.5 text-xs font-bold text-slate-700 group-hover:text-slate-900 transition-colors">
                                                    <MapPin :size="12" class="text-indigo-500" />
                                                    <span class="truncate">
                                                        {{ project.location?.full_address || 'No definida' }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="text-[10px] text-slate-400 font-medium pl-4 truncate italic group-hover:text-indigo-400">
                                                    {{ project.location?.address_detail || '---' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-1/5 px-6 py-5 text-center flex flex-col items-center gap-2">
                                            <span :class="[
                                                'text-[9px] px-2.5 py-1 rounded-lg font-black uppercase tracking-wider border transition-all',
                                                project.dates?.is_expired
                                                    ? 'bg-rose-50 text-rose-600 border-rose-100 group-hover:bg-rose-100'
                                                    : 'bg-emerald-50 text-emerald-600 border-emerald-100 group-hover:bg-emerald-100'
                                            ]">
                                                {{ project.dates?.is_expired ? 'Finalizado' : 'En Ejecución' }}
                                            </span>
                                            <div
                                                class="flex items-center gap-1.5 text-[10px] text-slate-500 font-bold bg-white px-2 py-0.5 rounded-md border border-slate-50 group-hover:border-indigo-100 transition-colors">
                                                <Calendar :size="11"
                                                    class="text-slate-400 group-hover:text-indigo-400" />
                                                {{ project.dates?.start || 'S/F' }}
                                            </div>
                                        </div>

                                        <div class="w-1/6 px-6 py-5 text-right">
                                            <div class="flex flex-col items-end leading-none">
                                                <span
                                                    class="text-[8px] text-slate-400 font-black uppercase tracking-[0.2em] mb-1 group-hover:text-indigo-400">Total
                                                    Contratado</span>
                                                <div class="flex items-baseline gap-1">
                                                    <span
                                                        class="text-[10px] font-bold text-indigo-500 uppercase">S/</span>
                                                    <span
                                                        class="text-sm font-black text-slate-900 tracking-tighter group-hover:text-indigo-950 transition-colors">
                                                        {{ formatCurrency(project.amounts?.contractual) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-1/5 px-8 py-5 flex justify-end gap-2">
                                            <Link :href="route('projects.show', project.id)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-emerald-600 hover:border-emerald-200 hover:shadow-sm transition-all duration-200 active:scale-95 shadow-sm"
                                                title="Explorar">
                                                <ExternalLink :size="16" />
                                            </Link>
                                            <button @click="openDrawer(project)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm transition-all duration-200 active:scale-95 shadow-sm"
                                                title="Editar">
                                                <Pencil :size="16" />
                                            </button>
                                            <button @click="deleteItem(project.id)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-red-600 hover:border-red-200 hover:shadow-sm transition-all duration-200 active:scale-95 shadow-sm"
                                                title="Eliminar">
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState v-if="projects.data.length === 0" :search="search" />

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="projects.meta.links" />
                </div>
            </div>
        </div>

        <ProjectDrawer :show="isDrawerOpen" :form="form" :project="selectedProject" :types="types" :statuses="statuses"
            :companies="companies" :consortia="consortia" :departments="departments" @close="isDrawerOpen = false"
            @submit="handleSubmit" />
    </AuthenticatedLayout>
</template>