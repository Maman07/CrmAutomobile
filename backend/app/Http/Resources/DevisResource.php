<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'description' => $this->description,
            'montant_ht' => (float) $this->montant_ht,
            'montant_tva' => (float) $this->montant_tva,
            'montant_ttc' => (float) $this->montant_ttc,
            'montant_ttc_formate' => number_format($this->montant_ttc, 0, ',', ' ') . ' FCFA',
            'statut' => $this->statut,
            'date_validite' => $this->date_validite?->format('Y-m-d'),
            'date_approbation' => $this->date_approbation?->format('Y-m-d H:i:s'),
            'motif_refus' => $this->motif_refus,
            
            // Indicateurs
            'est_expire' => $this->isExpire(),
            'est_approuve' => $this->isApprouve(),
            
            // Relations
            'ticket' => $this->whenLoaded('ticket', function () {
                return new TicketResource($this->ticket);
            }),
            'technicien' => $this->whenLoaded('technicien', function () {
                return new TechnicienResource($this->technicien);
            }),
            'lignes' => LigneDevisResource::collection($this->whenLoaded('lignes')),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
