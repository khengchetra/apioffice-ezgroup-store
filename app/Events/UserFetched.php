<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFetched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userData;

    public function __construct($userData)
    {
        $this->userData = $userData;
    }

    public function broadcastOn()
    {
        return new Channel('users');
    }

    public function broadcastAs()
    {
        return 'user.fetched';
    }

    // បន្ថែម method នេះដើម្បីធានាថាទិន្នន័យត្រូវបានផ្ញើ
    public function broadcastWith()
    {
        return [
            'userData' => $this->userData
        ];
    }
}