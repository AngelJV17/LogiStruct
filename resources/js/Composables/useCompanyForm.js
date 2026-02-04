import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

export function useCompanyForm() {
    const photoPreview = ref(null);
    const showingModal = ref(false);
    const editMode = ref(false);

    const form = useForm({
        id: null,
        ruc: "",
        name: "",
        email: "",
        phone: "",
        address: "",
        legal_representative: "",
        representative_dni: "",
        representative_phone: "",
        issues_payment_order: false,
        url_logo: null,
    });

    const updatePhotoPreview = (file) => {
        if (!file) return;
        form.url_logo = file;
        const reader = new FileReader();
        reader.onload = (e) => photoPreview.value = e.target.result;
        reader.readAsDataURL(file);
    };

    const resetForm = () => {
        form.reset();
        photoPreview.value = null;
        form.clearErrors();
    };

    return { form, photoPreview, showingModal, editMode, updatePhotoPreview, resetForm };
}