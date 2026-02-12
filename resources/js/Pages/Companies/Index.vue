<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
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
    issues_payment_order: false
});

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
    const url = editMode.value ? route('companies.update', form.id) : route('companies.store');

    // Transformación necesaria para manejar archivos con método PUT en Laravel
    form.transform((data) => ({
        ...data,
        _method: editMode.value ? 'PUT' : 'POST',
    })).post(url, {
        forceFormData: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Operación exitosa',
                timer: 1500,
                showConfirmButton: false,
                customClass: { popup: 'rounded-[2rem]' }
            });
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
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="font-black text-3xl text-slate-900 tracking-tight">Empresas</h2>
                    <p class="text-sm text-slate-500 font-medium">Visualización y control de empresas.</p>
                </div>
                <PrimaryButton @click="openDrawer()"
                    class="rounded-2xl h-12 px-6 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 gap-2">
                    <Plus :size="20" />
                    <span class="font-bold tracking-tight">Nueva Empresa</span>
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
                                    <th class="px-6 py-4">Información Fiscal</th>
                                    <th class="px-6 py-4">Representación</th>
                                    <th class="px-6 py-4 text-center">Estado OP</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="co in companies.data" :key="co.id"
                                    class="group transition-all duration-200 hover:bg-indigo-50/40 hover:shadow-[inset_4px_0_0_0_#4f46e5]">

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 shadow-sm group-hover:border-indigo-200">
                                                <img v-if="co.url_logo" :src="`/storage/${co.url_logo}`"
                                                    class="h-full w-full object-cover" />
                                                <Building2 v-else class="text-gray-300" :size="20" />
                                            </div>
                                            <div>
                                                <div
                                                    class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors uppercase">
                                                    {{ co.name }}
                                                </div>
                                                <div class="text-xs text-gray-400 font-medium">
                                                    RUC: {{ co.ruc || 'PENDIENTE' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div
                                            :class="['text-sm font-semibold ', co.legal_representative ? 'text-gray-700' : 'text-gray-400 italic']">
                                            {{ co.legal_representative || 'No asignado' }}
                                        </div>
                                        <div
                                            :class="['text-xs font-semibold ', co.representative_phone ? 'text-gray-600' : 'text-gray-400 italic']">
                                            {{ co.representative_phone || 'Teléfono pendiente' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span :class="[
                                            'text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-tighter shadow-sm border',
                                            co.issues_payment_order ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-600 border-gray-100'
                                        ]">
                                            {{ co.issues_payment_order ? 'Emite OP' : 'No Emite' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center gap-1">
                                            <button @click="showContact(co)"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                                title="Ver Ficha">
                                                <Contact :size="18" />
                                            </button>
                                            <button @click="openDrawer(co)"
                                                class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                                title="Editar">
                                                <Pencil :size="18" />
                                            </button>
                                            <button @click="deleteItem(co.id)"
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

                    <EmptyState v-if="companies.data.length === 0" :search="search" />

                    <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                        <Pagination :links="companies.links" />
                    </div>
                </div>
            </div>
        </div>

        <CompanyDrawer :show="isDrawerOpen" :edit-mode="editMode" :form="form" @close="isDrawerOpen = false"
            @submit="handleSubmit" />

        <CompanyDetailModal :show="isDetailModalOpen" :company="selectedCompany" @close="isDetailModalOpen = false" />
    </AuthenticatedLayout>
</template>