<script setup>
import { SearchX } from 'lucide-vue-next';
defineProps({ search: String, title: String });
</script>

<template>
    <div class="flex flex-col items-center py-12">
        <div class="bg-gray-100 p-4 rounded-full mb-4">
            <SearchX class="text-gray-400" :size="40" />
        </div>
        <h3 class="text-lg font-medium text-gray-900">
            {{ search ? 'No hay coincidencias para "' + search + '"' : title }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">
            {{ search ? 'Intenta con otros términos.' : 'Comienza agregando un nuevo registro.' }}
        </p>
    </div>
</template>