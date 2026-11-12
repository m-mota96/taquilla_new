<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'sold', 'payment_method', 'date'
    ];

    public $timestamps = false;

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }
}
