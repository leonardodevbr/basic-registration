<?php

namespace App\Services;

use GuzzleHttp\Exception\GuzzleException;
use Pusher\ApiErrorException;
use Pusher\Pusher;
use Pusher\PusherException;

class RealtimeNotificationService
{
    /**
     * @throws PusherException
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public static function trigger(string $channel, string $event, array $data): void
    {
        $pusher = new Pusher(
            config('services.pusher.key'),
            config('services.pusher.secret'),
            config('services.pusher.app_id'),
            config('services.pusher.options')
        );
        $pusher->trigger($channel, $event, $data);

    }
}
