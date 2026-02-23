<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConsortiumController;
use App\Http\Controllers\GlobalParameterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Models\UbigeoPeruDistrict;
use App\Models\UbigeoPeruProvince;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('ubigeo')->group(function () {

    // Provincias con Caché de 1 hora
    Route::get('provinces/{department_id}', function ($department_id) {
        return Cache::remember("provinces_dept_{$department_id}", 3600, function () use ($department_id) {
            return UbigeoPeruProvince::where('department_id', $department_id)
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);
        });
    })->name('api.ubigeo.provinces');

    // Distritos con Caché de 1 hora
    Route::get('districts/{province_id}', function ($province_id) {
        return Cache::remember("districts_prov_{$province_id}", 3600, function () use ($province_id) {
            return UbigeoPeruDistrict::where('province_id', $province_id)
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);
        });
    })->name('api.ubigeo.districts');

});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    // CRUD de Empresas (Resource) 
    // Nota: Para editar con logo, usar POST en el form con _method: PUT
    Route::resource('companies', CompanyController::class);

    // CRUD de Consorcios (Resource)
    Route::resource('consortia', ConsortiumController::class);

    // CRUD de Proyectos (Resource)
    Route::resource('projects', ProjectController::class);
    
    // CRUD de Parametros (Resource)
    Route::resource('parameters', GlobalParameterController::class);
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
