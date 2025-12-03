<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Créer une notification in-app
     */
    public function createInApp(
        int $userId,
        string $type,
        string $message,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'canal' => 'in_app',
            'message' => $message,
            'data' => $data,
            'statut' => 'non_lu',
        ]);
    }

    /**
     * Envoyer une notification complète (in-app + email + SMS selon préférences)
     */
    public function send(
        User $user,
        string $type,
        string $message,
        array $data = [],
        bool $sendEmail = true,
        bool $sendSms = false
    ): void {
        // 1. Créer notification in-app (toujours)
        $this->createInApp($user->id, $type, $message, $data);

        // 2. Envoyer email si activé
        if ($sendEmail && $user->email_verified_at) {
            $this->sendEmail($user, $type, $message, $data);
        }

        // 3. Envoyer SMS si activé et demandé
        if ($sendSms && $user->telephone_verified_at) {
            $this->sendSms($user, $message);
        }
    }

    /**
     * Envoyer notification email
     * TODO: Implémenter avec Mailtrap/SendGrid/Mailgun
     */
    protected function sendEmail(User $user, string $type, string $message, array $data): void
    {
        try {
            // TODO: Créer Mailable classes pour chaque type
            // Mail::to($user->email)->send(new TicketCreatedMail($data));
            
            Log::info("📧 Email envoyé", [
                'to' => $user->email,
                'type' => $type,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur envoi email : " . $e->getMessage());
        }
    }

    /**
     * Envoyer notification SMS
     * TODO: Implémenter avec Twilio/InfoBip/etc.
     */
    protected function sendSms(User $user, string $message): void
    {
        try {
            // TODO: Intégrer API SMS Sénégal
            // $twilioClient->messages->create($user->telephone, ['body' => $message]);
            
            Log::info("📱 SMS envoyé", [
                'to' => $user->telephone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur envoi SMS : " . $e->getMessage());
        }
    }

    /**
     * NOTIFICATIONS PRÉDÉFINIES PAR TYPE
     */

    /**
     * Notification : Nouveau ticket créé (pour agents)
     */
    public function ticketCreated($ticket): void
    {
        // Notifier tous les agents
        $agents = User::where('role', 'agent')->where('statut', 'actif')->get();
        
        foreach ($agents as $agent) {
            $this->send(
                $agent,
                'ticket_created',
                "Nouveau ticket #{$ticket->numero_ticket} créé par {$ticket->client->user->nom_complet}",
                [
                    'ticket_id' => $ticket->id,
                    'numero_ticket' => $ticket->numero_ticket,
                    'priorite' => $ticket->priorite,
                ]
            );
        }
    }

    /**
     * Notification : Ticket assigné (pour technicien)
     */
    public function ticketAssigned($ticket): void
    {
        if ($ticket->technicien) {
            $this->send(
                $ticket->technicien->user,
                'ticket_assigned',
                "Nouveau ticket #{$ticket->numero_ticket} vous a été assigné. Priorité : {$ticket->priorite}",
                [
                    'ticket_id' => $ticket->id,
                    'numero_ticket' => $ticket->numero_ticket,
                    'vehicule' => $ticket->vehicule->libelle_complet,
                ],
                true, // Email
                true  // SMS si urgent
            );
        }
    }

    /**
     * Notification : Devis prêt (pour client)
     */
    public function devisReady($devis): void
    {
        $this->send(
            $devis->ticket->client->user,
            'devis_ready',
            "Votre devis #{$devis->numero} est prêt. Montant : " . number_format($devis->montant_ttc, 0, ',', ' ') . " FCFA",
            [
                'devis_id' => $devis->id,
                'numero_devis' => $devis->numero,
                'montant_ttc' => $devis->montant_ttc,
                'date_validite' => $devis->date_validite->format('Y-m-d'),
            ],
            true, // Email avec lien vers devis
            true  // SMS
        );
    }

    /**
     * Notification : Devis approuvé (pour technicien)
     */
    public function devisApproved($devis): void
    {
        if ($devis->technicien) {
            $this->send(
                $devis->technicien->user,
                'devis_approved',
                "Le devis #{$devis->numero} a été approuvé. En attente de paiement pour débloquer la réparation.",
                [
                    'devis_id' => $devis->id,
                    'ticket_id' => $devis->ticket_intervention_id,
                ]
            );
        }
    }

    /**
     * Notification : Paiement confirmé (pour client et technicien)
     */
    public function paymentConfirmed($paiement): void
    {
        $facture = $paiement->facture;
        $ticket = $facture->ticket;
        
        // Notifier le client
        $this->send(
            $ticket->client->user,
            'paiement_confirmed',
            "Votre paiement de " . number_format($paiement->montant, 0, ',', ' ') . " FCFA a été confirmé. La réparation va commencer.",
            [
                'paiement_id' => $paiement->id,
                'facture_numero' => $facture->numero,
                'ticket_id' => $ticket->id,
            ],
            true,
            true
        );
        
        // Notifier le technicien
        if ($ticket->technicien) {
            $this->send(
                $ticket->technicien->user,
                'paiement_confirmed',
                "Paiement confirmé pour ticket #{$ticket->numero_ticket}. Vous pouvez commencer la réparation.",
                [
                    'ticket_id' => $ticket->id,
                    'numero_ticket' => $ticket->numero_ticket,
                ]
            );
        }
    }

    /**
     * Notification : Véhicule prêt (pour client)
     */
    public function vehicleReady($ticket): void
    {
        $this->send(
            $ticket->client->user,
            'vehicle_ready',
            "Votre véhicule {$ticket->vehicule->libelle_complet} est prêt. Vous pouvez venir le récupérer.",
            [
                'ticket_id' => $ticket->id,
                'vehicule' => $ticket->vehicule->libelle_complet,
            ],
            true,
            true
        );
    }

    /**
     * Notification : Rappel RDV (24h avant)
     */
    public function rdvReminder($ticket): void
    {
        $this->send(
            $ticket->client->user,
            'rappel_rdv',
            "Rappel : RDV demain à " . $ticket->date_rdv->format('H:i') . " pour votre {$ticket->vehicule->libelle_complet}",
            [
                'ticket_id' => $ticket->id,
                'date_rdv' => $ticket->date_rdv->format('Y-m-d H:i'),
            ],
            true,
            true
        );
    }

    /**
     * Notification : Compte activé (pour client)
     */
    public function accountActivated(User $user): void
    {
        $this->send(
            $user,
            'account_activated',
            "Bienvenue chez AUTOTECH SERVICES ! Votre compte a été activé. Vous pouvez maintenant créer vos tickets d'intervention.",
            [],
            true,
            true
        );
    }
}

