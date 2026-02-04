<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Consortium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConsortiumController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'perPage']);
        $perPage = $request->perPage ?? 10;

        $consortia = Consortium::query()
            ->with('companies') // Cargamos los socios para el Drawer y la tabla
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Consortia/Index', [
            'consortia' => $consortia,
            'companies' => Company::select('id', 'name')->get(), // Para el select del Drawer
            'filters'   => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($request, $validated) {
            // 1. Manejo del Logo
            if ($request->hasFile('url_logo')) {
                $validated['url_logo'] = $request->file('url_logo')->store('consortia_logos', 'public');
            }

            // 2. Crear Consorcio
            $consortium = Consortium::create($validated);

            // 3. Sincronizar Socios (Attach con porcentajes)
            $this->syncCompanies($consortium, $request->selected_companies);
        });

        return redirect()->back()->with('success', 'Consorcio creado correctamente.');
    }

    public function update(Request $request, Consortium $consortium)
    {
        //dd('Aquí estamos en update:  ', $request->selected_companies);
        $validated = $this->validateRequest($request, $consortium->id);

        DB::transaction(function () use ($request, $validated, $consortium) {
            // 1. Actualizar Logo (borrar anterior si hay uno nuevo)
            if ($request->hasFile('url_logo')) {
                if ($consortium->url_logo) {
                    Storage::disk('public')->delete($consortium->url_logo);
                }
                $validated['url_logo'] = $request->file('url_logo')->store('consortia_logos', 'public');
            }

            // 2. Actualizar datos básicos
            $consortium->update($validated);

            // 3. Sincronizar Socios (Sync reemplaza los anteriores por los nuevos)
            $this->syncCompanies($consortium, $request->selected_companies);
        });

        return redirect()->back()->with('success', 'Consorcio actualizado.');
    }

    public function destroy(Consortium $consortium)
    {
        if ($consortium->url_logo) {
            Storage::disk('public')->delete($consortium->url_logo);
        }

        $consortium->delete();
        return redirect()->back()->with('success', 'Consorcio eliminado.');
    }

    /**
     * Helpers internos para mantener el código limpio (DRY)
     */
    protected function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'name'                            => 'required|string|max:255',
            'ruc'                             => 'nullable|string|max:11|unique:consortia,ruc,' . $id,
            // CAMBIO AQUÍ: Solo valida como imagen si el dato es efectivamente un archivo
            'url_logo'                        => $request->hasFile('url_logo') ? 'image|mimes:jpg,jpeg,png|max:2048' : 'nullable',
            'legal_representative'            => 'required|string|max:255',
            'representative_dni'              => 'nullable|string|max:8',
            'representative_email'            => 'nullable|email',
            'representative_phone'            => 'nullable|string',
            'selected_companies'              => 'required|array|min:2',
            'selected_companies.*.company_id' => 'required|exists:companies,id', 'distinct',
            'selected_companies.*.percentage' => 'required|numeric|min:0.01|max:100',
        ], [
            // Mensaje personalizado para que el usuario entienda qué pasó
            'selected_companies.*.company_id.distinct' => 'No puedes seleccionar la misma empresa más de una vez.',
        ]);

    }

    protected function syncCompanies($consortium, $selectedCompanies)
    {
        $syncData = [];

        foreach ($selectedCompanies as $item) {
            // Asegúrate de que 'company_id' y 'percentage' existan en el objeto que viene de Vue
            if (! empty($item['company_id'])) {
                $syncData[$item['company_id']] = [
                    // AQUÍ: El nombre de la columna debe ser EXACTO al de tu base de datos
                    'participation_percentage' => $item['percentage'],
                ];
            }
        }

        $consortium->companies()->sync($syncData);
    }
}
