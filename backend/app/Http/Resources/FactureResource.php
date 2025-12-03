<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'montant_ht' => (float) $this->montant_ht,
            'montant_tva' => (float) $this->montant_tva,
            'montant_ttc' => (float) $this->montant_ttc,
            'montant_ttc_formate' => number_format($this->montant_ttc, 0, ',', ' ') . ' FCFA',
            'statut' => $this->statut,
            'date_emission' => $this->date_emission?->format('Y-m-d'),
            'date_echeance' => $this->date_echeance?->format('Y-m-d'),
            'date_paiement' => $this->date_paiement?->format('Y-m-d H:i:s'),
            
            // Calculs
            'montant_paye' => (float) $this->montant_paye,
            'reste_a_payer' => (float) $this->reste_a_payer,
            'reste_a_payer_formate' => number_format($this->reste_a_payer, 0, ',', ' ') . ' FCFA',
            
            // Indicateurs
            'est_payee' => $this->isPayee(),
            'est_echue' => $this->isEchue(),
            
            // Relations
            'devis' => $this->whenLoaded('devis', function () {
                return new DevisResource($this->devis);
            }),
            'ticket' => $this->whenLoaded('ticket', function () {
                return new TicketResource($this->ticket);
            }),
            'paiements' => PaiementResource::collection($this->whenLoaded('paiements')),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
