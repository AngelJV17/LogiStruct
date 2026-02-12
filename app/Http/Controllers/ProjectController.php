<?php
namespace App\Http\Controllers;

use App\Http\Requests\Project\SaveProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Company;
use App\Models\Consortium;
use App\Models\GlobalParameter;
use App\Models\Project;
use App\Models\UbigeoPeruDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta con Eager Loading para evitar el problema N+1
        $projects = Project::query()
            ->with([
                'type',
                'status',
                'department',
                'province',
                'district',
                'company',
                'consortium',
            ])
        // 2. Aplicamos filtros de búsqueda si existen
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                        ->orWhere('project_code', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%");
                });
            })
        // 3. Ordenamos por los más recientes por defecto
            ->latest()
        // 4. Paginación dinámica (usa 10 por defecto si no viene en el request)
            ->paginate($request->input('perPage', 10))
        // 5. Mantenemos los parámetros de búsqueda en los links de paginación
            ->withQueryString();

        // 6. Retornamos a la vista de Inertia con todos los datos necesarios
        return Inertia::render('Projects/Index', [
            // Transformamos los proyectos usando el Resource que corregimos
            'projects'    => ProjectResource::collection($projects),

            // Enviamos los filtros actuales para que los inputs no se limpien
            'filters'     => $request->only(['search', 'perPage']),

            // Datos para los Selects del Drawer
            // Filtramos por el grupo que definiste en tu seeder
            'types'       => GlobalParameter::where('group', 'PROJECT_TYPE')
                ->where('level', 2)
                ->select('id', 'name')
                ->get(),

            'statuses'    => GlobalParameter::where('group', 'PROJECT_STATUS')
                ->where('level', 2)
                ->select('id', 'name')
                ->get(),

            'companies'   => Company::orderBy('name')->get(),
            'consortia'   => Consortium::orderBy('name')->get(),
            'departments' => UbigeoPeruDepartment::orderBy('name')->get(),
        ]);
    }

    public function show(Project $project)
    {
        // Debes cargar 'consortium.companies' explícitamente
        $project->load([
            'type',
            'status',
            'department',
            'province',
            'district',
            'company',
            'consortium.companies', // <--- ESTA ES LA CLAVE
        ]);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function store(SaveProjectRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('cover_image')) {
                // Guardamos en la carpeta unificada
                $path                = $request->file('cover_image')->store('projects/covers', 'public');
                $data['cover_image'] = $path;
            }

            Project::create($data);

            return back()->with('message', [
                'type' => 'success',
                'text' => "Proyecto registrado con éxito.",
            ]);
        });
    }

    public function update(SaveProjectRequest $request, Project $project)
    {
        return DB::transaction(function () use ($request, $project) {
            $data = $request->validated();

            if ($request->hasFile('cover_image')) {
                // 1. ELIMINACIÓN: Si existe una imagen previa, la borramos del disco
                if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
                    Storage::disk('public')->delete($project->cover_image);
                }

                // 2. GUARDADO: Usamos la misma carpeta 'projects/covers'
                $path = $request->file('cover_image')->store('projects/covers', 'public');

                // 3. ASIGNACIÓN: Guardamos la nueva ruta en el array
                $data['cover_image'] = $path;
            } else {
                // Si no se subió una nueva imagen, quitamos el campo del array
                // para que no intente guardar el objeto temporal o null
                unset($data['cover_image']);
            }

            // 4. ACTUALIZACIÓN: Actualizamos el modelo con el array limpio
            $project->update($data);

            return back()->with('message', [
                'type' => 'success',
                'text' => "Proyecto actualizado correctamente.",
            ]);
        });
    }

    public function destroy(Project $project)
    {
        return DB::transaction(function () use ($project) {
            // 1. Borrar la imagen física si existe
            if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
                Storage::disk('public')->delete($project->cover_image);
            }

            // 2. Eliminar el proyecto de la base de datos
            $project->delete();

            return back()->with('message', [
                'type' => 'success',
                'text' => "Proyecto eliminado exitosamente.",
            ]);
        });
    }
}
