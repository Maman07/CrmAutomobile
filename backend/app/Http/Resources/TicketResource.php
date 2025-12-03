<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_ticket' => $this->numero_ticket,
            'description' => $this->description,
            'statut' => $this->statut,
            'priorite' => $this->priorite,
            'kilometrage_entree' => $this->kilometrage_entree,
            'kilometrage_sortie' => $this->kilometrage_sortie,
            'duree_estimee' => $this->duree_estimee,
            'observation' => $this->observation,
            
            // Dates
            'date_rdv' => $this->date_rdv?->format('Y-m-d H:i:s'),
            'date_affectation' => $this->date_affectation?->format('Y-m-d H:i:s'),
            'date_cloture' => $this->date_cloture?->format('Y-m-d H:i:s'),
            
            // Indicateurs
            'est_cloture' => $this->isCloture(),
            
            // Relations
            'client' => $this->whenLoaded('client', function () {
                return new ClientResource($this->client);
            }),
            'vehicule' => $this->whenLoaded('vehicule', function () {
                return new VehiculeResource($this->vehicule);
            }),
            'technicien' => $this->whenLoaded('technicien', function () {
                return new TechnicienResource($this->technicien);
            }),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'devis' => $this->whenLoaded('devis', function () {
                return new DevisResource($this->devis);
            }),
            'facture' => $this->whenLoaded('facture', function () {
                return new FactureResource($this->facture);
            }),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
            'modifie_le' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}

