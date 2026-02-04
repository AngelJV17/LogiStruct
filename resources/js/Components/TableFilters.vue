<script setup>
import { Search } from 'lucide-vue-next';
defineProps({
    modelValue: String,
    perPage: [String, Number]
});
defineEmits(['update:modelValue', 'update:perPage']);
</script>

<template>
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <Search :size="18" />
            </span>
            <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" type="search"
                placeholder="Buscar..."
                class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
        </div>
        <div class="flex items-center text-sm text-gray-600">
            <span>Mostrar</span>
            <select :value="perPage" @change="$emit('update:perPage', $event.target.value)"
                class="mx-2 rounded-lg border-gray-300 py-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span>registros</span>
        </div>
    </div>
</template>