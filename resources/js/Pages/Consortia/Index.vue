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
                    <table class="w-full text-left border-separate border-spacing-y-3 px-4">
                        <thead>
                            <tr class="text-slate-500 text-[11px] font-bold uppercase tracking-[0.1em]">
                                <th class="px-8 py-3">Información del Consorcio</th>
                                <th class="px-6 py-3">Representante Legal</th>
                                <th class="px-6 py-3 text-center">Composición / Socios</th>
                                <th class="px-8 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in consortia.data" :key="c.id"
                                class="group transition-all duration-300 ease-in-out">

                                <td colspan="4" class="p-0">
                                    <div
                                        class="relative flex items-center w-full bg-white border border-slate-100 rounded-2xl transition-all duration-300 
                                                group-hover:border-indigo-300 group-hover:bg-indigo-50/40 group-hover:-translate-y-0.5
                                                group-hover:shadow-[inset_4px_0_0_0_#6366f1,0_8px_20px_-6px_rgba(79,70,229,0.15)]">

                                        <div class="w-1/3 px-8 py-5 flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 rounded-xl border border-slate-100 bg-white flex items-center justify-center overflow-hidden shadow-sm transition-transform group-hover:scale-110 group-hover:border-indigo-100">
                                                <img v-if="c.url_logo" :src="`/storage/${c.url_logo}`"
                                                    class="h-full w-full object-cover" />
                                                <Briefcase v-else class="text-slate-300" :size="20" />
                                            </div>
                                            <div class="leading-tight">
                                                <div
                                                    class="font-black text-slate-800 uppercase text-xs tracking-tight transition-colors group-hover:text-indigo-900">
                                                    {{ c.name }}
                                                </div>
                                                <div
                                                    class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-[9px] font-bold text-slate-500 mt-2 uppercase transition-colors group-hover:bg-white group-hover:text-indigo-600">
                                                    RUC: {{ c.ruc || 'Pendiente' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5">
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-bold text-slate-700 text-xs transition-colors group-hover:text-slate-900">
                                                    {{ c.legal_representative || 'Sin asignar' }}
                                                </span>
                                                <span
                                                    class="text-[10px] text-slate-400 font-medium mt-1 italic transition-colors group-hover:text-indigo-400">
                                                    {{ c.representative_email || 'correo@ejemplo.com' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-6 py-5">
                                            <div class="flex flex-col items-center gap-2">
                                                <div class="flex -space-x-3 overflow-hidden">
                                                    <template v-for="company in c.companies.slice(0, 3)"
                                                        :key="company.id">
                                                        <div class="inline-block h-8 w-8 rounded-full ring-4 ring-white bg-slate-50 overflow-hidden shadow-sm hover:z-10 transition-transform hover:scale-110"
                                                            :title="company.name">
                                                            <img v-if="company.url_logo"
                                                                :src="`/storage/${company.url_logo}`"
                                                                class="h-full w-full object-cover" />
                                                            <div v-else
                                                                class="h-full w-full flex items-center justify-center text-[9px] font-black text-indigo-400 uppercase">
                                                                {{ company.name.substring(0, 2) }}
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div v-if="c.companies.length > 3"
                                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 ring-4 ring-white text-[9px] font-black text-white shadow-sm">
                                                        +{{ c.companies.length - 3 }}
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-500">
                                                    {{ c.companies.length }} Miembros
                                                </span>
                                            </div>
                                        </div>

                                        <div class="w-1/4 px-8 py-5 flex justify-end gap-2">
                                            <button @click="showContact(c)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-emerald-600 hover:border-emerald-200 hover:shadow-sm transition-all duration-200 active:scale-95"
                                                title="Ver Detalles">
                                                <Contact :size="16" />
                                            </button>

                                            <button @click="openDrawer(c)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm transition-all duration-200 active:scale-95"
                                                title="Editar">
                                                <Pencil :size="16" />
                                            </button>

                                            <button @click="deleteItem(c.id)"
                                                class="p-2.5 bg-white border border-slate-100 text-slate-400 rounded-xl hover:text-red-600 hover:border-red-200 hover:shadow-sm transition-all duration-200 active:scale-95"
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