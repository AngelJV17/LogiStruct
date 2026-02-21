<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\SaveCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanyController extends Controller
{
    private const STORAGE_PATH = 'companies_logos';

    public function index(Request $request)
    {
        return Inertia::render('Companies/Index', [
            'companies' => Company::query()
                ->when($request->search, fn($q, $s) =>
                    $q->where('name', 'like', "%{$s}%")->orWhere('ruc', 'like', "%{$s}%")
                )
                ->latest()
                ->paginate($request->perPage ?? 10)
                ->withQueryString(),
            'filters' => $request->only(['search', 'perPage']),
        ]);
    }

    public function store(SaveCompanyRequest $request)
    {
        try {
            $data                         = $request->validated();
            $data['issues_payment_order'] = $request->boolean('issues_payment_order');

            if ($request->hasFile('url_logo')) {
                $data['url_logo'] = $request->file('url_logo')->store(self::STORAGE_PATH, 'public');
            }

            Company::create($data);

            return redirect()->route('companies.index')
                ->with(['message' => 'Empresa registrada con éxito', 'type' => 'success']);

        } catch (\Exception $e) {
            Log::error("Error Store Company: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'Error al registrar empresa', 'type' => 'error']);
        }
    }

    public function update(SaveCompanyRequest $request, Company $company)
    {
        try {
            $data                         = $request->validated();
            $data['issues_payment_order'] = $request->boolean('issues_payment_order');

            if ($request->hasFile('url_logo')) {
                $this->deleteLogo($company->url_logo);
                $data['url_logo'] = $request->file('url_logo')->store(self::STORAGE_PATH, 'public');
            } else {
                // Si no hay archivo nuevo, evitamos sobreescribir con el string de la URL
                unset($data['url_logo']);
            }

            $company->update($data);

            return redirect()->route('companies.index')
                ->with(['message' => 'Empresa actualizada correctamente', 'type' => 'success']);

        } catch (\Exception $e) {
            Log::error("Error Update Company ID {$company->id}: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'Error al actualizar empresa', 'type' => 'error']);
        }
    }

    public function destroy(Company $company)
    {
        try {
            $this->deleteLogo($company->url_logo);
            $company->delete();

            return redirect()->route('companies.index')
                ->with(['message' => 'Empresa eliminada', 'type' => 'warning']);
        } catch (\Exception $e) {
            Log::error("Error Delete Company ID {$company->id}: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'No se pudo eliminar la empresa', 'type' => 'error']);
        }
    }

    /**
     * Helper privado para limpieza de archivos (Consistencia Senior)
     */
    private function deleteLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
