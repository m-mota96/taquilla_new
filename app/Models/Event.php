<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'initial_date', 'final_date', 'status', 'uuid_events_conferences',
    ];

    public function setting() {
        return $this->hasOne(EventSetting::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    /**
     * Modo de venta del evento. Si no tiene configuracion se usa el modo original.
     */
    public function saleMode() {
        return $this->setting ? (int) $this->setting->sale_mode : EventSetting::SALE_MODE_PER_PURCHASE;
    }

    public function isPerPersonMode() {
        return $this->saleMode() === EventSetting::SALE_MODE_PER_PERSON;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }

    public function tickets() {
        return $this->hasMany(Ticket::class)->orderBy('name', 'ASC');
    }

    public function cuts() {
        return $this->hasMany(Cut::class);
    }
}
