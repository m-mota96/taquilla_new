<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'initial_date', 'final_date', 'status', 
    ];

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
