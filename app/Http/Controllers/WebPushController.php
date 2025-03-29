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
        $userId = auth()->id();

        if (!$subscription || !$userId) {
            return response()->json(['error' => 'Subscription inválida'], 400);
        }

        $subscriptionArray = json_decode($subscription, true);

        $allSubscriptions = [];
        $filePath = storage_path('push-subscriptions.json');
        if (file_exists($filePath)) {
            $allSubscriptions = json_decode(file_get_contents($filePath), true) ?? [];
        }

        $allSubscriptions[$userId] = $subscriptionArray;

        file_put_contents($filePath, json_encode($allSubscriptions, JSON_PRETTY_PRINT));

        return response()->json(['success' => 'Inscrição salva!']);
    }


    public function sendNotification(BenefitDelivery $benefitDelivery)
    {
        $filePath = storage_path('push-subscriptions.json');

        if (!file_exists($filePath)) return;

        $allSubscriptions = json_decode(file_get_contents($filePath), true);
        $currentUserId = auth()->id();

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);

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
                'url' => route('benefit-deliveries.index') . "?highlight={$benefitDelivery->id}",
            ]
        ];

        foreach ($allSubscriptions as $userId => $subscriptionData) {
            if ((int)$userId === (int)$currentUserId) continue;

            try {
                $subscription = Subscription::create($subscriptionData);
                $webPush->queueNotification($subscription, json_encode($options));
            } catch (\Throwable $e) {
                \Log::error("Erro ao criar subscription: " . $e->getMessage());
            }
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                Log::info("Notificação enviada com sucesso!");
            } else {
                Log::error("Erro ao enviar notificação: ".$report->getReason());
            }
        }
    }

}
