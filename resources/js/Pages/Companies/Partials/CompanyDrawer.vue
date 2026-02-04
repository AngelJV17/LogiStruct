<script setup>
import { computed, ref, watch } from 'vue';
import { X, Camera, Building2 } from 'lucide-vue-next';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: Boolean,
    editMode: Boolean,
    form: Object,
});

const emit = defineEmits(['close', 'submit', 'updatePhoto']);

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
                                    <Building2 :size="24" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-gray-800 tracking-tight">
                                        {{ editMode ? 'Editar Empresa' : 'Nueva Empresa' }}
                                    </h2>
                                </div>
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
                                1. Datos Fiscales
                            </h3>

                            <div class="flex gap-8">
                                <div class="relative group h-32 w-32 shrink-0">
                                    <div
                                        class="h-full w-full rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden transition-all group-hover:border-indigo-400">
                                        <img v-if="photoPreview || (editMode && form.url_logo)"
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

                                <div class="flex-1 grid grid-cols-1 gap-4">
                                    <div>
                                        <InputLabel for="name" value="Razón Social" />
                                        <TextInput id="name" v-model="form.name" type="text"
                                            class="mt-1 block w-full bg-gray-50/30 focus:bg-white"
                                            placeholder="Ej: Corporación Inmobiliaria S.A.C" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="ruc" value="Número RUC" />
                                            <TextInput id="ruc" v-model="form.ruc" type="text"
                                                class="mt-1 block w-full bg-gray-50/30" maxlength="11"
                                                placeholder="20XXXXXXXXX" />
                                            <InputError :message="form.errors.ruc" />
                                        </div>
                                        <div>
                                            <InputLabel for="phone" value="Teléfono Corporativo" />
                                            <TextInput id="phone" v-model="form.phone" type="text"
                                                class="mt-1 block w-full bg-gray-50/30" maxlength="9"
                                                placeholder="999 000 000" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                2. Localización y Contacto
                            </h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <InputLabel for="address" value="Dirección Legal" />
                                    <TextInput id="address" v-model="form.address" type="text"
                                        class="mt-1 block w-full bg-gray-50/30" />
                                </div>
                                <div>
                                    <InputLabel for="email" value="Correo de Facturación / Contacto" />
                                    <TextInput id="email" v-model="form.email" type="email"
                                        class="mt-1 block w-full bg-gray-50/30" placeholder="admin@empresa.com" />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b pb-2">
                                3. Representación Legal
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <InputLabel for="legal_representative" value="Nombre Completo del Representante" />
                                    <TextInput id="legal_representative" v-model="form.legal_representative" type="text"
                                        class="mt-1 block w-full bg-gray-50/30"/>
                                    <InputError :message="form.errors.legal_representative" />
                                </div>
                                <div>
                                    <InputLabel for="representative_dni" value="DNI / CE" />
                                    <TextInput id="representative_dni" v-model="form.representative_dni" maxlength="8"
                                        class="mt-1 block w-full bg-gray-50/30" />
                                </div>
                                <div>
                                    <InputLabel for="representative_phone" value="Celular Personal" />
                                    <TextInput id="representative_phone" v-model="form.representative_phone" class="w-full mt-1 bg-gray-50/30" />
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