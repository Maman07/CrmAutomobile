<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends BaseController
{
    /**
     * Liste des notifications de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::where('user_id', auth('api')->id());

        // Filtrer par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtrer par type
        if ($request->has('type')) {
            $query->type($request->type);
        }

        // Uniquement non lues
        if ($request->has('non_lues') && $request->non_lues) {
            $query->nonLues();
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->sendPaginated($notifications, 'Notifications');
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::where('user_id', auth('api')->id())
            ->find($id);

        if (!$notification) {
            return $this->sendNotFound('Notification non trouvée');
        }

        $notification->marquerCommeLu();

        return $this->sendSuccess('Notification marquée comme lue');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::where('user_id', auth('api')->id())
            ->nonLues()
            ->update([
                'statut' => 'lu',
                'date_lecture' => now(),
            ]);

        return $this->sendSuccess('Toutes les notifications marquées comme lues');
    }

    /**
     * Nombre de notifications non lues
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notification::where('user_id', auth('api')->id())
            ->nonLues()
            ->count();

        return $this->sendResponse(['count' => $count], 'Nombre de notifications non lues');
    }

    /**
     * Supprimer une notification
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::where('user_id', auth('api')->id())
            ->find($id);

        if (!$notification) {
            return $this->sendNotFound('Notification non trouvée');
        }

        $notification->delete();

        return $this->sendSuccess('Notification supprimée');
    }
}

