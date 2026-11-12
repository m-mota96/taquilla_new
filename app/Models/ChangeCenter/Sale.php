<?php

namespace App\Models\ChangeCenter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $connection = 'changeCenter';

    protected $fillable = [
        'customer_id', 'provider_id', 'amount', 
    ];

    public $timestamps = false;
}
