<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin: 0 0 4px; }
        .subtitle { margin: 0; color: #4b5563; }
        .meta { margin: 12px 0; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; }
        .meta p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; text-align: left; font-weight: 700; }
        .text-right { text-align: right; }
        .total-box { margin-top: 14px; text-align: right; font-size: 14px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Encomenda de Fornecedor</p>
        <p class="subtitle">Documento {{ $encomenda->numero }}</p>
    </div>

    <div class="meta">
        <p><strong>Número:</strong> {{ $encomenda->numero }}</p>
        <p><strong>Data:</strong> {{ $encomenda->data_encomenda }}</p>
        <p><strong>Fornecedor:</strong> {{ $encomenda->fornecedor_nome ?? '-' }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($encomenda->estado ?? 'rascunho') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Artigo</th>
                <th class="text-right">Qtd</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($linhas as $linha)
                <tr>
                    <td>{{ $linha->artigo_nome }}</td>
                    <td class="text-right">{{ number_format($linha->quantidade, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($linha->preco_unitario, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($linha->subtotal, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
            @if($linhas->isEmpty())
                <tr>
                    <td colspan="4">Sem linhas de artigo.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="total-box">
        Total: {{ number_format($encomenda->valor_total, 2, ',', '.') }} €
    </div>
</body>
</html>
