<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name', 'price', 'status', 
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function folios() {
        return $this->hasMany(Folio::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function customers() {
        return $this->belongsToMany(Customer::class)->withPivot('quantity');
    }
}
