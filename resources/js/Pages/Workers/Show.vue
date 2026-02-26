<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, User, Fingerprint, CreditCard, Phone, Briefcase, Landmark, Calendar } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import WorkerDrawer from './Partials/WorkerDrawer.vue';

const props = defineProps({
    worker: Object,
    positions: Array,
    banks: Array,
    pensionSystems: Array, // Actualizado de pension_systems
    companies: Array,
    projects: Array,
    params: Object
});

// --- 1. ESTADOS ---
const isDrawerOpen = ref(false);
const editMode = ref(false);

// Formateo de datos para la vista
const formattedBirthDate = computed(() => props.worker.birth_date || '---');
const formattedHireDate = computed(() => props.worker.hire_date || '---');

const salaryDisplay = computed(() => ({
    monthly: props.worker.monthly_salary ? `S/ ${Number(props.worker.monthly_salary).toLocaleString('es-PE', { minimumFractionDigits: 2 })}` : 'S/ 0.00',
    daily: props.worker.daily_salary ? `S/ ${Number(props.worker.daily_salary).toLocaleString('es-PE', { minimumFractionDigits: 2 })}` : 'S/ 0.00'
}));

const statusBadgeClasses = computed(() =>
    props.worker.is_active
        ? 'w-2 h-2 rounded-full bg-green-500 animate-pulse'
        : 'w-2 h-2 rounded-full bg-red-500'
);

// --- 2. MAPEO ---
const mapWorkerToForm = (data) => ({
    uuid: data?.uuid ?? null,
    document_type_id: data?.document_type_id ?? '',
    document_number: data?.document_number ?? '',
    first_name: data?.first_name ?? '',
    last_name_paternal: data?.last_name_paternal ?? '',
    last_name_maternal: data?.last_name_maternal ?? '',
    birth_date: data?.birth_date ?? '',
    gender_id: data?.gender_id ?? '',
    phone: data?.phone ?? '',
    email: data?.email ?? '',
    address: data?.address ?? '',
    worker_type_id: data?.worker_type_id ?? '',
    position_id: data?.position_id ?? '',
    project_id: data?.project_id ?? '',
    company_id: data?.company_id ?? '',
    daily_salary: data?.daily_salary ?? 0,
    monthly_salary: data?.monthly_salary ?? 0,
    payment_type_id: data?.payment_type_id ?? '',
    bank_id: data?.bank_id ?? '',
    pension_system_id: data?.pension_system_id ?? '',
    bank_account: data?.bank_account ?? '',
    cci: data?.cci ?? '',
    cuspp: data?.cuspp ?? '',
    hire_date: data?.hire_date ?? '',
    is_active: data ? Boolean(data.is_active) : true,
    photo: null,
    photo_path: data?.photo_path ?? null,
});

const form = useForm(mapWorkerToForm(props.worker));

// --- 3. ACCIONES ---
const openEditDrawer = () => {
    form.clearErrors();
    form.defaults(mapWorkerToForm(props.worker));
    form.reset();
    editMode.value = true;
    isDrawerOpen.value = true;
};

const handleSubmit = () => {
    form.transform((data) => ({ ...data, _method: 'put' }))
        .post(route('workers.update', props.worker.uuid), {
            forceFormData: true,
            onSuccess: () => isDrawerOpen.value = false,
        });
};
</script>

<template>

    <Head :title="`Expediente - ${worker.first_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('workers.index')" class="text-slate-400 hover:text-indigo-600 transition-colors">
                    <ArrowLeft :size="20" />
                </Link>
                <h2 class="font-black uppercase tracking-tighter text-slate-700 italic text-sm">
                    Personal / <span class="text-indigo-600">Expediente Digital</span>
                </h2>
            </div>
        </template>

        <template #header-actions>
            <PrimaryButton @click="openEditDrawer"
                class="rounded-xl h-10 px-5 bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200 shadow-lg border-none">
                <Pencil :size="16" class="mr-2" />
                <span class="font-bold text-xs uppercase tracking-widest">Editar Perfil</span>
            </PrimaryButton>
        </template>

        <div class="max-w-7xl mx-auto space-y-6 pb-20">
            <div class="grid grid-cols-12 gap-6">

                <aside class="col-span-12 lg:col-span-4 space-y-6">
                    <section
                        class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 border-b-indigo-500 border-b-4 text-center">
                        <div
                            class="h-32 w-32 rounded-3xl border-4 border-slate-50 shadow-inner mx-auto mb-5 overflow-hidden flex items-center justify-center bg-slate-100">
                            <img v-if="worker.photo_url" :src="worker.photo_url" class="h-full w-full object-cover"
                                alt="Foto perfil" />
                            <User v-else class="text-slate-300" :size="48" />
                        </div>

                        <h3 class="font-black text-slate-800 uppercase text-lg tracking-tight leading-none">
                            {{ worker.last_name_paternal }} {{ worker.last_name_maternal }}
                            <span class="block text-indigo-600 font-extrabold mt-1">{{ worker.first_name }}</span>
                        </h3>

                        <p class="text-[11px] font-bold text-slate-400 uppercase mt-2 tracking-wider">{{ worker.email }}
                        </p>

                        <div
                            class="mt-6 flex justify-center items-center gap-3 bg-slate-50 py-2.5 px-5 rounded-2xl border border-slate-100 w-fit mx-auto">
                            <div :class="statusBadgeClasses"></div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-[0.15em]">
                                {{ worker.is_active ? 'Colaborador Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </section>

                    <section
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 border-b-sky-500 border-b-4">
                        <h4
                            class="text-[10px] font-black text-sky-600 uppercase tracking-widest mb-5 flex items-center gap-2">
                            <Phone :size="14" /> Información de Contacto
                        </h4>
                        <div class="space-y-4">
                            <div class="group">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Celular
                                    Personal</span>
                                <span class="font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">
                                    {{ worker.phone || '---' }}
                                </span>
                            </div>
                            <div class="group border-t border-slate-50 pt-4">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Dirección
                                    Registrada</span>
                                <span class="font-bold text-slate-700 uppercase leading-tight text-xs">
                                    {{ worker.address || 'No especificado' }}
                                </span>
                            </div>
                        </div>
                    </section>
                </aside>

                <main class="col-span-12 lg:col-span-8 space-y-6">

                    <section class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                        <header
                            class="flex items-center gap-2 mb-8 text-violet-600 font-black text-[10px] uppercase tracking-[0.2em]">
                            <Fingerprint :size="18" />
                            <span>Identidad y Biometría</span>
                        </header>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                            <div v-for="(item, idx) in [
                                { label: 'Tipo Doc.', val: worker.document_type?.name },
                                { label: 'Número', val: worker.document_number },
                                { label: 'F. Nacimiento', val: formattedBirthDate },
                                { label: 'Género', val: worker.gender?.name }
                            ]" :key="idx" class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                                <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">{{ item.label }}</p>
                                <p class="font-black text-slate-700 uppercase text-xs">{{ item.val || '---' }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                        <header
                            class="flex items-center gap-2 mb-8 text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em]">
                            <Briefcase :size="18" />
                            <span>Estructura Laboral</span>
                        </header>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div
                                class="md:col-span-2 bg-indigo-50/30 p-5 rounded-2xl border border-indigo-100 flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] font-bold text-indigo-400 uppercase mb-1">Empresa Empleadora
                                    </p>
                                    <p class="text-sm font-black uppercase text-indigo-900 tracking-tight italic">
                                        {{ worker.company?.name }}
                                    </p>
                                </div>
                                <Landmark class="text-indigo-200" :size="24" />
                            </div>

                            <div v-for="(item, idx) in [
                                { label: 'Proyecto Actual', val: worker.project?.name },
                                { label: 'Cargo / Puesto', val: worker.position?.name },
                                { label: 'Tipo de Régimen', val: worker.worker_type?.name }
                            ]" :key="idx" class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">{{ item.label }}</p>
                                <p class="font-bold text-slate-700 uppercase text-xs">{{ item.val || 'SIN ASIGNAR' }}
                                </p>
                            </div>

                            <div
                                class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 flex justify-between items-center">
                                <div>
                                    <p class="text-[9px] font-bold text-amber-600 uppercase mb-1">Fecha de Ingreso</p>
                                    <p class="font-black text-amber-700">{{ formattedHireDate }}</p>
                                </div>
                                <Calendar class="text-amber-200" :size="20" />
                            </div>
                        </div>
                    </section>

                    <section class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                        <header
                            class="flex items-center gap-2 mb-8 text-emerald-600 font-black text-[10px] uppercase tracking-[0.2em]">
                            <Landmark :size="18" />
                            <span>Información Financiera y Previsional</span>
                        </header>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-emerald-600 p-6 rounded-2xl shadow-emerald-100 shadow-xl text-white">
                                <p class="text-[9px] font-black text-emerald-100 uppercase mb-2">Remuneración Mensual
                                </p>
                                <p class="text-2xl font-black tracking-tighter font-mono">{{ salaryDisplay.monthly }}
                                </p>
                                <div class="mt-4 pt-4 border-t border-white/10">
                                    <p class="text-[8px] font-bold uppercase opacity-70 italic text-right">
                                        Ref. Diario: {{ salaryDisplay.daily }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="md:col-span-2 grid grid-cols-2 gap-4 bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                                <div v-for="(item, idx) in [
                                    { label: 'Entidad Bancaria', val: worker.bank?.name },
                                    { label: 'Número de Cuenta', val: worker.bank_account, highlight: true },
                                    { label: 'Código CCI', val: worker.cci, highlight: true },
                                    { label: 'Frecuencia de Pago', val: worker.payment_type?.name }
                                ]" :key="idx">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mb-1">{{ item.label }}</p>
                                    <p class="text-xs font-black"
                                        :class="item.highlight ? 'text-emerald-600 font-mono' : 'text-slate-700'">
                                        {{ item.val || '---' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="md:col-span-3 bg-indigo-50/40 p-5 rounded-2xl border border-indigo-100/50 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="border-b md:border-b-0 md:border-r border-indigo-100 pb-4 md:pb-0">
                                    <p class="text-[9px] font-bold text-indigo-400 uppercase mb-1">Sistema
                                        Nacional/Privado</p>
                                    <p class="text-xs font-black text-indigo-900 uppercase">
                                        {{ worker.pension_system?.name || '---' }}
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-[9px] font-bold text-indigo-400 uppercase mb-1">Código CUSPP</p>
                                    <p class="font-mono font-black text-indigo-600 tracking-[0.2em] italic text-sm">
                                        {{ worker.cuspp || 'NO REGISTRA' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                </main>
            </div>
        </div>

        <WorkerDrawer :show="isDrawerOpen" :edit-mode="editMode" :form="form" :params="params" :positions="positions"
            :banks="banks" :pension-systems="pensionSystems" :companies="companies" :projects="projects"
            @close="isDrawerOpen = false" @submit="handleSubmit" />
    </AuthenticatedLayout>
</template>