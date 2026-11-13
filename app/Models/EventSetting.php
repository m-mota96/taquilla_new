<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasFactory;

    /** Un solo registro (nombre, correo, telefono) por compra. Es el modo original. */
    const SALE_MODE_PER_PURCHASE = 1;

    /** Un formulario por cada boleto vendido, con cargo y QR. */
    const SALE_MODE_PER_PERSON = 2;

    protected $fillable = [
        'event_id', 'sale_mode', 'event_type',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
