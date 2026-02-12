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
        return [
            'project_code' => 'required|string|max:20|unique:projects,project_code,' . $this->project?->id,
            'project_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50|unique:projects,short_name,' . $this->project?->id,
            
            // Relaciones con Global Parameters
            'type_id' => 'required|exists:global_parameters,id',
            'status_id' => 'required|exists:global_parameters,id',

            // Financiero
            'contractual_amount' => 'required|numeric|min:0',
            'projected_amount' => 'nullable|numeric|min:0',

            // Ubigeo
            'department_id' => 'required|exists:ubigeo_peru_departments,id',
            'province_id' => 'required|exists:ubigeo_peru_provinces,id',
            'district_id' => 'required|exists:ubigeo_peru_districts,id',
            'address' => 'nullable|string|max:500',

            // Entidad Responsable (Lógica Senior: Uno de los dos es obligatorio)
            'consortium_id' => 'required_without:company_id|nullable|exists:consortia,id',
            'company_id' => 'required_without:consortium_id|nullable|exists:companies,id',
            
            'start_date' => 'required|date',
            'end_date_contractual' => 'required|date|after_or_equal:start_date',
            'cover_image' => 'nullable|image|max:2048', // 2MB max
        ];
    }
}
