<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bon de livraison {{ $order->bon_livraison_numero }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 24px; border-bottom: 2px solid #333; padding-bottom: 12px; }
        .title { font-size: 18px; font-weight: bold; }
        .numero { font-size: 14px; color: #555; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 10px; color: #666; }
        .signature { margin-top: 40px; }
        .signature-line { border-bottom: 1px solid #333; width: 200px; margin-top: 24px; padding-top: 4px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Bon de livraison</div>
        <div class="numero">{{ $order->bon_livraison_numero }}</div>
        <div style="margin-top: 8px;">Commande : {{ $order->commande_numero }} — Date : {{ $order->updated_at->format('d/m/Y') }}</div>
    </div>

    <table>
        <tr>
            <th>Client</th>
            <td>{{ $order->client->users ?? '—' }}</td>
        </tr>
        <tr>
            <th>Produit</th>
            <td>{{ $order->produit->libelle ?? '—' }}</td>
        </tr>
        <tr>
            <th>Quantité</th>
            <td>{{ $order->quantite }}</td>
        </tr>
    </table>

    <div class="signature">
        <div class="signature-line">Signature client</div>
    </div>

    <div class="footer" style="margin-top: 48px;">
        Document généré le {{ now()->format('d/m/Y à H:i') }} — Bon de livraison {{ $order->bon_livraison_numero }}
    </div>
</body>
</html>
