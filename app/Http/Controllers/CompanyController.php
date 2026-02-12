<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()
    ->when($request->input('search'), function ($query, $search) {
        $query->where('name', 'like', "%{$search}%")
            ->orWhere('ruc', 'like', "%{$search}%");
    }) // <-- Verifica que este paréntesis esté cerrado
    ->latest()
    ->paginate($request->input('perPage', 10))
    ->withQueryString();

return Inertia::render('Companies/Index', [
    'companies' => $companies,
    'filters'   => $request->only(['search', 'perPage']),
]);

    }

    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('url_logo')) {
            $data['url_logo'] = $request->file('url_logo')->store('companies_logos', 'public');
        }

        Company::create($data);
        return redirect()->route('companies.index');
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'ruc'                  => 'required|digits:11|unique:companies,ruc,' . $company->id,
            'name'                 => 'required|string|max:255',
            'email'                => 'nullable|email',
            'phone'                => 'nullable|string',
            'address'              => 'nullable|string',
            'legal_representative' => 'nullable|string',
            'representative_dni'   => 'nullable|digits:8',
            'representative_phone' => 'nullable|string',
            'issues_payment_order' => 'required|boolean',
            'url_logo'             => 'nullable|image|max:2048',
        ]);

        // Lógica inteligente para el logo:
        if ($request->hasFile('url_logo')) {
            // Si el usuario subió un archivo nuevo, borramos el viejo y guardamos el nuevo
            if ($company->url_logo) {
                Storage::disk('public')->delete($company->url_logo);
            }
            $validated['url_logo'] = $request->file('url_logo')->store('companies_logos', 'public');
        } else {
            // SI NO HAY ARCHIVO NUEVO: Quitamos el campo del array
            // para que Laravel NO intente actualizarlo a null.
            unset($validated['url_logo']);
        }

        $company->update($validated);

        return redirect()->route('companies.index');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();
        return response()->json(null, 204);
    }
}
