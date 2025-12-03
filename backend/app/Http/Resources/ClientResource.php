<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'type_client' => $this->type_client,
            'nom_entreprise' => $this->nom_entreprise,
            'is_entreprise' => $this->isEntreprise(),
            
            // Relations conditionnelles
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'vehicules_count' => $this->whenCounted('vehicules'),
            'tickets_count' => $this->whenCounted('tickets'),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
            'modifie_le' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
