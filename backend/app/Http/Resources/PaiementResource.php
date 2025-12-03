<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'montant' => (float) $this->montant,
            'montant_formate' => number_format($this->montant, 0, ',', ' ') . ' FCFA',
            'date_paiement' => $this->date_paiement?->format('Y-m-d H:i:s'),
            'statut' => $this->statut,
            'reference_externe' => $this->reference_externe,
            
            // Indicateurs
            'est_confirme' => $this->isConfirme(),
            
            // Relations
            'facture' => $this->whenLoaded('facture', function () {
                return new FactureResource($this->facture);
            }),
            'type_paiement' => $this->whenLoaded('typePaiement', function () {
                return [
                    'id' => $this->typePaiement->id,
                    'libelle' => $this->typePaiement->libelle,
                ];
            }),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
