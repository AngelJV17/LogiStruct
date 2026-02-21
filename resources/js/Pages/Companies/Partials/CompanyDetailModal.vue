<script setup>
import Modal from '@/Components/Modal.vue';
import { Briefcase, Contact, Mail, Phone, MapPin, CreditCard, User, IdCard } from 'lucide-vue-next';

defineProps({
    show: Boolean,
    company: Object,
});

defineEmits(['close']);
</script>

<template>
    <Modal :show="show" @close="$emit('close')" max-width="md">
        <div class="p-6" v-if="company">
            <div class="flex items-center gap-4 mb-6 border-b pb-4">
                <div class="bg-indigo-50 p-2 rounded-lg border border-indigo-100">
                    <img v-if="company.url_logo" :src="`/storage/${company.url_logo}`"
                        class="h-16 w-16 rounded-md object-cover shadow-sm">
                    <Briefcase v-else class="h-16 w-16 text-indigo-500 p-2" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 leading-tigh">{{ company.name }}</h3>
                    <p class="text-sm text-indigo-600 font-medium">RUC: {{ company.ruc }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <Mail class="text-indigo-500 mt-1" :size="18" />
                    <div>
                        <p class="text-xs uppercase text-gray-400 font-bold">Correo Electrónico</p>
                        <p class="text-sm text-gray-700">{{ company.email || 'No disponible' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <Phone class="text-indigo-500 mt-1" :size="18" />
                    <div>
                        <p class="text-xs uppercase text-gray-400 font-bold">Teléfono Central</p>
                        <p class="text-sm text-gray-700">{{ company.phone || 'No disponible' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <MapPin class="text-indigo-500 mt-1" :size="18" />
                    <div>
                        <p class="text-xs uppercase text-gray-400 font-bold">Dirección</p>
                        <p class="text-sm text-gray-700">{{ company.address || 'No disponible' }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs uppercase text-gray-400 font-bold mb-3 flex items-center gap-2">
                        <User :size="14" /> Representante Legal
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <IdCard class="text-gray-400" :size="16" />
                            <p class="text-sm text-gray-700 font-semibold">
                                {{ company.legal_representative || 'No registrado' }}
                                <span class="block text-xs font-normal text-gray-500">DNI: {{
                                    company.representative_dni || '---' }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <Mail class="text-gray-400" :size="16" />
                            <p class="text-sm text-gray-700">{{ company.representative_email || 'Sin correo' }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <Phone class="text-gray-400" :size="16" />
                            <p class="text-sm text-gray-700">{{ company.representative_phone || 'Sin teléfono' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button @click="$emit('close')"
                    class="w-full bg-gray-800 text-white px-6 py-2.5 rounded-lg hover:bg-gray-700 transition font-medium">
                    Cerrar
                </button>
            </div>
        </div>
    </Modal>
</template>