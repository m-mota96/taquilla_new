<?php

namespace App\Models\ChangeCenter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $connection = 'changeCenter';

    protected $fillable = [
        'event_id', 'name', 'email', 'phone', 
    ];

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
}
