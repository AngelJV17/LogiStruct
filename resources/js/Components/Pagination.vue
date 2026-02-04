<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: Array });
</script>

<template>
    <div v-if="links.length > 3" class="mt-6 flex justify-center">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null"
                    class="mr-1 mb-1 px-4 py-3 text-sm text-gray-400 border rounded bg-gray-50" v-html="link.label" />

                <Link v-else
                    class="mr-1 mb-1 px-4 py-3 text-sm border rounded hover:bg-white focus:border-indigo-500 transition-all shadow-sm"
                    :class="{ 'bg-indigo-600 text-white font-bold border-indigo-600': link.active }" :href="link.url"
                    preserve-scroll>
                    <span v-html="link.label"></span>
                </Link>
            </template>
        </div>
    </div>
</template>