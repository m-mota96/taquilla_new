<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'ticket_id', 'position_id', 'name', 'email', 'country_code', 'phone', 'qr', 'conference_ticket_id', 'conference_sent_at',
    ];

    protected $casts = [
        'conference_sent_at' => 'datetime',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }

    public function position() {
        return $this->belongsTo(Position::class);
    }

    /**
     * Datos del gafete, iguales a los que imprime conferencias (badge90x150ZPL).
     * Null si aun no se tiene el id del ticket en conferencias (va en el QR).
     */
    public function badgeData() {
        if (empty($this->conference_ticket_id)) {
            return null;
        }
        $this->loadMissing(['ticket', 'position']);
        return [
            'ticket_id'   => $this->conference_ticket_id,
            'name'        => $this->name,
            'occupation'  => $this->position->name,
            'ticket_name' => $this->ticket->name,
        ];
    }

    /**
     * Asistentes de un evento que aun no llegan a la API de conferencias.
     */
    public function scopePendingConference($query, $event_id) {
        return $query->whereNull('conference_sent_at')
            ->whereHas('order', function ($q) use ($event_id) {
                $q->where('event_id', $event_id);
            });
    }
}
