<script setup>
import { ref, watch, onUnmounted } from 'vue';
import {
    X, Camera, User, Briefcase, Landmark,
    CreditCard, Hash, Phone
} from 'lucide-vue-next';

// Componentes Base
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

/**
 * Props normalizadas. 
 * Nota: Asegúrate de que desde el padre envíes pensionSystems y banks 
 * como props independientes según tu definición inicial.
 */
const props = defineProps({
    show: { type: Boolean, default: false },
    editMode: { type: Boolean, default: false },
    form: { type: Object, required: true },
    params: { type: Object, default: () => ({}) },
    positions: { type: Array, default: () => [] },
    banks: { type: Array, default: () => [] },
    pensionSystems: { type: Array, default: () => [] }, // Corregido a camelCase
    companies: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'submit']);

// --- Estado ---
const photoPreview = ref(null);

// --- Lógica de Negocio (Sueldos) ---
const syncSalaries = (origin) => {
    const value = parseFloat(origin === 'monthly' ? props.form.monthly_salary : props.form.daily_salary) || 0;
    if (origin === 'monthly') {
        props.form.daily_salary = value > 0 ? (value / 30).toFixed(2) : "";
    } else {
        props.form.monthly_salary = value > 0 ? (value * 30).toFixed(2) : "";
    }
};

// Funciones llamadas desde el template
const calculateDaily = () => syncSalaries('monthly');
const calculateMonthly = () => syncSalaries('daily');

// --- Manejo de Archivos ---
const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => photoPreview.value = event.target.result;
    reader.readAsDataURL(file);
    props.form.photo = file;
};

// --- Watchers & Lifecycle ---
watch(() => props.show, (isVisible) => {
    document.body.style.overflow = isVisible ? 'hidden' : 'auto';
    if (!isVisible) photoPreview.value = null;
});

/**
 * Normalización de entradas: Nombres en mayúsculas.
 */
watch(
    () => [props.form.first_name, props.form.last_name_paternal, props.form.last_name_maternal],
    ([fn, lp, lm]) => {
        if (fn) props.form.first_name = fn.toUpperCase();
        if (lp) props.form.last_name_paternal = lp.toUpperCase();
        if (lm) props.form.last_name_maternal = lm.toUpperCase();
    }
);

onUnmounted(() => {
    document.body.style.overflow = 'auto';
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex justify-end overflow-hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-3xl transform transition duration-500 ease-in-out">

                <form @submit.prevent="emit('submit')" class="flex h-full flex-col bg-white shadow-2xl">

                    <div class="bg-gray-50 px-6 py-6 border-b border-gray-100 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-600 p-2 rounded-lg text-white">
                                    <User :size="24" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-gray-800 tracking-tight leading-none">
                                        {{ editMode ? 'Editar Colaborador' : 'Expediente de Ingreso' }}
                                    </h2>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1.5">
                                        Módulo de Personal / Altas y Modificaciones
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="emit('close')"
                                class="rounded-full p-2 text-gray-400 hover:bg-white transition">
                                <X :size="24" />
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 space-y-12 custom-scrollbar bg-white">

                        <div class="space-y-6">
                            <h3
                                class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2 flex items-center gap-2">
                                <Hash :size="14" /> 1. Datos de Identidad
                            </h3>
                            <div class="flex gap-8">
                                <div class="relative group h-36 w-36 shrink-0">
                                    <div
                                        class="h-full w-full rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden transition-all group-hover:border-indigo-400">
                                        <img v-if="photoPreview || (editMode && form.photo_path)"
                                            :src="photoPreview || `/storage/${form.photo_path}`"
                                            class="h-full w-full object-cover" />
                                        <Camera v-else class="text-gray-300" :size="32" />
                                    </div>
                                    <label
                                        class="absolute inset-0 bg-indigo-600/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center cursor-pointer rounded-3xl transition-all scale-95 group-hover:scale-100">
                                        <Camera class="text-white mb-1" :size="24" />
                                        <span class="text-[9px] text-white font-black uppercase text-center">Subir
                                            Foto</span>
                                        <input type="file" class="hidden" @change="handlePhotoChange"
                                            accept="image/*" />
                                    </label>
                                </div>

                                <div class="flex-1 grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <InputLabel value="Tipo Doc." />
                                        <select v-model="form.document_type_id" class="custom-select">
                                            <option v-for="t in params.document_types" :key="t.id" :value="t.id">{{
                                                t.name }}</option>
                                        </select>
                                        <InputError :message="form.errors.document_type_id" />
                                    </div>
                                    <div class="space-y-1">
                                        <InputLabel value="Número Documento" />
                                        <TextInput v-model="form.document_number"
                                            class="block w-full bg-gray-50/50 font-mono" />
                                        <InputError :message="form.errors.document_number" />
                                    </div>
                                    <div class="col-span-2 grid grid-cols-3 gap-3">
                                        <div>
                                            <InputLabel value="Nombres" />
                                            <TextInput v-model="form.first_name"
                                                class="mt-1 block w-full bg-gray-50/50 uppercase text-xs" />
                                            <InputError :message="form.errors.first_name" />
                                        </div>
                                        <div>
                                            <InputLabel value="Ap. Paterno" />
                                            <TextInput v-model="form.last_name_paternal"
                                                class="mt-1 block w-full bg-gray-50/50 uppercase text-xs" />
                                            <InputError :message="form.errors.last_name_paternal" />
                                        </div>
                                        <div>
                                            <InputLabel value="Ap. Materno" />
                                            <TextInput v-model="form.last_name_maternal"
                                                class="mt-1 block w-full bg-gray-50/50 uppercase text-xs" />
                                            <InputError :message="form.errors.last_name_maternal" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3
                                class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2 flex items-center gap-2">
                                <Phone :size="14" /> 2. Información Personal y Contacto
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <InputLabel value="F. Nacimiento" />
                                    <TextInput type="date" v-model="form.birth_date"
                                        class="mt-1 block w-full text-xs" />
                                    <InputError :message="form.errors.birth_date" />
                                </div>
                                <div>
                                    <InputLabel value="Género" />
                                    <select v-model="form.gender_id" class="custom-select">
                                        <option v-for="g in params.genders" :key="g.id" :value="g.id">{{ g.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.gender_id" />
                                </div>
                                <div>
                                    <InputLabel value="Celular" />
                                    <TextInput v-model="form.phone" class="mt-1 block w-full" placeholder="999..." />
                                    <InputError :message="form.errors.phone" />
                                </div>
                                <div class="col-span-1">
                                    <InputLabel value="Correo Electrónico" />
                                    <TextInput type="email" v-model="form.email" class="mt-1 block w-full text-xs"
                                        placeholder="correo@ejemplo.com" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Dirección Domiciliaria" />
                                    <TextInput v-model="form.address" class="mt-1 block w-full uppercase text-xs"
                                        placeholder="Av. Ejemplo 123..." />
                                    <InputError :message="form.errors.address" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3
                                class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2 flex items-center gap-2">
                                <Briefcase :size="14" /> 3. Estructura Organizativa
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <InputLabel value="Empresa Empleadora" />
                                    <select v-model="form.company_id"
                                        class="custom-select font-bold text-indigo-900 bg-indigo-50/30 border-indigo-100">
                                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.company_id" />
                                </div>
                                <div>
                                    <InputLabel value="Tipo de Trabajador" />
                                    <select v-model="form.worker_type_id" class="custom-select">
                                        <option v-for="wt in params.worker_types" :key="wt.id" :value="wt.id">{{ wt.name
                                            }}</option>
                                    </select>
                                    <InputError :message="form.errors.worker_type_id" />
                                </div>
                                <div>
                                    <InputLabel value="Cargo / Posición" />
                                    <select v-model="form.position_id" class="custom-select">
                                        <option v-for="p in positions" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.position_id" />
                                </div>
                                <div>
                                    <InputLabel value="Proyecto Asignado" />
                                    <select v-model="form.project_id" class="custom-select">
                                        <option value="">OFICINA CENTRAL</option>
                                        <option v-for="pj in projects" :key="pj.id" :value="pj.id">{{ pj.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.project_id" />
                                </div>
                                <div>
                                    <InputLabel value="Fecha de Ingreso" />
                                    <TextInput type="date" v-model="form.hire_date"
                                        class="mt-1 block w-full text-xs font-bold" />
                                    <InputError :message="form.errors.hire_date" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3
                                class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <CreditCard :size="14" /> 4. Estructura Salarial
                                </span>
                                <span class="text-[9px] font-bold text-emerald-500">● Sincronizado (30 días)</span>
                            </h3>
                            <div
                                class="grid grid-cols-3 gap-4 bg-indigo-50/30 p-5 rounded-2xl border border-indigo-100/50">
                                <div>
                                    <InputLabel value="Sueldo Mensual" class="text-indigo-700" />
                                    <div class="relative mt-1">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-indigo-500">S/</span>
                                        <TextInput type="number" step="0.01" v-model="form.monthly_salary"
                                            @input="calculateDaily"
                                            :class="{ '!border-red-500 !ring-red-200': form.errors.monthly_salary }"
                                            class="block w-full pl-9 !border-indigo-200 focus:!ring-indigo-500 font-bold text-lg text-indigo-700" />
                                    </div>
                                    <InputError :message="form.errors.monthly_salary" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Sueldo Diario (Ref)" />
                                    <div class="relative mt-1">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">S/</span>
                                        <TextInput type="number" step="0.01" v-model="form.daily_salary"
                                            @input="calculateMonthly"
                                            :class="{ '!border-red-500': form.errors.daily_salary }"
                                            class="block w-full pl-8 bg-white/50 border-dashed border-gray-300 opacity-80" />
                                    </div>
                                    <InputError :message="form.errors.daily_salary" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Frecuencia Pago" />
                                    <select v-model="form.payment_type_id" class="custom-select bg-white"
                                        :class="{ '!border-red-500': form.errors.payment_type_id }">
                                        <option v-for="pt in params.payment_types" :key="pt.id" :value="pt.id">{{
                                            pt.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.payment_type_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3
                                class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2 flex items-center gap-2">
                                <Landmark :size="14" /> 5. Bancos y Previsión (AFP)
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel value="Sistema de Pensiones" />
                                    <select v-model="form.pension_system_id" class="custom-select">
                                        <option v-for="ps in pensionSystems" :key="ps.id" :value="ps.id">{{ ps.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.pension_system_id" />
                                </div>
                                <div>
                                    <InputLabel value="Código CUSPP" />
                                    <TextInput v-model="form.cuspp" class="mt-1 block w-full font-mono text-xs"
                                        placeholder="Código AFP..." />
                                    <InputError :message="form.errors.cuspp" />
                                </div>
                                <div>
                                    <InputLabel value="Banco" />
                                    <select v-model="form.bank_id" class="custom-select">
                                        <option v-for="b in banks" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.bank_id" />
                                </div>
                                <div>
                                    <InputLabel value="N° Cuenta" />
                                    <TextInput v-model="form.bank_account"
                                        class="mt-1 block w-full font-mono text-xs" />
                                    <InputError :message="form.errors.bank_account" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Código Interbancario (CCI)" />
                                    <TextInput v-model="form.cci" class="mt-1 block w-full font-mono text-xs"
                                        placeholder="000-000-000000000000-00" />
                                    <InputError :message="form.errors.cci" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 px-8 py-6 bg-white shrink-0">
                        <div class="flex items-center justify-between">
                            <button type="button" @click="emit('close')"
                                class="text-xs font-black text-gray-400 hover:text-rose-500 transition-colors uppercase tracking-widest">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="form.processing"
                                class="!px-12 !py-3.5 shadow-xl shadow-indigo-100">
                                {{ editMode ? 'Actualizar Expediente' : 'Registrar Colaborador' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-select {
    @apply mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-gray-50/30 text-xs font-bold uppercase py-2 px-3;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6366f1;
}
</style>