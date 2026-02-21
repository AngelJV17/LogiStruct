<?php
namespace App\Http\Requests\Consortium;

use Illuminate\Foundation\Http\FormRequest;

class SaveConsortiumRequest extends FormRequest
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
        $consortiumId = $this->route('consortium')?->id;

        return [
            'name'     => 'required|string|max:255',
            
            // 'unique:tabla,columna,except_id'
            'ruc'      => 'required|digits:11|unique:consortia,ruc,' . $consortiumId,

            // El logo es opcional en update y solo debe validarse si es un archivo
            'url_logo' => $this->hasFile('url_logo') ? 'image|mimes:jpg,jpeg,png|max:2048' : 'nullable',

            'legal_representative'            => 'required|string|max:255',
            'representative_dni'              => 'required|digits:8',
            'representative_email'            => 'nullable|email',
            'representative_phone'            => 'nullable|string',
            'selected_companies'              => 'required|array|min:2',
            'selected_companies.*.company_id' => 'required|exists:companies,id|distinct',
            'selected_companies.*.percentage' => 'required|numeric|min:0.01|max:100',
        ];
    }
}
