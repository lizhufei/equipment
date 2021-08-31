<?php


namespace Hsvisus\Equipment\Events;

use App\Models\Record;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecordCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $record;

    public function __construct(Record $record)
    {
        $this->record = $record;
    }

}
