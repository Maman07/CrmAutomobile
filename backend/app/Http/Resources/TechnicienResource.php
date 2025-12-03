<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicienResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'matricule' => $this->matricule,
            'date_embauche' => $this->date_embauche->format('Y-m-d'),
            'anciennete_annees' => $this->anciennete,
            'specialite' => $this->specialite,
            'niveau_experience' => $this->niveau_experience,
            'certifications' => $this->certifications ?? [],
            'est_expert' => $this->isExpert(),
            
            // Stats
            'tickets_en_cours' => $this->when(
                $request->route()?->getName() === 'agent.dashboard',
                $this->tickets_en_cours_count ?? 0
            ),
            
            // Relations
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            
            'cree_le' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

