<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Benefício</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .header {
            font-size: 18px;
            font-weight: bold;
        }
        .info {
            margin-top: 20px;
            text-align: left;
        }
        .signature {
            margin-top: 40px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid black;
            width: 200px;
            margin: 10px auto;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">Recibo de Entrega de Benefício</div>
    <div class="info">
        <p><strong>Benefício:</strong> {{ $benefitDelivery->benefit->name }}</p>
        <p><strong>Nome do Beneficiário:</strong> {{ $benefitDelivery->person->name }}</p>
        <p><strong>CPF:</strong> {{ $benefitDelivery->person->cpf }}</p>
        <p><strong>Data da Entrega:</strong> {{ $benefitDelivery->delivered_at ? $benefitDelivery->delivered_at->format('d/m/Y H:i') : 'Pendente' }}</p>
        <p><strong>Entregue por:</strong> {{ $benefitDelivery->delivered_by_id ? $benefitDelivery->deliveredBy->name : 'Não informado' }}</p>
    </div>

    <div class="signature">
        <p>______________________________________</p>
        <p>Assinatura do Beneficiário</p>
    </div>

    <div class="signature">
        <p>______________________________________</p>
        <p>Assinatura do Responsável</p>
    </div>
</div>
</body>
</html>
