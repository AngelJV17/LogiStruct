<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ConsortiumCompany extends Pivot
{
    protected $table = 'consortium_company';
    
    // Indica que los IDs son incrementales si usaste $table->id()
    public $incrementing = true; 

    // Método de utilidad para el futuro módulo económico
    public function getCompanyShare(float $amount): float {
        return ($this->participation_percentage / 100) * $amount;
    }
}
