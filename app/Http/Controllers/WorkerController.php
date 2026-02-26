<?php
namespace App\Http\Controllers;

use App\Http\Requests\Worker\SaveWorkerRequest;
use App\Models\Bank;
use App\Models\Company;
use App\Models\GlobalParameter;
use App\Models\PensionSystem;
use App\Models\Position;
use App\Models\Project;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'perPage']);

        // Parámetros que se quedan en GlobalParameter
        $globalParams = GlobalParameter::where('is_active', 1)
            ->get()
            ->groupBy('group');

        $getParams = function ($groupName) use ($globalParams) {
            return $globalParams->get($groupName) ?? [];
        };

        $workers = Worker::query()
            ->with([
                'company:id,name',
                'project:id,short_name as name',
                'position:id,name',
                'bank:id,name,short_name',
                // Carga de parámetros relacionados
                'pensionSystem:id,name',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where('document_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name_paternal', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->perPage ?? 10)
            ->withQueryString();

        return Inertia::render('Workers/Index', [
            'workers'         => $workers,
            'filters'         => $filters,
            'params'          => [
                'document_types' => $getParams('DOCUMENT_TYPE'),
                'worker_types'   => $getParams('WORKER_TYPE'),
                'payment_types'  => $getParams('PERIODICIDAD_DE_PAGO'),
                'genders'        => $getParams('GENDER'),
            ],
            // Tablas Independientes
            'positions'       => Position::where('is_active', 1)->orderBy('name')->get(['id', 'name']),
            'banks'           => Bank::orderBy('name')->get(['id', 'name', 'short_name']),
            'pensionSystems' => PensionSystem::all(['id', 'name', 'type']),

            'companies'       => Company::all(['id', 'name']),
            'projects'        => Project::select('id', 'short_name as name')->get(),
        ]);
    }

    public function show(Worker $worker)
    {
        $worker->load([
            'documentType', 'gender', 'workerType', 'position',
            'project', 'company', 'paymentType', 'pensionSystem', 'bank',
        ]);

        // Necesitamos estos datos para que los selects del Drawer tengan opciones
        $globalParams = GlobalParameter::where('is_active', 1)->get()->groupBy('group');
        $getParams    = function ($groupName) use ($globalParams) {
            return $globalParams->get($groupName) ?? [];
        };

        return Inertia::render('Workers/Show', [
            'worker'         => $worker,
            // Agregamos lo que faltaba:
            'params'         => [
                'document_types' => $getParams('DOCUMENT_TYPE'),
                'worker_types'   => $getParams('WORKER_TYPE'),
                'payment_types'  => $getParams('PERIODICIDAD_DE_PAGO'),
                'genders'        => $getParams('GENDER'),
            ],
            'positions'      => Position::where('is_active', 1)->orderBy('name')->get(['id', 'name']),
            'banks'          => Bank::orderBy('name')->get(['id', 'name', 'short_name']),
            'pensionSystems' => PensionSystem::all(['id', 'name', 'type']),
            'companies'      => Company::all(['id', 'name']),
            'projects'       => Project::select('id', 'short_name as name')->get(),
        ]);
    }

    public function store(SaveWorkerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('workers/photos', 'public');
        }

        Worker::create($data);

        return redirect()->back()->with([
            'message' => 'Trabajador registrado correctamente.',
            'type'    => 'success',
        ]);
    }

    public function update(SaveWorkerRequest $request, Worker $worker)
    {
        // 1. Obtener todos los datos validados del Request
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // 2. Eliminar foto anterior si existe
            if ($worker->photo_path) {
                Storage::disk('public')->delete($worker->photo_path);
            }

            // 3. Guardar la nueva foto y agregar la ruta al array de datos
            $data['photo_path'] = $request->file('photo')->store('workers/photos', 'public');
        }

        // 4. Actualizar el modelo con los datos validados + la nueva ruta de foto
        $worker->update($data);

        return redirect()->back()->with([
            'message' => 'Información actualizada con éxito.',
            'type'    => 'success',
        ]);

    }

    public function destroy(Worker $worker)
    {
        $worker->delete();

        return redirect()->back()->with([
            'message' => 'Trabajador enviado a la papelera.',
            'type'    => 'warning',
        ]);
    }
}
