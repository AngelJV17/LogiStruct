<?php
namespace App\Http\Controllers;

use App\Models\GlobalParameter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GlobalParameterController extends Controller
{
    public function index(Request $request)
    {
        // 1. Capturamos los parámetros de la URL con valores por defecto
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // Por defecto 10 filas

        // 2. Construcción de la consulta
        $parameters = GlobalParameter::query()
            ->with('parent') // Cargamos la relación del padre para la tabla
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('group', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            // 3. Ordenamiento solicitado: Por nivel de forma ascendente
            // Añadimos 'group' y 'id' como secundarios para mantener jerarquía visual lógica
            ->orderBy('level', 'asc')
            ->orderBy('group', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // Mantiene los filtros en los enlaces de paginación

        // 4. Obtenemos solo los de Nivel 1 para el selector de "Padres" en el modal
        $parentOptions = GlobalParameter::where('level', 1)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'group']);

        return Inertia::render('Parameters/Index', [
            'parameters' => $parameters,
            'parentOptions' => $parentOptions,
            'filters'   => $request->only(['search', 'perPage']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:global_parameters,id',
            'is_active' => 'boolean'
        ]);

        // Lógica de Nivel automática
        if ($request->filled('parent_id')) {
            $parent = GlobalParameter::findOrFail($request->parent_id);
            $validated['level'] = $parent->level + 1;
        } else {
            $validated['level'] = 1;
        }

        GlobalParameter::create($validated);

        return redirect()->back()->with('message', 'Parámetro creado correctamente.');
    }

    public function update(Request $request, GlobalParameter $parameter)
    {
        $validated = $request->validate([
            'group' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:global_parameters,id',
            'is_active' => 'boolean'
        ]);

        // Recalcular nivel si el padre cambió
        if ($request->filled('parent_id')) {
            $parent = GlobalParameter::findOrFail($request->parent_id);
            $validated['level'] = $parent->level + 1;
        } else {
            $validated['level'] = 1;
        }

        $parameter->update($validated);

        return redirect()->back()->with('message', 'Parámetro actualizado.');
    }

    public function destroy(GlobalParameter $parameter)
    {
        // Al tener 'onDelete('cascade')' en la migración, los hijos se borrarán solos
        $parameter->delete();

        return redirect()->back()->with('message', 'Parámetro eliminado.');
    }
}
