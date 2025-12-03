<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nom_complet' => $this->nom_complet,
            'email' => $this->email,
            'email_verifie' => !is_null($this->email_verified_at),
            'telephone' => $this->telephone,
            'telephone_verifie' => !is_null($this->telephone_verified_at),
            'role' => $this->role,
            'statut' => $this->statut,
            'derniere_connexion' => $this->derniere_connexion?->format('Y-m-d H:i:s'),
            
            // Relations conditionnelles
            'client' => $this->whenLoaded('client', function () {
                return new ClientResource($this->client);
            }),
            'agent' => $this->whenLoaded('agent', function () {
                return new AgentResource($this->agent);
            }),
            'technicien' => $this->whenLoaded('technicien', function () {
                return new TechnicienResource($this->technicien);
            }),
            'manager' => $this->whenLoaded('manager', function () {
                return new ManagerResource($this->manager);
            }),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
            'modifie_le' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
