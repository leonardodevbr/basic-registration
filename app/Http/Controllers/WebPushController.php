<?php

namespace App\Http\Controllers;

use App\Models\BenefitDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushController extends Controller
{
    public function subscribe(Request $request)
    {
        $subscription = $request->getContent();

        if (!$subscription) {
            return response()->json(['error' => 'Subscription inválida'], 400);
        }

        file_put_contents(storage_path('push-subscriptions.json'), $subscription);

        return response()->json(['success' => 'Inscrição salva!']);
    }

    public function sendNotification(BenefitDelivery $benefitDelivery)
    {
        $subscriptionData = json_decode(file_get_contents(storage_path('push-subscriptions.json')), true);
        $subscription = Subscription::create($subscriptionData);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);

        // Dados da notificação
        $title = "Entrega atualizada";
        $statusLabel = match ($benefitDelivery->status) {
            'PENDING' => 'Pendente',
            'DELIVERED' => 'Entregue',
            'EXPIRED' => 'Expirada',
            'REISSUED' => 'Reemitida',
            default => 'Atualizada',
        };

        $options = [
            'title' => "Senha {$benefitDelivery->ticket_code} - {$statusLabel}",
            'body' => "Status alterado para: {$statusLabel}.",
            'icon' => asset('/img/logo.png'),
            'tag' => 'delivery-'.$benefitDelivery->id,
            'data' => [
                'url' => route('benefit-deliveries.index')."?highlight={$benefitDelivery->id}",
            ]
        ];

        $webPush->queueNotification($subscription, json_encode($options));

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                Log::info("Notificação enviada com sucesso!");
            } else {
                Log::error("Erro ao enviar notificação: ".$report->getReason());
            }
        }

        return response()->json(['success' => 'Notificação enviada!']);
    }
}
