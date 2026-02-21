<?php
namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class SaveCompanyRequest extends FormRequest
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
        $companyId = $this->route('company')?->id;

        return [
            'name' => 'required|string|max:255',
            'ruc'  => "required|digits:11|unique:companies,ruc,{$companyId}",

            // REGLA CRÍTICA:
            'url_logo'             => $this->hasFile('url_logo')
                ? 'image|mimes:jpg,jpeg,png|max:2048'
                : 'nullable',
                
            'email'                => 'nullable|email',
            'phone'                => 'nullable|string|max:20',
            'address'              => 'nullable|string|max:500',
            'legal_representative' => 'nullable|string|max:255',
            'representative_dni'   => 'nullable|digits:8',
            'representative_phone' => 'nullable|string|max:20',
            'issues_payment_order' => 'boolean',
        ];
    }
}
