<?php

namespace App\Events;
use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public Product $product) {}

    // Canal público para que todos vean los cambios
    public function broadcastOn(): array
    {
        return [new Channel('stock-updates')];
    }

    // Nombre personalizado del evento
    public function broadcastAs(): string
    {
        return 'stock.updated';
    }

    // Datos que se enviarán al frontend
    public function broadcastWith(): array
    {
        return [
            'id' => $this->product->id,
            'name' => $this->product->name,
            'stock' => $this->product->stock,
            'updated_at' => $this->product->updated_at->toDateTimeString(),
            'action' => 'updated'
        ];
    }
}
