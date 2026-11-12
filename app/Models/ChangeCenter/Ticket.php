<?php

namespace App\Models\ChangeCenter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $connection = 'changeCenter';

    protected $fillable = [
        'customer_id', 'winner', 
    ];

    public $timestamps = false;

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}
