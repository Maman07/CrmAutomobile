<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'matricule' => $this->matricule,
            'date_embauche' => $this->date_embauche?->format('Y-m-d'),
            'poste' => $this->poste,
            'anciennete_annees' => $this->anciennete,
            
            // Relations
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
