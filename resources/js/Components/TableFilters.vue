<script setup>
import { computed } from 'vue';
import { Search, Filter, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    modelValue: String,
    perPage: [String, Number]
});

const emit = defineEmits(['update:modelValue', 'update:perPage']);

const search = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});
</script>

<template>
    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 md:gap-4 w-full">

        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Search class="text-slate-400" :size="18" />
            </div>
            <input v-model="search" type="search" placeholder="Escribe para buscar..."
                class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm text-slate-700 placeholder:text-slate-400 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all duration-200 shadow-sm outline-none" />
        </div>

        <div
            class="flex items-center justify-center bg-slate-50 border border-slate-200/60 p-1 rounded-2xl shrink-0 shadow-sm self-center md:self-auto">

            <div
                class="flex items-center gap-1.5 px-3 text-[10px] font-black text-slate-400 uppercase tracking-widest shrink-0">
                <Filter :size="14" />
                <span class="hidden xs:inline">Mostrar</span>
            </div>

            <div class="relative">
                <select :value="perPage" @change="emit('update:perPage', $event.target.value)"
                    class="appearance-none pl-3 pr-8 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer transition-all outline-none">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <ChevronDown class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"
                    :size="14" />
            </div>

            <div class="px-3 text-[10px] font-black text-slate-400 uppercase tracking-widest shrink-0">
                Filas
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Clase de utilidad para pantallas muy pequeñas (iPhone SE, etc) */
@media (min-width: 400px) {
    .xs\:inline {
        display: inline;
    }
}

@media (max-width: 399px) {
    .xs\:hidden {
        display: none;
    }
}
</style>