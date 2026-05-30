<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AdminOrderController extends Controller
{
    /**
     * Génère une facture PDF pour une commande.
     */
    public function adminOrdersInvoice(Commande $commande): Response
    {
        // Charger les relations nécessaires
        $commande->load([
            'client',
            'adresseFacturation',
            'adresseLivraison',
            'lignes.produit',
            'lignes.variante',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', [
            'commande' => $commande,
            'company' => [
                'name' => config('app.name'),
                'address' => config('company_address', 'Votre adresse'),
                'email' => config('company_email', 'contact@example.com'),
                'phone' => config('company_phone', '+33 1 23 45 67 89'),
                'siret' => config('company_siret', '123 456 789 00012'),
                'tva' => config('company_tva', 'FR12345678900'),
            ],
        ]);

        // Options PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true, // pour les images distantes
        ]);

        $filename = sprintf('facture-%s-%s.pdf',
            $commande->numero_commande,
            $commande->date_commande->format('Ymd')
        );

        return $pdf->download($filename);
    }
}
