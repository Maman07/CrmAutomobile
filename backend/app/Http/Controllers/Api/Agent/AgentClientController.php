<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AgentClientController extends BaseController
{
    /**
     * Liste des clients en attente d'activation
     */
    public function enAttente(): JsonResponse
    {
        $clients = User::where('role', 'client')
            ->where('statut', 'inactif')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->sendPaginated($clients, 'Clients en attente d\'activation');
    }

    /**
     * Activer un compte client
     */
    public function activer(int $id): JsonResponse
    {
        $user = User::where('role', 'client')
            ->where('statut', 'inactif')
            ->find($id);

        if (!$user) {
            return $this->sendNotFound('Client non trouvé ou déjà actif');
        }

        $user->update(['statut' => 'actif']);

        // TODO: Envoyer email/SMS de bienvenue au client

        return $this->sendResponse($user, 'Compte client activé avec succès. Le client peut maintenant se connecter et créer des tickets.');
    }

    /**
     * Rejeter une demande d'inscription client
     */
    public function rejeter(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', 'client')
            ->where('statut', 'inactif')
            ->find($id);

        if (!$user) {
            return $this->sendNotFound('Client non trouvé');
        }

        $validator = Validator::make($request->all(), [
            'motif' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Mettre en statut suspendu avec motif
        $user->update(['statut' => 'suspendu']);

        // TODO: Envoyer email/SMS au client avec motif de rejet

        return $this->sendSuccess('Demande d\'inscription rejetée. Le client a été notifié.');
    }
}

