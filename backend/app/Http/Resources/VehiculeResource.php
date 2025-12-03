<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'immatriculation' => $this->immatriculation,
            'marque' => $this->marque,
            'modele' => $this->modele,
            'annee' => $this->annee,
            'couleur' => $this->couleur,
            'numero_serie' => $this->numero_serie,
            'type_carburant' => $this->type_carburant,
            'libelle_complet' => $this->libelle_complet,
            'dernier_kilometrage' => $this->dernier_kilometrage,
            'date_kilometrage' => $this->date_kilometrage?->format('Y-m-d'),
            
            // Relations
            'client' => $this->whenLoaded('client', function () {
                return new ClientResource($this->client);
            }),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

