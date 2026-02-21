<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

// --- ICONOS ---
import {
    X, Save, Building2, Users, Image as ImageIcon,
    Wallet, Calendar, MapPin
} from 'lucide-vue-next';

// --- COMPONENTES ---
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// --- PROPIEDADES Y EVENTOS ---
const props = defineProps({
    show: Boolean,
    project: Object,
    form: Object,
    types: Array,
    statuses: Array,
    departments: Array,
    companies: Array,
    consortia: Array,
});

const emit = defineEmits(['close', 'submit']);

// --- ESTADOS LOCALES ---
const provinces = ref([]);
const districts = ref([]);
const photoPreview = ref(null);
const fileInput = ref(null);

// --- PROPIEDADES COMPUTADAS ---
const editMode = computed(() => !!props.project);

// Lógica para mostrar qué código se generará (Solo visual)
const autoCodePreview = computed(() => {
    if (editMode.value) return props.form.project_code;
    if (!props.form.type_id) return 'AUTO-GENERADO';

    const selectedType = props.types.find(t => Number(t.id) === Number(props.form.type_id));
    const prefix = selectedType ? selectedType.name.substring(0, 3).toUpperCase() : 'PROY';
    return `${prefix}-${new Date().getFullYear()}-XXXX`;
});

// --- FUNCIONES DE AYUDA ---
const currency = (val) => new Intl.NumberFormat('es-PE', {
    style: 'currency',
    currency: 'PEN'
}).format(val || 0);

const handlePhotoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        props.form.cover_image = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const fetchProvinces = async (id) => {
    if (!id) return;
    const res = await axios.get(route('api.ubigeo.provinces', id));
    provinces.value = res.data;
};

const fetchDistricts = async (id) => {
    if (!id) return;
    const res = await axios.get(route('api.ubigeo.districts', id));
    districts.value = res.data;
};

const formatToInputDate = (dateStr) => {
    if (!dateStr) return '';
    const parts = dateStr.split('/');
    if (parts.length === 3) return `${parts[2]}-${parts[1]}-${parts[0]}`;
    return dateStr;
};

// --- SINCRONIZACIÓN DE DATOS ---
watch(() => props.show, async (val) => {
    if (val && props.project) {
        props.form.id = props.project.id;
        props.form.project_code = props.project.project_code;
        props.form.project_name = props.project.project_name;
        props.form.short_name = props.project.short_name;
        props.form.type_id = props.project.type_id ? Number(props.project.type_id) : '';
        props.form.status_id = props.project.status_id ? Number(props.project.status_id) : '';
        props.form.company_id = props.project.company_id;
        props.form.consortium_id = props.project.consortium_id;

        props.form.contractual_amount = props.project.amounts?.contractual || 0;
        props.form.projected_amount = props.project.amounts?.projected || 0;

        props.form.start_date = formatToInputDate(props.project.dates?.start);
        props.form.end_date_contractual = formatToInputDate(props.project.dates?.end_contractual);
        props.form.end_date_real = formatToInputDate(props.project.dates?.end_real);

        const loc = props.project.location?.ids;
        props.form.department_id = loc?.department || '';
        props.form.province_id = loc?.province || '';
        props.form.district_id = loc?.district || '';
        props.form.address = props.project.location?.address_detail || '';

        props.form.cover_image = null;
        photoPreview.value = props.project.cover_url;

        if (props.form.department_id) {
            await fetchProvinces(props.form.department_id);
            if (props.form.province_id) await fetchDistricts(props.form.province_id);
        }
    } else if (!val) {
        photoPreview.value = null;
        provinces.value = [];
        districts.value = [];
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex justify-end overflow-hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-2xl transform transition duration-500 ease-in-out">
                <form @submit.prevent="emit('submit')" class="flex h-full flex-col bg-white shadow-2xl">

                    <div class="bg-gray-50 px-6 py-6 border-b border-gray-100 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-600 p-2 rounded-lg text-white">
                                    <Building2 :size="24" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-gray-800 tracking-tight">
                                        {{ editMode ? 'Editar Proyecto' : 'Nuevo Proyecto' }}
                                    </h2>
                                </div>
                            </div>
                            <button type="button" @click="emit('close')"
                                class="rounded-full p-2 text-gray-400 hover:bg-white hover:text-gray-600 transition">
                                <X :size="24" />
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar">

                        <div class="space-y-6">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                1. Identificación y Clasificación
                            </h3>
                            <div class="grid grid-cols-6 gap-4">
                                <div class="col-span-6">
                                    <InputLabel for="project_name" value="Nombre Completo del Proyecto" />
                                    <textarea id="project_name" v-model="form.project_name" rows="4"
                                        class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 uppercase text-sm"
                                        required></textarea>
                                    <InputError :message="form.errors.project_name" class="mt-1" />
                                </div>

                                <div class="col-span-3">
                                    <InputLabel for="project_code" value="Código de Proyecto (Automático)" />
                                    <TextInput id="project_code" :value="autoCodePreview" disabled
                                        class="mt-1 block w-full bg-gray-100 font-mono uppercase text-gray-500 cursor-not-allowed" />
                                    <InputError :message="form.errors.project_code" class="mt-1" />
                                </div>

                                <div class="col-span-3">
                                    <InputLabel for="short_name" value="Nombre Corto" />
                                    <TextInput id="short_name" v-model="form.short_name"
                                        class="mt-1 block w-full bg-gray-50/30 uppercase" />
                                    <InputError :message="form.errors.short_name" class="mt-1" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel for="type_id" value="Tipo de Proyecto" />
                                    <select id="type_id" v-model="form.type_id"
                                        class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                        <option :value="''" disabled>Seleccione...</option>
                                        <option v-for="t in types" :key="t.id" :value="Number(t.id)">{{ t.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.type_id" class="mt-1" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel for="status_id" value="Estado de Gestión" />
                                    <select id="status_id" v-model="form.status_id"
                                        class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                                        <option :value="''" disabled>Seleccione Estado</option>
                                        <option v-for="s in statuses" :key="s.id" :value="Number(s.id)">{{ s.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.status_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                2. Responsable del Proyecto
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div @click="form.company_id = null"
                                    :class="form.consortium_id ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-200'"
                                    class="p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-indigo-300">
                                    <div
                                        class="flex items-center gap-2 mb-2 font-bold text-gray-700 uppercase text-[10px]">
                                        Consorcio</div>
                                    <select v-model="form.consortium_id"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                        <option :value="null">Ninguno</option>
                                        <option v-for="c in consortia" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.consortium_id" class="mt-1" />
                                </div>
                                <div @click="form.consortium_id = null"
                                    :class="form.company_id ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-200'"
                                    class="p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-indigo-300">
                                    <div
                                        class="flex items-center gap-2 mb-2 font-bold text-gray-700 uppercase text-[10px]">
                                        Empresa</div>
                                    <select v-model="form.company_id" class="w-full border-gray-300 rounded-md text-sm">
                                        <option :value="null">Ninguna</option>
                                        <option v-for="co in companies" :key="co.id" :value="co.id">{{ co.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.company_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                3. Cronograma y Presupuesto
                            </h3>
                            <div class="grid grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div>
                                    <InputLabel value="Monto Contractual" />
                                    <input type="number" step="0.01" v-model="form.contractual_amount"
                                        class="w-full mt-1 border-gray-300 rounded-md text-sm font-bold" />
                                    <p class="text-[10px] text-gray-500 mt-1">{{ currency(form.contractual_amount) }}
                                    </p>
                                    <InputError :message="form.errors.contractual_amount" />
                                </div>
                                <div>
                                    <InputLabel value="Monto Proyectado" />
                                    <input type="number" step="0.01" v-model="form.projected_amount"
                                        class="w-full mt-1 border-gray-300 rounded-md text-sm font-bold text-indigo-600" />
                                    <p class="text-[10px] text-indigo-400 mt-1">{{ currency(form.projected_amount) }}
                                    </p>
                                    <InputError :message="form.errors.projected_amount" />
                                </div>
                                <div>
                                    <InputLabel value="Inicio" />
                                    <TextInput type="date" v-model="form.start_date" class="w-full mt-1" />
                                    <InputError :message="form.errors.start_date" />
                                </div>
                                <div>
                                    <InputLabel value="Fin Contractual" />
                                    <TextInput type="date" v-model="form.end_date_contractual"
                                        class="w-full mt-1 text-red-600" />
                                    <InputError :message="form.errors.end_date_contractual" />
                                </div>
                                <div>
                                    <InputLabel value="Fin Real" />
                                    <TextInput type="date" v-model="form.end_date_real" class="w-full mt-1" />
                                    <InputError :message="form.errors.end_date_real" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                4. Ubicación Geográfica
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <select v-model="form.department_id" @change="fetchProvinces(form.department_id)"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">Departamento</option>
                                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.department_id" />
                                </div>
                                <div>
                                    <select v-model="form.province_id" @change="fetchDistricts(form.province_id)"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">Provincia</option>
                                        <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                    <InputError :message="form.errors.province_id" />
                                </div>
                                <div>
                                    <select v-model="form.district_id"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">Distrito</option>
                                        <option v-for="dist in districts" :key="dist.id" :value="dist.id">{{ dist.name
                                        }}</option>
                                    </select>
                                    <InputError :message="form.errors.district_id" />
                                </div>
                                <div class="col-span-6">
                                    <TextInput v-model="form.address" placeholder="Dirección / Referencia técnica..."
                                        class="w-full uppercase text-xs" />
                                    <InputError :message="form.errors.address" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                5. Portada del Proyecto
                            </h3>
                            <div class="group relative">
                                <div
                                    class="w-full h-64 rounded-2xl bg-gray-100 border-2 border-dashed border-gray-300 overflow-hidden flex items-center justify-center transition-all group-hover:border-indigo-400 relative">
                                    <img v-if="photoPreview" :src="photoPreview"
                                        class="w-full h-full object-cover transition-transform group-hover:scale-105" />
                                    <div v-else class="text-center">
                                        <ImageIcon class="mx-auto text-gray-300" :size="48" />
                                        <p class="text-xs text-gray-400">Sin imagen seleccionada</p>
                                    </div>
                                    <div v-if="photoPreview"
                                        class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" @click="fileInput.click()"
                                            class="bg-white text-gray-900 px-4 py-2 rounded-lg text-xs font-bold">
                                            CAMBIAR FOTO
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <input type="file" ref="fileInput" @change="handlePhotoUpload" accept="image/*"
                                        class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer" />
                                    <InputError :message="form.errors.cover_image" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="Object.keys(form.errors).length > 0" class="bg-red-100 text-red-600 p-4 rounded-lg">
                        {{ form.errors }}
                    </div>

                    <div class="border-t border-gray-100 px-8 py-6 bg-white shrink-0">
                        <div class="flex items-center justify-between">
                            <button type="button" @click="emit('close')"
                                class="text-xs font-black text-gray-500 hover:text-red-500 uppercase">Cancelar</button>
                            <PrimaryButton @click="emit('submit')" :disabled="form.processing" class="!px-10 !py-3">
                                {{ editMode ? 'Guardar Cambios' : 'Registrar Proyecto' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
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