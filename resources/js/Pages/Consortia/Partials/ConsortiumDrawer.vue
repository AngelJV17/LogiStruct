<script setup>
import { computed, ref, watch } from 'vue';
import { X, Camera, Briefcase, Plus, Trash2, AlertCircle, CheckCircle } from 'lucide-vue-next';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: Boolean,
    editMode: Boolean,
    form: Object,
    allCompanies: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'submit', 'updatePhoto']);

// Lógica de participación
const totalPercentage = computed(() => {
    return (props.form.selected_companies || []).reduce((sum, item) => {
        return sum + (parseFloat(item.percentage) || 0);
    }, 0);
});

const isPercentageValid = computed(() => totalPercentage.value === 100);

const addCompanyRow = () => {
    props.form.selected_companies.push({ company_id: '', percentage: '0' });
};

const removeCompanyRow = (index) => {
    props.form.selected_companies.splice(index, 1);
};

// Manejo de Logo
const photoPreview = ref(null);
const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => photoPreview.value = e.target.result;
        reader.readAsDataURL(file);
        emit('updatePhoto', file);
    }
};

const duplicateCompanyIds = computed(() => {
    const ids = props.form.selected_companies
        .map(c => c.company_id)
        .filter(id => id !== null && id !== '');

    // Retorna solo los IDs que aparecen más de una vez
    return ids.filter((id, index) => ids.indexOf(id) !== index);
});

// Esto nos servirá para bloquear el botón de guardado más adelante
const hasDuplicates = computed(() => duplicateCompanyIds.value.length > 0);

// Validar que haya al menos 2 empresas con IDs seleccionados
const hasMinimumSocioCount = computed(() => {
    return props.form.selected_companies.filter(c => c.company_id !== '').length >= 2;
});

// Actualizamos la validación del botón (debe cumplir todas estas condiciones)
const isFormInvalid = computed(() => {
    return !isPercentageValid.value || // Debe sumar 100%
        !hasMinimumSocioCount.value || // Debe haber al menos 2 socios
        hasDuplicates.value || // No debe haber repetidos
        props.form.processing; // No debe estar enviando
});

// Cerrar con la tecla ESC
watch(() => props.show, (val) => {
    if (val) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = 'auto';
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-2xl transform transition duration-500 ease-in-out">
                <form @submit.prevent="emit('submit')" class="flex h-full flex-col bg-white shadow-2xl">

                    <div class="bg-gray-50 px-6 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-600 p-2 rounded-lg text-white">
                                    <Briefcase :size="20" />
                                </div>
                                <h2 class="text-xl font-bold text-gray-800 tracking-tight">
                                    {{ editMode ? 'Editar Consorcio' : 'Nuevo Consorcio' }}
                                </h2>
                            </div>
                            <button type="button" @click="emit('close')"
                                class="rounded-full p-2 text-gray-400 hover:bg-white hover:text-gray-600 transition">
                                <X :size="24" />
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 space-y-10">

                        <div class="space-y-6">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                1. Identidad Corporativa
                            </h3>
                            <div class="flex items-center gap-8">
                                <div class="relative group h-28 w-28 shrink-0">
                                    <div
                                        class="h-full w-full rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden transition-all group-hover:border-indigo-400">
                                        <img v-if="photoPreview || (editMode && form.url_logo && typeof form.url_logo === 'string')"
                                            :src="photoPreview || `/storage/${form.url_logo}`"
                                            class="h-full w-full object-cover" />
                                        <Camera v-else class="text-gray-300" :size="32" />
                                    </div>
                                    <label
                                        class="absolute inset-0 bg-indigo-600/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center cursor-pointer rounded-[2rem] transition-all duration-300 transform scale-95 group-hover:scale-100">
                                        <Camera class="text-white mb-1" :size="24" />
                                        <span class="text-[10px] text-white font-bold uppercase">Subir Logo</span>
                                        <input type="file" class="hidden" @change="handlePhotoChange"
                                            accept="image/*" />
                                    </label>
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <InputLabel for="name" value="Razón Social del Consorcio" />
                                        <TextInput id="name" v-model="form.name" type="text"
                                            class="mt-1 block w-full bg-gray-50/30 focus:bg-white"
                                            placeholder="Ej: Consorcio Empresarial" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div>
                                        <InputLabel for="ruc" value="RUC" />
                                        <TextInput id="ruc" v-model="form.ruc" type="text"
                                            class="mt-1 block w-full bg-gray-50/30" maxlength="11"
                                            placeholder="20XXXXXXXXX" />
                                        <InputError :message="form.errors.ruc" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b pb-2">
                                <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest">
                                    2. Estructura de Participación
                                </h3>
                                <button type="button" @click="addCompanyRow"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                    <Plus :size="14" /> AGREGAR SOCIO
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div v-for="(item, index) in form.selected_companies" :key="index"
                                    class="flex items-center gap-3 p-4 rounded-2xl border transition-all hover:shadow-md relative"
                                    :class="duplicateCompanyIds.includes(item.company_id)
                                        ? 'bg-red-50 border-red-200'
                                        : 'bg-gray-50 border-gray-100 hover:bg-white'">

                                    <div v-if="duplicateCompanyIds.includes(item.company_id)"
                                        class="absolute -top-2 left-4 px-2 py-0.5 bg-red-600 text-[10px] text-white font-black rounded uppercase shadow-sm">
                                        Empresa repetida
                                    </div>

                                    <div class="flex-1">
                                        <select v-model="item.company_id"
                                            class="w-full border-none bg-transparent focus:ring-0 text-sm font-medium"
                                            :class="duplicateCompanyIds.includes(item.company_id) ? 'text-red-700' : 'text-gray-700'">
                                            <option value="" disabled>Seleccionar integrante...</option>

                                            <option v-for="company in allCompanies" :key="company.id"
                                                :value="company.id"
                                                :disabled="form.selected_companies.some(s => s.company_id === company.id && s !== item)">
                                                {{ company.name }}
                                                {{form.selected_companies.some(s => s.company_id === company.id && s
                                                    !== item) ? ' (Seleccionada)' : ''}}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="w-24 relative">
                                        <input v-model="item.percentage" type="number" step="0.01"
                                            class="w-full border-none bg-transparent focus:ring-0 text-right font-bold text-indigo-600" />
                                        <span class="absolute right-0 top-2 text-xs text-gray-400 font-bold">%</span>
                                    </div>

                                    <button @click="removeCompanyRow(index)" type="button"
                                        class="text-gray-300 hover:text-red-500 transition-colors">
                                        <Trash2 :size="18" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.selected_companies" />
                            </div>

                            <div class="p-4 rounded-2xl border flex flex-col gap-2 transition-colors"
                                :class="(isPercentageValid && hasMinimumSocioCount) ? 'bg-green-50 border-green-100' : 'bg-amber-50 border-amber-100'">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm font-bold"
                                        :class="isPercentageValid ? 'text-green-700' : 'text-amber-700'">
                                        <CheckCircle v-if="isPercentageValid" :size="18" />
                                        <AlertCircle v-else :size="18" />
                                        Participación Total: {{ totalPercentage }}%
                                    </div>
                                    <div v-if="!isPercentageValid"
                                        class="text-[10px] uppercase font-black text-amber-600 tracking-tighter">
                                        Debe ser exactamente 100%
                                    </div>
                                </div>

                                <div v-if="!hasMinimumSocioCount"
                                    class="flex items-center gap-2 text-[11px] font-bold text-amber-600 border-t border-amber-200 pt-2 italic">
                                    <AlertCircle :size="14" />
                                    Un consorcio requiere al menos 2 empresas integrantes.
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                3. Representación Legal
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <InputLabel for="legal_representative" value="Nombre Completo" />
                                    <TextInput id="legal_representative" v-model="form.legal_representative"
                                        class="mt-1 block w-full bg-gray-50/30" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel for="email" value="Correo Electrónico del Representante" />
                                    <TextInput id="email" v-model="form.email" type="email"
                                        class="mt-1 block w-full bg-gray-50/30" />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div>
                                    <InputLabel for="representative_dni" value="DNI" />
                                    <TextInput id="representative_dni" v-model="form.representative_dni" maxlength="8"
                                        class="mt-1 block w-full bg-gray-50/30" />
                                </div>
                                <div>
                                    <InputLabel for="representative_phone" value="Teléfono" />
                                    <TextInput id="representative_phone" v-model="form.representative_phone"
                                        class="w-full mt-1 bg-gray-50/50" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 px-8 py-6 bg-white">
                        <div class="flex items-center justify-between">
                            <button type="button" @click="emit('close')"
                                class="text-xs font-black text-gray-500 hover:text-red-500 tracking-tighter transition-colors uppercase">
                                Cancelar Registro
                            </button>
                            <PrimaryButton :disabled="form.processing" class="!px-10 !py-3 shadow-lg shadow-indigo-100">
                                {{ editMode ? 'Guardar Cambios' : 'Finalizar Registro' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>