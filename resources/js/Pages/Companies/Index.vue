<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Pencil, Trash2, Contact, Building2, Plus } from 'lucide-vue-next';
import Swal from 'sweetalert2';

// Componentes
import TableFilters from '@/Components/TableFilters.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import CompanyDrawer from './Partials/CompanyDrawer.vue';
import CompanyDetailModal from './Partials/CompanyDetailModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    companies: Object,
    filters: Object,
});

const page = usePage();

// --- 1. ESTADOS ---
const isDrawerOpen = ref(false);
const isDetailModalOpen = ref(false);
const editMode = ref(false);
const selectedCompany = ref(null);
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || '10');

// --- 2. FORMULARIO ---
const form = useForm({
    id: null,
    ruc: '',
    name: '',
    url_logo: null,
    phone: '',
    email: '',
    address: '',
    legal_representative: '',
    representative_dni: '',
    representative_phone: '',
    issues_payment_order: false // <-- Asegúrate que empiece en false
});

// Watcher para mensajes Flash (SweetAlert centralizado)
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
            // Limpiamos el mensaje para que no se repita
            page.props.flash.message = null;
        });
    }
}, { deep: true });

// --- 3. WATCHERS (Filtros) ---
watch([search, perPage], () => {
    router.get(route('companies.index'),
        { search: search.value, perPage: perPage.value },
        { preserveState: true, replace: true, preserveScroll: true }
    );
});

// --- 4. ACCIONES ---

/** Abre el drawer para crear o editar */
const openDrawer = (company = null) => {
    editMode.value = !!company;
    form.clearErrors();

    if (company) {
        form.id = company.id;
        form.ruc = company.ruc;
        form.name = company.name;
        form.url_logo = company.url_logo;
        form.phone = company.phone;
        form.email = company.email;
        form.address = company.address;
        form.legal_representative = company.legal_representative;
        form.representative_dni = company.representative_dni;
        form.representative_phone = company.representative_phone;
        form.issues_payment_order = Boolean(company.issues_payment_order);
    } else {
        form.reset();
    }
    isDrawerOpen.value = true;
};

/** Muestra modal con detalles de la empresa */
const showContact = (company) => {
    selectedCompany.value = company;
    isDetailModalOpen.value = true;
};

/** Envío de datos al backend */
const handleSubmit = () => {
    const url = editMode.value
        ? route('companies.update', form.id)
        : route('companies.store');

    // Transformación necesaria para manejar archivos con método PUT en Laravel
    form.transform((data) => ({
        ...data,
        _method: editMode.value ? 'PUT' : 'POST',
    })).post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
            form.reset();
        },
    });
};

/** Elimina un registro con confirmación */
const deleteItem = (id) => {
    Swal.fire({
        title: '¿Eliminar empresa?',
        text: "Se perderán todos los datos vinculados a esta empresa.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('companies.destroy', id));
        }
    });
};
</script>

<template>

    <Head title="Gestión de Empresas" />

    <AuthenticatedLayout>
        <template #header>Empresas</template>

        <template #header-actions>
            <PrimaryButton @click="openDrawer()"
                class="rounded-lg h-10 px-4 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md gap-2 border-none">
                <Plus :size="18" />
                <span class="font-bold text-xs uppercase tracking-widest">Nueva Empresa</span>
            </PrimaryButton>
        </template>

        <div class="space-y-4">
            <div
                class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between gap-4">
                <div class="flex-grow">
                    <TableFilters v-model="search" v-model:perPage="perPage" />
                </div>
                <div
                    class="hidden md:flex items-center gap-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest shrink-0 whitespace-nowrap">
                    <span>Total Empresas: {{ companies.data.length }}</span>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3 px-4">
                        <thead>
                            <tr class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.1em]">
                                <th class="px-8 py-3">Información Fiscal</th>
                                <th class="px-6 py-3">Representación</th>
                                <th class="px-6 py-3 text-center">Estado OP</th>
                                <th class="px-8 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="co in companies.data" :key="co.id"
                                class="group transition-all duration-300 ease-in-out">

                                <td colspan="4" class="p-0">
                                    <div
                                        class="relative flex items-center w-full bg-white border border-slate-100 rounded-2xl transition-all duration-300 
                                                group-hover:border-indigo-300 group-hover:bg-indigo-50/40 group-hover:-translate-y-0.5
                                                group-hover:shadow-[inset_4px_0_0_0_#6366f1,0_8px_20px_-6px_rgba(79,70,229,0.15)]">

                                        <div class="w-1/3 px-8 py-5 flex items-center gap-4">
                                            <div class="relative shrink-0">
                                                <div
                                                    class="h-12 w-12 rounded-xl border border-slate-100 bg-white flex items-center justify-center overflow-hidden shadow-sm transition-transform group-hover:scale-110 group-hover:border-indigo-100">
                                                    <img v-if="co.url_logo" :src="`/storage/${co.url_logo}`"
                                                        class="h-full w-full object-cover" />
                                                    <Building2 v-else class="text-slate-300" :size="20" />
                                                </div>
                                            </div>
                                            <div class="leading-tight truncate">
                                                <div
                                                    class="font-black text-slate-800 uppercase text-xs tracking-tight group-hover:text-indigo-900 transition-colors">
                                                    {{ co.name }}
                                                </div>
                                                <div
                                                    class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-[9px] font-bold text-slate-500 mt-2 uppercase transition-colors group-hover:bg-white group-hover:text-indigo-600">
                                                    RUC: {{ co.ruc || '---' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-1.5">
                                                    <User :size="12" class="text-indigo-400" />
                                                    <span
                                                        class="font-bold text-slate-700 text-xs group-hover:text-slate-900 transition-colors">
                                                        {{ co.legal_representative || 'Sin asignar' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-1.5">
                                                    <Phone :size="10"
                                                        class="text-slate-400 group-hover:text-indigo-400" />
                                                    <span
                                                        class="text-[10px] text-slate-400 font-medium italic group-hover:text-indigo-400">
                                                        {{ co.representative_phone || 'S/T' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5 text-center flex justify-center">
                                            <div :class="[
                                                'flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all duration-300',
                                                co.issues_payment_order
                                                    ? 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-sm shadow-emerald-100 group-hover:bg-emerald-100'
                                                    : 'bg-slate-50 text-slate-400 border-slate-100 group-hover:bg-white'
                                            ]">
                                                <div
                                                    :class="['w-1.5 h-1.5 rounded-full', co.issues_payment_order ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300']">
                                                </div>
                                                <span class="text-[10px] font-black uppercase tracking-wider">
                                                    {{ co.issues_payment_order ? 'Emite OP' : 'No Emite' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="w-1/6 px-8 py-5 flex justify-end gap-2">
                                            <button @click="showContact(co)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-emerald-600 hover:border-emerald-200 hover:shadow-sm transition-all duration-200 active:scale-95 shadow-sm"
                                                title="Ver Detalle">
                                                <Contact :size="16" />
                                            </button>
                                            <button @click="openDrawer(co)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm transition-all duration-200 active:scale-95 shadow-sm"
                                                title="Editar">
                                                <Pencil :size="16" />
                                            </button>
                                            <button @click="deleteItem(co.id)"
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

                <EmptyState v-if="companies.data.length === 0" :search="search" />

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="companies.links" />
                </div>
            </div>
        </div>

        <CompanyDrawer :show="isDrawerOpen" :edit-mode="editMode" :form="form" @close="isDrawerOpen = false"
            @submit="handleSubmit" />
        <CompanyDetailModal :show="isDetailModalOpen" :company="selectedCompany" @close="isDetailModalOpen = false" />
    </AuthenticatedLayout>
</template>