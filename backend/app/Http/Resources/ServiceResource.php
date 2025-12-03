<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'actif' => $this->actif,
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

