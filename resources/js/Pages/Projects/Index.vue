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
watch(() => page.props.flash?.message, (message) => {
    if (message) {
        Swal.fire({
            icon: page.props.flash.type || 'success',
            title: '¡Operación exitosa!',
            text: message,
            timer: 2000,
            showConfirmButton: false,
            customClass: { popup: 'rounded-[2rem]' }
        });
    }
});

// --- 4. ACCIONES ---

/** Abre el Drawer para crear o editar */
const openDrawer = (project = null) => {
    selectedProject.value = project;

    if (project) {
        // Llenado de datos para edición
        form.id = project.id;
        form.project_code = project.project_code;
        form.project_name = project.project_name;
        form.short_name = project.short_name;
        form.type_id = project.type_id;
        form.status_id = project.status_id;
        form.contractual_amount = project.amounts?.contractual || 0;
        form.start_date = project.dates?.start_raw;
        // ... cargar los demás campos según tu estructura de API
    } else {
        form.reset();
        form.id = null;
    }

    isDrawerOpen.value = true;
};

/** Envío del formulario */
const handleSubmit = () => {
    const url = selectedProject.value
        ? route('projects.update', selectedProject.value.id)
        : route('projects.store');

    form.transform((data) => ({
        ...data,
        _method: selectedProject.value ? 'PUT' : 'POST',
    })).post(url, {
        forceFormData: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
            form.reset();
        },
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
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="font-black text-3xl text-slate-900 tracking-tight">Proyectos</h2>
                    <p class="text-sm text-slate-500 font-medium">Visualización y control de cartera de obras.</p>
                </div>
                <PrimaryButton @click="openDrawer()"
                    class="rounded-2xl h-12 px-6 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 gap-2">
                    <Plus :size="20" />
                    <span class="font-bold tracking-tight">Nuevo Proyecto</span>
                </PrimaryButton>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <TableFilters v-model="search" v-model:perPage="perPage" />
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-200 text-xs font-bold text-gray-500 uppercase">
                                <tr>
                                    <th class="px-6 py-4">Información del Proyecto</th>
                                    <th class="px-6 py-4">Ubicación</th>
                                    <th class="px-6 text-center">Estado / Fechas</th>
                                    <th class="px-6 py-4">Presupuesto</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="project in projects.data" :key="project.id"
                                    class="group transition-all duration-200 hover:bg-indigo-50/40 hover:shadow-[inset_4px_0_0_0_#4f46e5]">

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 shadow-sm group-hover:border-indigo-200">
                                                <img v-if="project.cover_url" :src="project.cover_url"
                                                    class="h-full w-full object-cover" />
                                                <LayoutGrid v-else class="text-gray-300" :size="20" />
                                            </div>
                                            <div class="max-w-xs">
                                                <div
                                                    class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate uppercase">
                                                    {{ project.short_name }}
                                                </div>
                                                <div class="text-xs text-gray-400 font-medium tracking-tight">
                                                    CÓDIGO: {{ project.project_code }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center gap-1.5 text-sm font-semibold tracking-tight"
                                                :class="project.location?.full_address ? 'text-gray-700' : 'text-gray-400 italic'">
                                                <MapPin :size="14" class="shrink-0 text-indigo-500/70" />
                                                <span class="truncate max-w-[200px]">
                                                    {{ project.location?.full_address || 'Sin ubicación' }}
                                                </span>
                                            </div>
                                            <div class="pl-5 text-xs font-medium"
                                                :class="project.location?.address_detail ? 'text-gray-500' : 'text-gray-400 italic'">
                                                {{ project.location?.address_detail || 'Sin detalles' }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <span :class="[
                                                'text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-tighter mb-1.5 shadow-sm border',
                                                project.dates?.is_expired ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-green-50 text-green-600 border-green-100'
                                            ]">
                                                {{ project.dates?.is_expired ? 'Finalizado' : 'En Ejecución' }}
                                            </span>
                                            <div class="flex items-center gap-1 text-[10px] text-slate-500 font-bold">
                                                <Calendar :size="10" /> {{ project.dates?.start || 'S/F' }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-[10px] text-slate-400 font-black uppercase tracking-widest">PEN</span>
                                            <span class="text-sm font-black text-slate-900 tracking-tight">
                                                {{ formatCurrency(project.amounts?.contractual) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center gap-1">
                                            <Link :href="route('projects.show', project.id)"
                                                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                                title="Ver Detalles del Proyecto">
                                            <ExternalLink :size="18" />
                                            </Link>
                                            <button @click="openDrawer(project)"
                                                class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                                title="Editar">
                                                <Pencil :size="18" />
                                            </button>
                                            <button @click="deleteItem(project.id)"
                                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                title="Eliminar">
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <EmptyState v-if="projects.data.length === 0" :search="search" />

                    <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                        <!-- <Pagination :links="projects.links" /> -->
                        <Pagination :links="projects.meta.links" />
                    </div>
                </div>
            </div>
        </div>

        <ProjectDrawer :show="isDrawerOpen" :form="form" :project="selectedProject" :types="types" :statuses="statuses"
            :companies="companies" :consortia="consortia" :departments="departments" @close="isDrawerOpen = false"
            @submit="handleSubmit" />
    </AuthenticatedLayout>
</template>