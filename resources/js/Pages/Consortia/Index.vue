<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import { Pencil, Trash2, Contact, Plus, Briefcase } from 'lucide-vue-next';

// Componentes
import TableFilters from '@/Components/TableFilters.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConsortiumDrawer from './Partials/ConsortiumDrawer.vue'; // Cambiado de Modal a Drawer
import ConsortiumDetailModal from './Partials/ConsortiumDetailModal.vue';

const props = defineProps({
    consortia: Object,
    companies: Array,
    filters: Object
});

// --- ESTADOS ---
const isDrawerOpen = ref(false);
const isDetailModalOpen = ref(false);
const editMode = ref(false);
const selectedConsortium = ref(null);
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || '10');

const form = useForm({
    id: null, ruc: '', name: '', url_logo: null,
    legal_representative: '', representative_dni: '',
    representative_email: '', representative_phone: '',
    selected_companies: [] // Estructura para consorciados
});

// --- LÓGICA DE NAVEGACIÓN Y FILTROS ---
watch([search, perPage], () => {
    router.get(route('consortia.index'),
        { search: search.value, perPage: perPage.value },
        { preserveState: true, preserveScroll: true }
    );
});

// --- ACCIONES ---
const openDrawer = (consortium = null) => {
    editMode.value = !!consortium;
    form.clearErrors();

    if (consortium) {
        form.id = consortium.id;
        form.ruc = consortium.ruc;
        form.name = consortium.name;
        form.url_logo = consortium.url_logo;
        form.legal_representative = consortium.legal_representative;
        form.representative_dni = consortium.representative_dni;
        form.representative_email = consortium.representative_email;
        form.representative_phone = consortium.representative_phone;

        // Mapeo senior de relación pivote
        form.selected_companies = consortium.companies ? consortium.companies.map(c => ({
            company_id: c.id,
            percentage: c.pivot.participation_percentage || c.pivot.percentage
        })) : [];
    } else {
        form.reset();
        form.selected_companies = [];
    }
    isDrawerOpen.value = true;
};

const showContact = (consortium) => {
    selectedConsortium.value = consortium;
    isDetailModalOpen.value = true;
};

const handleSubmit = () => {
    const url = editMode.value ? route('consortia.update', form.id) : route('consortia.store');

    // Transformación senior para archivos en PUT
    form.transform((data) => ({
        ...data,
        _method: editMode.value ? 'PUT' : 'POST',
    })).post(url, {
        onSuccess: () => {
            isDrawerOpen.value = false;
            Swal.fire({ icon: 'success', title: 'Operación exitosa', timer: 1500, showConfirmButton: false });
        },
    });
};

const deleteItem = (id) => {
    Swal.fire({
        title: '¿Eliminar Consorcio?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancelar'
    }).then((res) => {
        if (res.isConfirmed) router.delete(route('consortia.destroy', id));
    });
};
</script>

<template>

    <Head title="Gestión de Consorcios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">Empresas</h2>
                <PrimaryButton @click="openDrawer()" class="gap-2 w-full sm:w-auto">
                    <Plus :size="18" /> Nuevo Consorcio
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
                                    <th class="px-6 py-4">Información del Consorcio</th>
                                    <th class="px-6 py-4">Representante Legal</th>
                                    <th class="px-6 py-4">Socios</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="c in consortia.data" :key="c.id"
                                    class="group transition-all duration-200 hover:bg-indigo-50/40 hover:shadow-[inset_4px_0_0_0_#4f46e5]">

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 shadow-sm group-hover:border-indigo-200">
                                                <img v-if="c.url_logo" :src="`/storage/${c.url_logo}`"
                                                    class="h-full w-full object-cover" />
                                                <Briefcase v-else class="text-gray-300" :size="20" />
                                            </div>
                                            <div>
                                                <div
                                                    class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                    {{ c.name }}</div>
                                                <div class="text-xs text-gray-400 font-medium">
                                                    RUC: {{ c.ruc || 'Pendiente' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div :class="['text-sm font-semibold ',
                                            c.legal_representative ? 'text-gray-700' : 'text-gray-400 italic']">
                                            {{ c.legal_representative || 'No asignado' }}
                                        </div>
                                        <div :class="['text-xs font-semibold ',
                                            c.representative_email ? 'text-gray-600' : 'text-gray-400 italic']">
                                            {{ c.representative_email || 'Pendiente' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex -space-x-3 overflow-hidden">
                                                <template v-for="(company) in c.companies.slice(0, 3)"
                                                    :key="company.id">
                                                    <div class="inline-block h-9 w-9 rounded-full ring-2 ring-white bg-gray-800 overflow-hidden shadow-sm"
                                                        :title="company.name">
                                                        <img v-if="company.url_logo"
                                                            :src="`/storage/${company.url_logo}`"
                                                            class="h-full w-full object-cover" :alt="company.name" />
                                                        <div v-else
                                                            class="h-full w-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase">
                                                            {{ company.name.substring(0, 2) }}
                                                        </div>
                                                    </div>
                                                </template>

                                                <div v-if="c.companies.length > 3"
                                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-800 ring-2 ring-white text-[10px] font-bold text-white shadow-sm">
                                                    +{{ c.companies.length - 3 }}
                                                </div>
                                            </div>

                                            <div class="ml-4">
                                                <span class="text-xs font-semibold text-gray-500 italic"
                                                    v-if="c.companies.length === 0">
                                                    Sin socios
                                                </span>
                                                <span
                                                    class="text-[11px] font-bold text-gray-400 uppercase tracking-tighter"
                                                    v-else>
                                                    {{ c.companies.length }} {{ c.companies.length === 1 ? 'Socio' :
                                                        'Socios' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center gap-1">
                                            <button @click="showContact(c)"
                                                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                                title="Detalles">
                                                <Contact :size="18" />
                                            </button>
                                            <button @click="openDrawer(c)"
                                                class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                                title="Editar">
                                                <Pencil :size="18" />
                                            </button>
                                            <button @click="deleteItem(c.id)"
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

                    <EmptyState v-if="consortia.data.length === 0" :search="search" />
                    <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                        <Pagination :links="consortia.links" />
                    </div>
                </div>
            </div>
        </div>

        <ConsortiumDrawer :show="isDrawerOpen" :edit-mode="editMode" :form="form" :all-companies="companies"
            @close="isDrawerOpen = false" @submit="handleSubmit" @updatePhoto="(file) => form.url_logo = file" />

        <ConsortiumDetailModal :show="isDetailModalOpen" :consortium="selectedConsortium"
            @close="isDetailModalOpen = false" />
    </AuthenticatedLayout>
</template>