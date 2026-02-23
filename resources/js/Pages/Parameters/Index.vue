<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Pencil, Trash2, Plus, Layers, Info } from 'lucide-vue-next';
import Swal from 'sweetalert2';

// Componentes Reutilizables
import TableFilters from '@/Components/TableFilters.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Componente Modal (Extraído)
import ParameterModal from './Partials/ParameterModal.vue';

const page = usePage();
const props = defineProps({
    parameters: Object,
    parentOptions: Array,
    filters: Object
});

// --- 1. ESTADOS DE NAVEGACIÓN Y MODAL ---
const isModalOpen = ref(false);
const editMode = ref(false);
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || '10');

// --- 2. FORMULARIO (Inertia Form) ---
const form = useForm({
    id: null,
    group: '',
    name: '',
    description: '',
    parent_id: '',
    is_active: true
});

// --- 3. WATCHERS (Sincronización con URL) ---
watch([search, perPage], () => {
    router.get(route('parameters.index'),
        { search: search.value, perPage: perPage.value },
        { preserveState: true, preserveScroll: true }
    );
});

// Watcher para notificaciones Flash (Swal)
watch(() => page.props.flash, (flash) => {
    if (flash?.message) {
        Swal.fire({
            icon: flash.type || 'success',
            title: flash.type === 'error' ? '¡Atención!' : '¡Éxito!',
            text: flash.message,
            timer: 3000,
            showConfirmButton: false,
            customClass: { popup: 'rounded-[2rem]' }
        });
        page.props.flash.message = null;
    }
}, { deep: true });

// --- 4. ACCIONES ---
const openModal = (param = null) => {
    editMode.value = !!param;
    form.clearErrors();

    if (param) {
        form.id = param.id;
        form.group = param.group;
        form.name = param.name;
        form.description = param.description;
        form.parent_id = param.parent_id || '';
        form.is_active = !!param.is_active;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const handleSubmit = () => {
    const url = editMode.value ? route('parameters.update', form.id) : route('parameters.store');
    const method = editMode.value ? 'put' : 'post';

    form[method](url, {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const deleteParam = (id) => {
    Swal.fire({
        title: '¿Eliminar Parámetro?',
        text: "Los registros dependientes podrían verse afectados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        confirmButtonColor: '#ef4444',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((res) => {
        if (res.isConfirmed) router.delete(route('parameters.destroy', id));
    });
};
</script>

<template>

    <Head title="Parámetros Globales" />

    <AuthenticatedLayout>
        <template #header>Parámetros del Sistema</template>

        <template #header-actions>
            <PrimaryButton @click="openModal()"
                class="rounded-lg h-10 px-4 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md gap-2 border-none">
                <Plus :size="18" />
                <span class="font-bold text-xs uppercase tracking-widest">Nuevo Parámetro</span>
            </PrimaryButton>
        </template>

        <div class="space-y-4">
            <div
                class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex-1 w-full">
                    <TableFilters v-model="search" v-model:perPage="perPage" />
                </div>
                <div
                    class="hidden md:flex items-center gap-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest shrink-0">
                    <span>Total: {{ parameters.total }} registros</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2 px-4">
                        <thead>
                            <tr class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.1em]">
                                <th class="px-6 py-3">Estructura</th>
                                <th class="px-6 py-3">Nombre del Parámetro</th>
                                <th class="px-6 py-3 text-center">Estado</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="param in parameters.data" :key="param.id"
                                class="group transition-all duration-300 ease-in-out">

                                <td colspan="4" class="p-0">
                                    <div class="relative flex items-center w-full bg-white border border-slate-100 rounded-2xl transition-all duration-300 
                        group-hover:border-indigo-300 group-hover:bg-indigo-50/40 group-hover:-translate-y-0.5
                        group-hover:shadow-[inset_4px_0_0_0_#6366f1,0_8px_20px_-6px_rgba(79,70,229,0.15)]">

                                        <div class="w-1/4 px-6 py-5 flex items-center gap-4">
                                            <div :class="param.level === 0 ? 'bg-slate-200' : 'bg-indigo-500'"
                                                class="w-1 h-10 rounded-full transition-all duration-300 group-hover:shadow-[0_0_10px_#6366f1] group-hover:scale-y-110">
                                            </div>
                                            <div>
                                                <span
                                                    class="block text-[10px] font-black text-slate-400 uppercase leading-none mb-1">
                                                    {{ param.group }}
                                                </span>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-[9px] font-bold text-slate-500 transition-colors group-hover:bg-white">
                                                    {{ param.level === 0 ? 'Categoría Raíz' : 'Opción Nivel 1' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5">
                                            <div class="flex flex-col">
                                                <span
                                                    :class="param.level === 0 ? 'text-slate-900 font-extrabold' : 'text-slate-600 font-semibold'"
                                                    class="text-sm uppercase tracking-tight transition-colors group-hover:text-indigo-900">
                                                    {{ param.name }}
                                                </span>
                                                <span v-if="param.parent"
                                                    class="text-[10px] text-indigo-400 font-bold flex items-center gap-1 mt-0.5">
                                                    <Layers :size="10" /> {{ param.parent.name }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5 text-center flex justify-center">
                                            <div v-if="param.is_active"
                                                class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 group-hover:bg-white group-hover:border-emerald-200">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-wider">Activo</span>
                                            </div>
                                            <div v-else
                                                class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 text-slate-400 border border-slate-200 group-hover:bg-white">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-wider">Inactivo</span>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5 flex justify-end gap-2">
                                            <button @click="openModal(param)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm transition-all duration-200">
                                                <Pencil :size="16" />
                                            </button>
                                            <button @click="deleteParam(param.id)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-red-600 hover:border-red-200 hover:shadow-sm transition-all duration-200">
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState v-if="parameters.data.length === 0" :search="search" />

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="parameters.links" />
                </div>
            </div>
        </div>

        <ParameterModal :show="isModalOpen" :edit-mode="editMode" :form="form" :parent-options="parentOptions"
            @close="isModalOpen = false" @submit="handleSubmit" />
    </AuthenticatedLayout>
</template>