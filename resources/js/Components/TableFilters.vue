<script setup>
import { computed } from 'vue';
import { Search, Filter } from 'lucide-vue-next';

// --- PROPIEDADES ---
const props = defineProps({
    modelValue: String,    // Valor del buscador
    perPage: [String, Number] // Cantidad de registros
});

// --- EVENTOS ---
const emit = defineEmits(['update:modelValue', 'update:perPage']);

// --- SINCRONIZACIÓN ---
const search = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});
</script>

<template>
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div class="relative w-full md:w-96">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
            <input v-model="search" type="search" placeholder="Buscar..."
                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm" />
        </div>

        <div class="flex items-center text-sm text-gray-600">
            <div class="flex items-center gap-2 text-xs font-black text-slate-500 uppercase tracking-widest mr-2">
                <Filter :size="14" />
                Mostrar
            </div>

            <select :value="perPage" @change="emit('update:perPage', $event.target.value)"
                class="mx-2 rounded-xl border-gray-200 py-1.5 pl-3 pr-8 text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer shadow-sm">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>

            <div class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">
                registros
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Evita el contorno azul nativo y usa el del anillo de Tailwind */
select {
    outline: none;
    background-position: right 0.5rem center;
}
</style>