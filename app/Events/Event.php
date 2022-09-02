<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

abstract class Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
