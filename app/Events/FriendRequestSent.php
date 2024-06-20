<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class FriendRequestSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $sender;
    public $receiver;

    public function __construct(User $sender, User $receiver)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('friends.' . $this->receiver->id);
    }

    public function broadcastWith()
    {
        return [
            'message' => "{$this->sender->name} vous a envoyé une demande d'ami."
        ];
    }
}
