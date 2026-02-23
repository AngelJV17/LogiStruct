<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Settings2, Layers } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    editMode: Boolean,
    form: Object,
    parentOptions: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'submit']);

const filteredParentOptions = computed(() => {
    if (!props.editMode) return props.parentOptions;
    return props.parentOptions.filter(opt => opt.id !== props.form.id);
});
</script>

<template>
    <Modal :show="show" @close="$emit('close')" max-width="2xl">
        <form @submit.prevent="$emit('submit')" class="p-6">

            <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                <div class="bg-indigo-600 p-2 rounded-xl text-white">
                    <Settings2 :size="20" />
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-tight">
                        {{ editMode ? 'Editar' : 'Nuevo' }} <span class="text-indigo-600">Parámetro</span>
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="col-span-1">
                    <InputLabel for="group" value="Grupo" class="ml-1 mb-1 text-[10px]" />
                    <TextInput id="group" v-model="form.group" type="text" class="block w-full uppercase text-xs"
                        placeholder="EJ: CATEGORIA_TRABAJADOR" required />
                    <InputError :message="form.errors.group" class="mt-1" />
                </div>

                <div class="col-span-1">
                    <InputLabel for="name" value="Nombre del Parámetro" class="ml-1 mb-1 text-[10px]" />
                    <TextInput id="name" v-model="form.name" type="text" class="block w-full text-xs"
                        placeholder="Nombre visible" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>

                <div class="col-span-1">
                    <div class="flex items-center gap-2 mb-1 ml-1">
                        <Layers class="text-indigo-500" :size="12" />
                        <InputLabel for="parent_id" value="Parámetro Padre" class="text-[10px]" />
                    </div>
                    <select id="parent_id" v-model="form.parent_id"
                        class="w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-xs font-bold py-2">
                        <option value="">--- Ninguno (Nivel 1) ---</option>
                        <option v-for="opt in filteredParentOptions" :key="opt.id" :value="opt.id">
                            {{ opt.group }} | {{ opt.name }}
                        </option>
                    </select>
                </div>

                <div class="col-span-1 flex items-end">
                    <div
                        class="flex items-center justify-between w-full p-2 bg-slate-50 rounded-xl border border-slate-100 h-[42px]">
                        <span class="text-[10px] font-black text-slate-500 uppercase ml-2">¿Activo?</span>
                        <label class="relative inline-flex items-center cursor-pointer scale-90">
                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                            <div
                                class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600">
                            </div>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="description" value="Descripción" class="ml-1 mb-1 text-[10px]" />
                    <textarea id="description" v-model="form.description" rows="2"
                        class="w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm text-xs font-medium py-2 resize-none"
                        placeholder="Notas adicionales..."></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end items-center gap-4 border-t pt-4">
                <button type="button" @click="$emit('close')"
                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">
                    Cancelar
                </button>
                <PrimaryButton :disabled="form.processing" class="rounded-xl px-6 py-2 text-[10px]">
                    {{ editMode ? 'Actualizar' : 'Guardar' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>