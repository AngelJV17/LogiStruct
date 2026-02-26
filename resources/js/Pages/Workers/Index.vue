<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage, Link } from '@inertiajs/vue3';
import { Plus, User, Briefcase, Pencil, Trash2, Phone, ExternalLink } from 'lucide-vue-next';
import Swal from 'sweetalert2';

// Layouts & Components
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import WorkerDrawer from './Partials/WorkerDrawer.vue';
import Pagination from '@/Components/Pagination.vue';
import TableFilters from '@/Components/TableFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    workers: Object,
    filters: Object,
    params: Object,
    positions: Array,
    banks: Array,
    pensionSystems: Array, // Actualizado de pension_systems
    companies: Array,
    projects: Array
});

const page = usePage();

// --- 1. ESTADOS REACTIVOS ---
const isDrawerOpen = ref(false);
const editMode = ref(false);
const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || '10');

// --- 2. LÓGICA DE NEGOCIO (Mapeo de Datos) ---

/**
 * Normaliza los datos del trabajador para el formulario.
 * Resuelve el problema de los selects en blanco convirtiendo null a ''.
 */
const mapWorkerToForm = (worker = null) => ({
    uuid: worker?.uuid ?? null,
    document_type_id: worker?.document_type_id ?? '',
    document_number: worker?.document_number ?? '',
    first_name: worker?.first_name ?? '',
    last_name_paternal: worker?.last_name_paternal ?? '',
    last_name_maternal: worker?.last_name_maternal ?? '',
    birth_date: worker?.birth_date ?? '',
    gender_id: worker?.gender_id ?? '',
    phone: worker?.phone ?? '',
    email: worker?.email ?? '',
    address: worker?.address ?? '',
    worker_type_id: worker?.worker_type_id ?? '',
    position_id: worker?.position_id ?? '',
    project_id: worker?.project_id ?? '', // Si es null, el select mostrará "OFICINA CENTRAL"
    company_id: worker?.company_id ?? '',
    daily_salary: worker?.daily_salary ?? 0,
    monthly_salary: worker?.monthly_salary ?? 0,
    payment_type_id: worker?.payment_type_id ?? '',
    bank_id: worker?.bank_id ?? '',
    pension_system_id: worker?.pension_system_id ?? '',
    bank_account: worker?.bank_account ?? '',
    cci: worker?.cci ?? '',
    cuspp: worker?.cuspp ?? '',
    hire_date: worker?.hire_date ?? '',
    is_active: worker ? Boolean(worker.is_active) : true,
    photo: null,
    photo_path: worker?.photo_path ?? null,
});

const form = useForm(mapWorkerToForm());

// --- 3. NOTIFICACIONES (SweetAlert2) ---
watch(() => page.props.flash, (flash) => {
    if (flash?.message) {
        Swal.fire({
            icon: flash.type || 'success',
            title: flash.type === 'error' ? '¡Atención!' : '¡Éxito!',
            text: flash.message,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: { popup: 'rounded-3xl' }
        });
        page.props.flash.message = null;
    }
}, { deep: true });

// --- 4. FILTROS Y BÚSQUEDA ---
watch([search, perPage], () => {
    router.get(route('workers.index'),
        { search: search.value, perPage: perPage.value },
        { preserveState: true, replace: true, preserveScroll: true }
    );
});

// --- 5. ACCIONES ---

const openDrawer = (worker = null) => {
    editMode.value = !!worker;
    form.clearErrors();

    const initialData = mapWorkerToForm(worker);
    Object.keys(initialData).forEach(key => {
        form[key] = initialData[key];
    });

    isDrawerOpen.value = true;
};

const handleSubmit = () => {
    const url = editMode.value
        ? route('workers.update', form.uuid)
        : route('workers.store');

    form.transform((data) => ({
        ...data,
        _method: editMode.value ? 'put' : 'post',
    })).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
            form.reset();
        },
    });
};

const deleteWorker = (worker) => {
    Swal.fire({
        title: '¿Dar de baja?',
        text: `El colaborador ${worker.first_name} será enviado a la papelera.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-3xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('workers.destroy', worker.uuid));
        }
    });
};
</script>

<template>

    <Head title="Maestro de Personal" />

    <AuthenticatedLayout>
        <template #header>Personal</template>

        <template #header-actions>
            <PrimaryButton @click="openDrawer()"
                class="rounded-lg h-10 px-4 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md gap-2 border-none">
                <Plus :size="18" />
                <span class="font-bold text-xs uppercase tracking-widest">Nuevo Ingreso</span>
            </PrimaryButton>
        </template>

        <div class="space-y-4">
            <div
                class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div class="flex-1">
                    <TableFilters v-model="search" v-model:perPage="perPage" />
                </div>
                <div
                    class="hidden md:flex items-center gap-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest shrink-0">
                    <span>Total Colaboradores: {{ workers.total }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3 px-4">
                        <thead>
                            <tr class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.1em]">
                                <th class="px-8 py-3">Colaborador</th>
                                <th class="px-6 py-3">Asignación Laboral</th>
                                <th class="px-6 py-3">Información Bancaria</th>
                                <th class="px-6 py-3 text-right">Sueldo</th>
                                <th class="px-8 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="worker in workers.data" :key="worker.id"
                                class="group transition-all duration-300 ease-in-out cursor-default">

                                <td class="px-8 py-5 bg-white border-y border-l border-slate-100 rounded-l-2xl transition-all duration-300
                           group-hover:border-indigo-300 group-hover:bg-indigo-50/40 
                           group-hover:shadow-[inset_4px_0_0_0_#6366f1,0_8px_20px_-6px_rgba(79,70,229,0.15)]">
                                    <div
                                        class="flex items-center gap-4 transition-transform duration-300 group-hover:translate-x-1">
                                        <div class="relative shrink-0">
                                            <div
                                                class="h-12 w-12 rounded-xl border-2 border-white bg-white flex items-center justify-center overflow-hidden shadow-sm transition-all group-hover:scale-110 group-hover:shadow-md">
                                                <img v-if="worker.photo_url" :src="worker.photo_url"
                                                    class="h-full w-full object-cover" />
                                                <div v-else
                                                    class="w-full h-full bg-slate-50 flex items-center justify-center text-slate-300">
                                                    <User :size="24" />
                                                </div>
                                            </div>
                                            <div :class="worker.is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.4)]'"
                                                class="absolute -bottom-1 -right-1 w-3.5 h-3.5 border-2 border-white rounded-full">
                                            </div>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <h3
                                                class="font-black text-slate-800 uppercase text-[13px] tracking-tight truncate group-hover:text-indigo-900">
                                                {{ worker.last_name_paternal }} {{ worker.last_name_maternal }}, {{
                                                    worker.first_name }}
                                            </h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold text-slate-400">DNI {{
                                                    worker.document_number }}</span>
                                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                <div
                                                    class="flex items-center gap-1 text-[10px] font-bold text-indigo-400">
                                                    <Phone :size="10" stroke-width="3" />
                                                    <span>{{ worker.phone || 'S/N' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="px-6 py-5 bg-white border-y border-slate-100 transition-all duration-300 group-hover:border-indigo-300 group-hover:bg-indigo-50/40">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5">
                                            <Briefcase :size="12" class="text-indigo-500" />
                                            <span
                                                class="text-[10px] font-black text-slate-700 uppercase group-hover:text-indigo-900">{{
                                                    worker.position?.name }}</span>
                                        </div>
                                        <span
                                            class="text-[9px] text-slate-400 font-bold italic pl-[18px] group-hover:text-indigo-400 transition-colors">{{
                                                worker.project?.name || 'Oficina Central' }}</span>
                                    </div>
                                </td>

                                <td
                                    class="px-6 py-5 bg-white border-y border-slate-100 transition-all duration-300 group-hover:border-indigo-300 group-hover:bg-indigo-50/40">
                                    <div class="flex flex-col gap-1.5">
                                        <div
                                            class="flex items-center gap-2 bg-slate-50 group-hover:bg-white rounded-lg p-1 border border-slate-100 group-hover:border-indigo-200 transition-all">
                                            <span
                                                class="bg-white px-1.5 py-0.5 rounded text-[8px] font-black text-slate-400 border border-slate-100 min-w-[35px] text-center uppercase">
                                                {{ worker.bank?.short_name || 'CTA' }}
                                            </span>
                                            <span
                                                class="font-mono text-[10px] font-bold text-slate-600 select-all group-hover:text-indigo-600">{{
                                                    worker.bank_account || '---' }}</span>
                                        </div>
                                        <div v-if="worker.cci"
                                            class="flex items-center gap-2 bg-indigo-50/50 group-hover:bg-white rounded-lg p-1 border border-indigo-100/50 group-hover:border-indigo-200 transition-all">
                                            <span
                                                class="bg-indigo-500 px-1.5 py-0.5 rounded text-[8px] font-black text-white min-w-[35px] text-center">CCI</span>
                                            <span class="font-mono text-[10px] font-bold text-indigo-700 select-all">{{
                                                worker.cci }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="px-6 py-5 bg-white border-y border-slate-100 transition-all duration-300 group-hover:border-indigo-300 group-hover:bg-indigo-50/40 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="text-[8px] font-black text-slate-400 uppercase mb-0.5 group-hover:text-indigo-400">Sueldo
                                            Neto</span>
                                        <span
                                            class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                            S/ {{ Number(worker.monthly_salary).toLocaleString('es-PE',
                                                { minimumFractionDigits: 2 }) }}
                                        </span>
                                    </div>
                                </td>

                                <td
                                    class="px-8 py-5 bg-white border-y border-r border-slate-100 rounded-r-2xl transition-all duration-300 group-hover:border-indigo-300 group-hover:bg-indigo-50/40 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('workers.show', worker)"
                                            class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-emerald-600 hover:border-emerald-200 hover:shadow-sm transition-all duration-200 active:scale-95 shadow-sm"
                                            title="Explorar">
                                            <ExternalLink :size="16" />
                                        </Link>
                                        <button @click="openDrawer(worker)"
                                            class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-200 hover:shadow-md transition-all active:scale-95 shadow-sm">
                                            <Pencil :size="15" />
                                        </button>
                                        <button @click="deleteWorker(worker)"
                                            class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-red-600 hover:border-red-200 hover:shadow-md transition-all active:scale-95 shadow-sm">
                                            <Trash2 :size="15" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState v-if="workers.data.length === 0" :search="search" />

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="workers.links" />
                </div>
            </div>
        </div>

        <WorkerDrawer :show="isDrawerOpen" :edit-mode="editMode" :form="form" :params="params" :positions="positions"
            :banks="banks" :pension-systems="pensionSystems" :companies="companies" :projects="projects"
            @close="isDrawerOpen = false" @submit="handleSubmit" />
    </AuthenticatedLayout>
</template>