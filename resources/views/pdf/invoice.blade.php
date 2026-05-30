<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $commande->numero_commande }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        .company-info h1 {
            font-size: 24px;
            margin: 0 0 5px;
            color: #111827;
        }
        .company-info p {
            margin: 3px 0;
            color: #4b5563;
        }
        .invoice-title h2 {
            font-size: 28px;
            margin: 0;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .invoice-details {
            text-align: right;
            margin-top: 10px;
        }
        .invoice-details p {
            margin: 3px 0;
        }
        .client-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .address-box {
            width: 45%;
        }
        .address-box h3 {
            font-size: 14px;
            margin: 0 0 10px;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }
        .address-box p {
            margin: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f3f4f6;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #9ca3af;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 5px 0;
        }
        .totals .total-line {
            border-top: 2px solid #9ca3af;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .legal {
            font-size: 10px;
            color: #6b7280;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- En-tête -->
    <div class="header">
        <div class="company-info">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            <p>Tél : {{ $company['phone'] }}</p>
            <p>Email : {{ $company['email'] }}</p>
            <p>SIRET : {{ $company['siret'] }}</p>
            <p>TVA : {{ $company['tva'] }}</p>
        </div>
        <div class="invoice-title">
            <h2>FACTURE</h2>
            <div class="invoice-details">
                <p><strong>N° {{ $commande->numero_commande }}</strong></p>
                <p>Date : {{ $commande->date_commande->format('d/m/Y') }}</p>
                @if($commande->date_paiement)
                    <p>Payée le : {{ $commande->date_paiement->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Adresses -->
    <div class="client-section">
        <div class="address-box">
            <h3>FACTURÉ À</h3>
            <p><strong>{{ $commande->client->full_name ?? 'Client' }}</strong></p>
            @if($commande->adresseFacturation)
                <p>{{ $commande->adresseFacturation->rue }}</p>
                @if($commande->adresseFacturation->complement)
                    <p>{{ $commande->adresseFacturation->complement }}</p>
                @endif
                <p>{{ $commande->adresseFacturation->code_postal }} {{ $commande->adresseFacturation->ville }}</p>
                <p>{{ $commande->adresseFacturation->pays }}</p>
            @else
                <p>Adresse non renseignée</p>
            @endif
        </div>
        <div class="address-box">
            <h3>LIVRÉ À</h3>
            @if($commande->adresseLivraison)
                <p>{{ $commande->adresseLivraison->rue }}</p>
                @if($commande->adresseLivraison->complement)
                    <p>{{ $commande->adresseLivraison->complement }}</p>
                @endif
                <p>{{ $commande->adresseLivraison->code_postal }} {{ $commande->adresseLivraison->ville }}</p>
                <p>{{ $commande->adresseLivraison->pays }}</p>
            @else
                <p>Adresse non renseignée</p>
            @endif
        </div>
    </div>

    <!-- Lignes de commande -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Qté</th>
                <th class="text-right">Prix unitaire HT</th>
                <th class="text-right">Taxe</th>
                <th class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->lignes as $ligne)
                <tr>
                    <td>
                        {{ $ligne->produit->nom ?? 'Produit' }}
                        @if($ligne->variante)
                            <br><small>Variante : {{ $ligne->variante->valeur }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $ligne->quantite }}</td>
                    <td class="text-right">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ number_format($ligne->taxe, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ number_format($ligne->prix_total, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="totals">
        <table>
            <tr>
                <td>Sous-total HT :</td>
                <td class="text-right">{{ number_format($commande->sous_total, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td>TVA :</td>
                <td class="text-right">{{ number_format($commande->taxe, 2, ',', ' ') }} €</td>
            </tr>
            @if($commande->frais_livraison > 0)
                <tr>
                    <td>Frais de livraison :</td>
                    <td class="text-right">{{ number_format($commande->frais_livraison, 2, ',', ' ') }} €</td>
                </tr>
            @endif
            @if($commande->total_remises > 0)
                <tr>
                    <td>Remises :</td>
                    <td class="text-right">-{{ number_format($commande->total_remises, 2, ',', ' ') }} €</td>
                </tr>
            @endif
            <tr class="total-line">
                <td><strong>Total TTC :</strong></td>
                <td class="text-right"><strong>{{ number_format($commande->total, 2, ',', ' ') }} €</strong></td>
            </tr>
        </table>
    </div>

    <!-- Notes -->
    @if($commande->notes)
        <div style="margin-top: 30px;">
            <strong>Notes :</strong>
            <p>{{ $commande->notes }}</p>
        </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p>{{ $company['name'] }} - SIRET {{ $company['siret'] }} - TVA {{ $company['tva'] }}</p>
    </div>

    <div class="legal">
        <p>En cas de retard de paiement, des pénalités de retard calculées sur la base de trois fois le taux d'intérêt légal en vigueur seront exigibles. Conformément à l'article L. 441-10 du code de commerce, une indemnité forfaitaire pour frais de recouvrement de 40 € est due en cas de retard de paiement.</p>
    </div>
</div>
</body>
</html>
