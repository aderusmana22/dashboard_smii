<?php

namespace App\Events;

use App\Models\JobMarsho;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public JobMarsho $job;
    public string $html;

    /**
     * Create a new event instance.
     */
    public function __construct(JobMarsho $job, string $html)
    {
        $this->job = $job;
        $this->html = $html;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('jobs'),
        ];
    }
}