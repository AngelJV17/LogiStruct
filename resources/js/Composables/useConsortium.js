import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";

export function useConsortium() {
    const showingModal = ref(false);
    const editMode = ref(false);

    const form = useForm({
        id: null,
        ruc: "",
        name: "",
        url_logo: "",
        legal_representative: "",
        representative_dni: "",
        representative_phone: "",
        representative_email: "",
        selected_companies: [],
    });

    const openModal = (item = null) => {
        editMode.value = !!item;
        if (item) Object.assign(form, item);
        else form.reset();
        showingModal.value = true;
    };

    return { form, showingModal, editMode, openModal };
}
