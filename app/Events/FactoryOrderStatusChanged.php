<?php

namespace App\Events;

use App\Models\FactoryOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FactoryOrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public $factoryOrder;
    public $oldStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(FactoryOrder $factoryOrder, string $oldStatus)
    {
        $this->factoryOrder = $factoryOrder;
        $this->oldStatus = $oldStatus;
    }
}
