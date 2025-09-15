<?php

namespace App\Events;

use App\Models\FactoryOrderDelivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryCreated
{
    use Dispatchable, SerializesModels;

    public $delivery;

    /**
     * Create a new event instance.
     */
    public function __construct(FactoryOrderDelivery $delivery)
    {
        $this->delivery = $delivery;
    }
}
