<?php

namespace App\Events;

use App\Models\FactoryOrderItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FactoryOrderItemCreated
{
    use Dispatchable, SerializesModels;

    public $orderItem;

    /**
     * Create a new event instance.
     */
    public function __construct(FactoryOrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }
}
