<?php
namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWorkerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Capturamos el UUID desde la ruta
        $workerUuid = $this->route('worker');

        // Si Laravel ya inyectó el objeto, extraemos el UUID, si no, usamos el string
        $uuid = is_object($workerUuid) ? $workerUuid->uuid : $workerUuid;

        return [
            'document_type_id'   => 'required|exists:global_parameters,id',
            'document_number'    => [
                'required',
                'string',
                'max:20',
                // Buscamos por la columna 'uuid' para ignorar correctamente
                Rule::unique('workers')->ignore($uuid, 'uuid'),
            ],

            // Nombres
            'first_name'         => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'required|string|max:255',

            // Datos Personales
            'birth_date'         => 'nullable|date',
            'gender_id'          => 'nullable|exists:global_parameters,id',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:255',

            // Clasificación Laboral
            'worker_type_id'     => 'required|exists:global_parameters,id',
            'position_id'        => 'required|exists:positions,id',
            'project_id'         => 'nullable|exists:projects,id',
            'company_id'         => 'required|exists:companies,id',

            // Salarios
            'daily_salary'       => 'numeric|min:0',
            'monthly_salary'     => 'numeric|min:0',
            'payment_type_id'    => 'nullable|exists:global_parameters,id',

            // Bancos y Pensiones
            'bank_id'            => 'nullable|exists:banks,id',
            'pension_system_id'  => 'nullable|exists:pension_systems,id',
            'bank_account'       => 'nullable|string|max:30',
            'cci'                => 'nullable|string|max:30',
            'cuspp'              => 'nullable|string|max:20',

            // Control
            'hire_date'          => 'nullable|date',
            'is_active'          => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'document_number.unique'      => 'Este número de documento ya está registrado.',
            'document_number.required'    => 'El número de documento es obligatorio.',
            'first_name.required'         => 'El nombre es obligatorio.',
            'last_name_paternal.required' => 'El apellido paterno es obligatorio.',
            'last_name_maternal.required' => 'El apellido materno es obligatorio.',
            'hire_date.required'          => 'La fecha de ingreso es obligatoria.',
            'position_id.required'        => 'Debe asignar un cargo o categoría.',
            'company_id.required'         => 'Debe indicar la empresa empleadora.',
        ];
    }
}
