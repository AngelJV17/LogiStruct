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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProjectController extends Controller
{
    private const STORAGE_PATH = 'projects/covers';

    public function index(Request $request)
    {
        $projects = Project::query()
            ->with(['type', 'status', 'department', 'province', 'district', 'company', 'consortium'])
            ->when($request->search, fn($q, $s) =>
                $q->where('project_name', 'like', "%{$s}%")
                    ->orWhere('project_code', 'like', "%{$s}%")
                    ->orWhere('short_name', 'like', "%{$s}%")
            )
            ->latest()
            ->paginate($request->perPage ?? 10)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects'    => ProjectResource::collection($projects),
            'filters'     => $request->only(['search', 'perPage']),
            'types'       => $this->getGlobalParams('PROJECT_TYPE'),
            'statuses'    => $this->getGlobalParams('PROJECT_STATUS'),
            'companies'   => Company::select('id', 'name')->orderBy('name')->get(),
            'consortia'   => Consortium::select('id', 'name')->orderBy('name')->get(),
            'departments' => UbigeoPeruDepartment::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function show(Project $project)
    {
        $project->load(['type', 'status', 'department', 'province', 'district', 'company', 'consortium.companies']);

        return Inertia::render('Projects/Show', ['project' => $project]);
    }

    public function store(SaveProjectRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $data = $request->validated();

                if ($request->hasFile('cover_image')) {
                    $data['cover_image'] = $request->file('cover_image')->store(self::STORAGE_PATH, 'public');
                }

                $project = Project::create($data);

                return redirect()->route('projects.index')
                    ->with(['message' => "Proyecto {$project->project_code} creado.", 'type' => 'success']);
            });
        } catch (\Exception $e) {
            Log::error("Error Store Project: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'Error al crear el proyecto.', 'type' => 'error']);
        }
    }

    public function update(SaveProjectRequest $request, Project $project)
    {
        try {
            return DB::transaction(function () use ($request, $project) {
                $data = $request->validated();

                if ($request->hasFile('cover_image')) {
                    $this->deleteCover($project->cover_image);
                    $data['cover_image'] = $request->file('cover_image')->store(self::STORAGE_PATH, 'public');
                } else {
                    unset($data['cover_image']);
                }

                $project->update($data);

                return redirect()->route('projects.index')
                    ->with(['message' => 'Proyecto actualizado correctamente.', 'type' => 'success']);
            });
        } catch (\Exception $e) {
            Log::error("Error Update Project ID {$project->id}: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'Error al actualizar el proyecto.', 'type' => 'error']);
        }
    }

    public function destroy(Project $project)
    {
        try {
            $this->deleteCover($project->cover_image);
            $project->delete();

            return redirect()->back()->with(['message' => 'Proyecto eliminado.', 'type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error Delete Project ID {$project->id}: {$e->getMessage()}");
            return redirect()->back()->with(['message' => 'No se pudo eliminar el proyecto.', 'type' => 'error']);
        }
    }

    /**
     * Helpers privados para limpieza y consistencia
     */
    private function deleteCover(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function getGlobalParams(string $group)
    {
        return GlobalParameter::where('group', $group)
            ->where('level', 2)
            ->select('id', 'name')
            ->get();
    }
}
