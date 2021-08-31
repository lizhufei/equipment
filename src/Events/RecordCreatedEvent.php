<?php


namespace Hsvisus\Equipment\Events;

use Hsvisus\Equipment\Models\Face;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecordCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $record;

    public function __construct(Face $record)
    {
        $this->record = $record;
    }

}
