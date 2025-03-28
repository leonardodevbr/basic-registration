<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SelfieUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public int $personId;
    public string $thumbUrl;

    public function __construct(int $personId, string $thumbUrl)
    {
        $this->personId = $personId;
        $this->thumbUrl = $thumbUrl;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('person-updates');
    }

    public function broadcastAs(): string
    {
        return 'selfie.updated';
    }
}
