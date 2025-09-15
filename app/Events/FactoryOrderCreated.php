<?php

namespace App\Events;

use App\Models\FactoryOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FactoryOrderCreated
{
    use Dispatchable, SerializesModels;

    public $factoryOrder;

    /**
     * Create a new event instance.
     */
    public function __construct(FactoryOrder $factoryOrder)
    {
        $this->factoryOrder = $factoryOrder;
    }
}
