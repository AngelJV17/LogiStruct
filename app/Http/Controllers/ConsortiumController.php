<?php
namespace App\Http\Controllers;

use App\Http\Requests\Consortium\SaveConsortiumRequest;
use App\Models\Company;
use App\Models\Consortium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ConsortiumController extends Controller
{
    private const STORAGE_PATH = 'consortia_logos';

    public function index(Request $request)
    {
        return Inertia::render('Consortia/Index', [
            'consortia' => Consortium::query()
                ->with('companies:id,name,ruc,url_logo') // Carga optimizada (solo columnas necesarias)
                ->when($request->search, fn($q, $s) =>
                    $q->where('name', 'like', "%{$s}%")->orWhere('ruc', 'like', "%{$s}%")
                )
                ->latest()
                ->paginate($request->perPage ?? 10)
                ->withQueryString(),
            'companies' => Company::select('id', 'name', 'ruc', 'url_logo')->get(),
            'filters'   => $request->only(['search', 'perPage']),
        ]);
    }

    public function store(SaveConsortiumRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                if ($request->hasFile('url_logo')) {
                    $data['url_logo'] = $request->file('url_logo')->store(self::STORAGE_PATH, 'public');
                }

                $consortium = Consortium::create($data);
                $this->attachCompanies($consortium, $data['selected_companies']);

                return redirect()->route('consortia.index')
                    ->with(['message' => "Consorcio {$consortium->name} creado.", 'type' => 'success']);
            });
        } catch (\Exception $e) {
            Log::error("Error Store Consortium: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'Error al crear consorcio.', 'type' => 'error']);
        }
    }

    public function update(SaveConsortiumRequest $request, Consortium $consortium)
    {
        try {
            return DB::transaction(function () use ($request, $consortium) {
                // Obtenemos los datos validados (excepto el logo por ahora)
                $data = $request->validated();

                // Manejo del Logo: Solo si se sube un archivo nuevo
                if ($request->hasFile('url_logo')) {
                    $this->deleteLogo($consortium->url_logo);
                    $data['url_logo'] = $request->file('url_logo')->store(self::STORAGE_PATH, 'public');
                } else {
                    // IMPORTANTE: Si no hay archivo nuevo, eliminamos url_logo del array
                    // para que update() no intente cambiar el valor actual en la BD.
                    unset($data['url_logo']);
                }

                $consortium->update($data);

                // Sincronización de socios
                if (isset($data['selected_companies'])) {
                    $this->attachCompanies($consortium, $data['selected_companies']);
                }

                return redirect()->route('consortia.index')
                    ->with(['message' => 'Consorcio actualizado correctamente.', 'type' => 'success']);
            });
        } catch (\Exception $e) {
            Log::error("Error Update Consortium ID {$consortium->id}: " . $e->getMessage());
            return redirect()->back()->with(['message' => 'Error al actualizar el consorcio.', 'type' => 'error']);
        }
    }

    public function destroy(Consortium $consortium)
    {
        $this->deleteLogo($consortium->url_logo);
        $consortium->delete();

        return redirect()->back()->with(['message' => 'Consorcio eliminado.', 'type' => 'success']);
    }

    /**
     * Centraliza la lógica de adjuntar compañías (Pivot)
     */
    private function attachCompanies(Consortium $consortium, array $companies): void
    {
        // Transformamos el array para sync(): [id => ['participation_percentage' => valor]]
        $syncData = collect($companies)->mapWithKeys(fn($item) => [
            $item['company_id'] => ['participation_percentage' => $item['percentage']],
        ])->toArray();

        $consortium->companies()->sync($syncData);
    }

    private function deleteLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
