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
        $userId = auth()->id(); // Pegue o ID do usuário logado

        if (!$subscription || !$userId) {
            return response()->json(['error' => 'Subscription inválida'], 400);
        }

        $allSubscriptions = json_decode(file_get_contents(storage_path('push-subscriptions.json')), true) ?? [];
        $allSubscriptions[$userId] = json_decode($subscription, true);

        file_put_contents(storage_path('push-subscriptions.json'), json_encode($allSubscriptions));

        return response()->json(['success' => 'Inscrição salva!']);
    }


    public function sendNotification(BenefitDelivery $benefitDelivery)
    {
        $allSubscriptions = json_decode(file_get_contents(storage_path('push-subscriptions.json')), true);
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);

        $currentUserId = auth()->id();

        foreach ($allSubscriptions as $userId => $subscriptionData) {
            if ((int)$userId === (int)$currentUserId) {
                continue; // pula o autor
            }

            $subscription = Subscription::create($subscriptionData);

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
        }

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
