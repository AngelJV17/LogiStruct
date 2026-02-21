<?php
namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class SaveProjectRequest extends FormRequest
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
        /** * SENIOR TIP:
         * No uses $this->id, usa el nombre del parámetro en la ruta.
         * Si tu ruta es /projects/{project}, usa $this->route('project')?->id.
         * Esto asegura que la excepción del 'unique' funcione incluso si el input
         * del formulario está vacío o mal nombrado.
         */
        $projectId = $this->route('project')?->id;

        return [
            // 1. Identificación: Código autogenerado o manual
            'project_code' => [
                'nullable',
                'string',
                'max:20',
                "unique:projects,project_code,{$projectId}",
            ],

            'project_name' => 'required|string|max:255',
            'short_name'   => "required|string|max:50|unique:projects,short_name,{$projectId}",

            // 2. Relaciones: Validamos existencia
            'type_id'              => 'required|exists:global_parameters,id',
            'status_id'            => 'required|exists:global_parameters,id',

            // 3. Financiero: Aseguramos precisión decimal
            'contractual_amount'   => 'required|numeric|min:0|between:0,9999999999.99',
            'projected_amount'     => 'nullable|numeric|min:0|between:0,9999999999.99',

            // 4. Ubigeo: Estructura geográfica obligatoria
            'department_id'        => 'required|exists:ubigeo_peru_departments,id',
            'province_id'          => 'required|exists:ubigeo_peru_provinces,id',
            'district_id'          => 'required|exists:ubigeo_peru_districts,id',
            'address'              => 'nullable|string|max:500',

            // 5. Entidad Responsable: Lógica condicional Senior
            // Uno de los dos debe estar presente, pero no necesariamente ambos.
            'consortium_id'        => 'required_without:company_id|nullable|exists:consortia,id',
            'company_id'           => 'required_without:consortium_id|nullable|exists:companies,id',

            // 6. Cronograma
            'start_date'           => 'required|date',
            'end_date_contractual' => 'required|date|after_or_equal:start_date',

            // 7. Imagen: Manejo flexible
            // Si es un string (la URL vieja), lo ignoramos. Si es un File, lo validamos.
            'cover_image'          => $this->hasFile('cover_image')
                ? 'image|mimes:jpg,jpeg,png|max:2048'
                : 'nullable',
        ];
    }

/**
 * Mensajes personalizados para una UX de calidad
 */
    public function messages(): array
    {
        return [
            'consortium_id.required_without'      => 'Debe seleccionar una Empresa o un Consorcio responsable.',
            'company_id.required_without'         => 'Debe seleccionar una Empresa o un Consorcio responsable.',
            'end_date_contractual.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ];
    }
}
