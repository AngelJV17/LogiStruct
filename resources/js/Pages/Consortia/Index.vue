<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Pencil, Trash2, Contact, Plus, Briefcase } from 'lucide-vue-next';
import Swal from 'sweetalert2';

// Componentes
import TableFilters from '@/Components/TableFilters.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConsortiumDrawer from './Partials/ConsortiumDrawer.vue';
import ConsortiumDetailModal from './Partials/ConsortiumDetailModal.vue';

const page = usePage();
const props = defineProps({
    consortia: Object,
    companies: Array,
    filters: Object
});

// --- 1. ESTADOS ---
const isDrawerOpen = ref(false);
const isDetailModalOpen = ref(false);
const editMode = ref(false);
const selectedConsortium = ref(null);
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || '10');

// --- 2. FORMULARIO ---
const form = useForm({
    id: null,
    ruc: '',
    name: '',
    url_logo: null,
    legal_representative: '',
    representative_dni: '',
    representative_email: '',
    representative_phone: '',
    selected_companies: [] // Socios y porcentajes
});

// --- 3. WATCHERS (NAVEGACIÓN) ---
watch([search, perPage], () => {
    router.get(route('consortia.index'),
        { search: search.value, perPage: perPage.value },
        { preserveState: true, preserveScroll: true }
    );
});

// --- 4. ACCIONES ---

/** Abre el Drawer para crear o editar consorcio */
const openDrawer = (consortium = null) => {
    editMode.value = !!consortium;
    form.clearErrors();

    if (consortium) {
        // Carga de datos para edición
        form.id = consortium.id;
        form.ruc = consortium.ruc;
        form.name = consortium.name;
        form.url_logo = consortium.url_logo;
        form.legal_representative = consortium.legal_representative;
        form.representative_dni = consortium.representative_dni;
        form.representative_email = consortium.representative_email;
        form.representative_phone = consortium.representative_phone;

        // Mapeo de socios (relación Many-to-Many con pivote)
        form.selected_companies = consortium.companies ? consortium.companies.map(c => ({
            company_id: c.id,
            percentage: c.pivot.participation_percentage || c.pivot.percentage
        })) : [];
    } else {
        // Reset para nuevo registro
        form.reset();
        form.selected_companies = [];
    }
    isDrawerOpen.value = true;
};

/** Muestra modal con detalles de contacto */
const showContact = (consortium) => {
    selectedConsortium.value = consortium;
    isDetailModalOpen.value = true;
};

// Watcher para mensajes Flash (Replicando la lógica de Empresas)
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
}, { deep: true });

/** Envío de datos al servidor */
const handleSubmit = () => {
    const url = editMode.value ? route('consortia.update', form.id) : route('consortia.store');

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

/** Confirmación de eliminación */
const deleteItem = (id) => {
    Swal.fire({
        title: '¿Eliminar Consorcio?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((res) => {
        if (res.isConfirmed) router.delete(route('consortia.destroy', id));
    });
};
</script>

<template>

    <Head title="Gestión de Consorcios" />

    <AuthenticatedLayout>
        <template #header>Consorcios</template>

        <template #header-actions>
            <PrimaryButton @click="openDrawer()"
                class="rounded-lg h-10 px-4 bg-indigo-600 hover:bg-indigo-700 transition-all shadow-md gap-2 border-none">
                <Plus :size="18" />
                <span class="font-bold text-xs uppercase tracking-widest">Nuevo Consorcio</span>
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
                    <span>Total Consorcios: {{ consortia.data.length }}</span>
                </div>

            </div>

            <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead
                            class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Información del Consorcio</th>
                                <th class="px-6 py-4">Representante Legal</th>
                                <th class="px-6 py-4 text-center">Composición / Socios</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="c in consortia.data" :key="c.id"
                                class="group transition-all duration-200 hover:bg-slate-50/80">

                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-10 w-10 rounded-lg border border-slate-200 bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-sm group-hover:border-indigo-300 transition-colors">
                                            <img v-if="c.url_logo" :src="`/storage/${c.url_logo}`"
                                                class="h-full w-full object-cover" />
                                            <Briefcase v-else class="text-slate-300" :size="18" />
                                        </div>
                                        <div class="leading-tight">
                                            <div
                                                class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors uppercase text-xs">
                                                {{ c.name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-bold mt-1 tracking-wide">
                                                RUC: {{ c.ruc || 'PENDIENTE' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-xs">
                                            {{ c.legal_representative || 'Sinasignar' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium truncate max-w-[180px]">{{
                                            c.representative_email || 'S/E' }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <div class="flex -space-x-2 overflow-hidden">
                                            <template v-for="company in c.companies.slice(0, 3)" :key="company.id">
                                                <div class="inline-block h-7 w-7 rounded-full ring-2 ring-white bg-slate-100 overflow-hidden shadow-sm"
                                                    :title="company.name">
                                                    <img v-if="company.url_logo" :src="`/storage/${company.url_logo}`"
                                                        class="h-full w-full object-cover" />
                                                    <div v-else
                                                        class="h-full w-full bg-indigo-50 flex items-center justify-center text-[8px] font-black text-indigo-500 uppercase">
                                                        {{ company.name.substring(0, 2) }}
                                                    </div>
                                                </div>
                                            </template>
                                            <div v-if="c.companies.length > 3"
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-800 ring-2 ring-white text-[8px] font-black text-white shadow-sm">
                                                +{{ c.companies.length - 3 }}
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
                                            {{ c.companies.length }} {{ c.companies.length === 1 ? 'Socio' : 'Socios' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-3 text-right">
                                    <div
                                        class="flex justify-end items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <button @click="showContact(c)"
                                            class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                            title="Detalles">
                                            <Contact :size="16" />
                                        </button>
                                        <button @click="openDrawer(c)"
                                            class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                            title="Editar">
                                            <Pencil :size="16" />
                                        </button>
                                        <button @click="deleteItem(c.id)"
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                            title="Eliminar">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState v-if="consortia.data.length === 0" :search="search" />

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="consortia.links" />
                </div>
            </div>
        </div>

        <ConsortiumDrawer :show="isDrawerOpen" :edit-mode="editMode" :form="form" :all-companies="companies"
            @close="isDrawerOpen = false" @submit="handleSubmit" />
        <ConsortiumDetailModal :show="isDetailModalOpen" :consortium="selectedConsortium"
            @close="isDetailModalOpen = false" />
    </AuthenticatedLayout>
</template>