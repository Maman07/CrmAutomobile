<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneDevisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'designation' => $this->designation,
            'type' => $this->type,
            'quantite' => $this->quantite,
            'prix_unitaire' => (float) $this->prix_unitaire,
            'montant' => (float) $this->montant,
            'ordre' => $this->ordre,
            'est_main_oeuvre' => $this->isMainOeuvre(),
        ];
    }
}
