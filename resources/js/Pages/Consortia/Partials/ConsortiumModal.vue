<script setup>
import { computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Briefcase, Camera } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    editMode: Boolean,
    form: Object,
    allCompanies: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'submit']);

// --- LÓGICA DE NEGOCIO ---

// Cálculo dinámico del total de participación
const totalPercentage = computed(() => {
    return (props.form.selected_companies || []).reduce((sum, item) => {
        return sum + (parseFloat(item.percentage) || 0);
    }, 0);
});

// Validación: ¿Es válida la suma?
const isPercentageValid = computed(() => totalPercentage.value === 100);

const addCompanyRow = () => {
    props.form.selected_companies.push({
        company_id: '',
        percentage: '0'
    });
};

const removeCompanyRow = (index) => {
    props.form.selected_companies.splice(index, 1);
};

// Resetear errores específicos al cambiar valores
watch(() => props.form.selected_companies, () => {
    props.form.clearErrors('selected_companies');
}, { deep: true });

</script>

<template>
    <Modal :show="show" @close="$emit('close')" max-width="2xl">
        <form @submit.prevent="$emit('submit')" class="p-6">
            <div class="flex items-center gap-3 mb-6 border-b pb-4">
                <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600">
                    <Briefcase :size="24" />
                </div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ editMode ? 'Editar' : 'Nuevo' }} Consorcio
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <InputLabel value="Logo del Consorcio" class="mb-2" />
                    <div class="flex items-center gap-5">
                        <div class="relative group">
                            <div
                                class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center">
                                <img v-if="photoPreview || (editMode && form.url_logo)"
                                    :src="photoPreview || `/storage/${form.url_logo}`"
                                    class="h-full w-full object-cover" />
                                <Briefcase v-else class="text-gray-300" :size="32" />
                            </div>
                            <label
                                class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-xl">
                                <Camera class="text-white" :size="20" />
                                <input type="file" class="hidden" @change="e => $emit('updatePhoto', e.target.files[0])"
                                    accept="image/*" />
                            </label>
                        </div>
                        <div class="text-xs text-gray-500">
                            <p class="font-bold text-gray-700">Subir imagen corporativa</p>
                            <p>Formatos permitidos: PNG, JPG o WEBP.</p>
                        </div>
                    </div>
                </div>

                <div class="col-span-1">
                    <InputLabel for="ruc" value="RUC" />
                    <TextInput id="ruc" v-model="form.ruc" type="text" class="mt-1 block w-full" maxlength="11" />
                    <InputError :message="form.errors.ruc" />
                </div>

                <div class="col-span-1">
                    <InputLabel for="name" value="Razón Social" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="md:col-span-2 mt-2 pt-4 border-t border-gray-100">
                    <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4">Datos del Representante
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="rep_name" value="Nombre Completo" />
                            <TextInput id="rep_name" v-model="form.legal_representative" type="text"
                                class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="rep_dni" value="DNI" />
                            <TextInput id="rep_dni" v-model="form.representative_dni" type="text"
                                class="mt-1 block w-full" maxlength="8" />
                        </div>
                        <div>
                            <InputLabel for="rep_email" value="Correo Electrónico" />
                            <TextInput id="rep_email" v-model="form.representative_email" type="email"
                                class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="rep_phone" value="Teléfono" />
                            <TextInput id="rep_phone" v-model="form.representative_phone" type="text"
                                class="mt-1 block w-full" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-5">
                <button type="button" @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-600">Cancelar</button>
                <PrimaryButton :disabled="form.processing">
                    {{ editMode ? 'Actualizar Datos' : 'Registrar Consorcio' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>