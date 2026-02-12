<?php
namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'project_code' => $this->project_code,
            'project_name' => $this->project_name,
            'short_name'   => $this->short_name,
            'amounts'      => [
                'contractual' => $this->contractual_amount,
                'projected'   => $this->projected_amount,
                'currency'    => 'S/',
            ],
            'location'     => [
                // Usamos ?-> para evitar errores si no hay relación cargada
                'department'   => $this->department?->name ?? 'N/A',
                'province'     => $this->province?->name ?? 'N/A',
                'district'     => $this->district?->name ?? 'N/A',
                'full_address' => "{$this->department?->name} - {$this->province?->name} - {$this->district?->name}",
                'address_detail' => $this->address,
                'ids'            => [
                    'department' => $this->department_id,
                    'province'   => $this->province_id,
                    'district'   => $this->district_id,
                ],
            ],
            'dates'     => [
                // Verificamos que las fechas existan antes de formatear
                'start'           => $this->start_date ? $this->start_date->format('d/m/Y') : '---',
                'end_contractual' => $this->end_date_contractual ? $this->end_date_contractual->format('d/m/Y') : '---',
                'is_expired'      => $this->end_date_contractual ? now()->gt($this->end_date_contractual) : false,
            ],
            'owner'     => $this->consortium_id
                ? ['type' => 'Consorcio', 'name' => $this->consortium?->name, 'id' => $this->consortium_id]
                : ['type' => 'Empresa', 'name' => $this->company?->name, 'id' => $this->company_id],
            'cover_url' => $this->cover_image ? asset("storage/{$this->cover_image}") : null,

            // No olvides pasar los IDs planos para que el Form del Drawer los entienda fácil
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'company_id' => $this->company_id,
            'consortium_id' => $this->consortium_id,
        ];
    }
}
