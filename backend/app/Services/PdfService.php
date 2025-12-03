<?php

namespace App\Services;

use App\Models\Devis;
use App\Models\Facture;
use Illuminate\Support\Facades\Log;

class PdfService
{
    /**
     * Générer le PDF d'un devis
     * TODO: Implémenter avec barryvdh/laravel-dompdf
     */
    public function generateDevisPdf(Devis $devis): string
    {
        // Charger toutes les relations nécessaires
        $devis->load([
            'ticket.client.user',
            'ticket.vehicule',
            'technicien.user',
            'lignes'
        ]);

        // TODO: Utiliser DomPDF
        /*
        $pdf = \PDF::loadView('pdf.devis', [
            'devis' => $devis,
            'entreprise' => $this->getEntrepriseInfo(),
        ]);
        
        $filename = "devis_{$devis->numero}.pdf";
        $path = storage_path("app/public/pdf/{$filename}");
        $pdf->save($path);
        
        return $path;
        */

        Log::info("📄 Génération PDF Devis", [
            'devis_id' => $devis->id,
            'numero' => $devis->numero,
        ]);

        // Retourner chemin temporaire pour développement
        return "pdf/devis_{$devis->numero}.pdf";
    }

    /**
     * Générer le PDF d'une facture
     * TODO: Implémenter avec DomPDF
     */
    public function generateFacturePdf(Facture $facture): string
    {
        // Charger toutes les relations
        $facture->load([
            'ticket.client.user',
            'ticket.vehicule',
            'devis.lignes',
            'paiements.typePaiement'
        ]);

        // TODO: Utiliser DomPDF
        /*
        $pdf = \PDF::loadView('pdf.facture', [
            'facture' => $facture,
            'entreprise' => $this->getEntrepriseInfo(),
        ]);
        
        $filename = "facture_{$facture->numero}.pdf";
        $path = storage_path("app/public/pdf/{$filename}");
        $pdf->save($path);
        
        return $path;
        */

        Log::info("📄 Génération PDF Facture", [
            'facture_id' => $facture->id,
            'numero' => $facture->numero,
        ]);

        return "pdf/facture_{$facture->numero}.pdf";
    }

    /**
     * Récupérer les infos de l'entreprise depuis paramètres
     */
    protected function getEntrepriseInfo(): array
    {
        return [
            'nom' => \App\Models\ParametreSysteme::get('nom_entreprise', 'AUTOTECH SERVICES'),
            'adresse' => \App\Models\ParametreSysteme::get('adresse_entreprise', 'Route de Rufisque, Dakar'),
            'telephone' => \App\Models\ParametreSysteme::get('telephone_entreprise', '+221 33 XXX XX XX'),
            'email' => \App\Models\ParametreSysteme::get('email_contact', 'contact@autotech-services.sn'),
        ];
    }

    /**
     * Template HTML pour devis (utilisé pour génération PDF)
     * TODO: Créer vraie vue Blade resources/views/pdf/devis.blade.php
     */
    public function getDevisTemplate(Devis $devis): string
    {
        $entreprise = $this->getEntrepriseInfo();
        
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .info-client { margin-top: 30px; }
                table { width: 100%; border-collapse: collapse; margin-top: 30px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; font-size: 1.2em; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$entreprise['nom']}</h1>
                <p>{$entreprise['adresse']}<br>{$entreprise['telephone']}<br>{$entreprise['email']}</p>
            </div>
            
            <h2>DEVIS N° {$devis->numero}</h2>
            <p>Date : {$devis->created_at->format('d/m/Y')}</p>
            <p>Valable jusqu'au : {$devis->date_validite->format('d/m/Y')}</p>
            
            <div class='info-client'>
                <h3>Client</h3>
                <p><strong>{$devis->ticket->client->user->nom_complet}</strong><br>
                {$devis->ticket->client->adresse}<br>
                {$devis->ticket->client->user->telephone}</p>
            </div>
            
            <div class='info-vehicule'>
                <h3>Véhicule</h3>
                <p>{$devis->ticket->vehicule->libelle_complet}<br>
                Immatriculation : {$devis->ticket->vehicule->immatriculation}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th>Type</th>
                        <th>Qté</th>
                        <th>Prix unit.</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($devis->lignes as $ligne) {
            $html .= "
                    <tr>
                        <td>{$ligne->designation}</td>
                        <td>{$ligne->type}</td>
                        <td>{$ligne->quantite}</td>
                        <td>" . number_format($ligne->prix_unitaire, 0, ',', ' ') . " FCFA</td>
                        <td>" . number_format($ligne->montant, 0, ',', ' ') . " FCFA</td>
                    </tr>";
        }
        
        $html .= "
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='4' style='text-align:right;'><strong>Total HT :</strong></td>
                        <td class='total'>" . number_format($devis->montant_ht, 0, ',', ' ') . " FCFA</td>
                    </tr>
                    <tr>
                        <td colspan='4' style='text-align:right;'><strong>TVA (18%) :</strong></td>
                        <td>" . number_format($devis->montant_tva, 0, ',', ' ') . " FCFA</td>
                    </tr>
                    <tr>
                        <td colspan='4' style='text-align:right;'><strong>Total TTC :</strong></td>
                        <td class='total'>" . number_format($devis->montant_ttc, 0, ',', ' ') . " FCFA</td>
                    </tr>
                </tfoot>
            </table>
            
            <p style='margin-top: 50px; font-size: 0.9em;'>
                Ce devis est valable jusqu'au {$devis->date_validite->format('d/m/Y')}. <br>
                Après approbation, le paiement intégral est requis avant le début des travaux.
            </p>
        </body>
        </html>
        ";
        
        return $html;
    }
}

