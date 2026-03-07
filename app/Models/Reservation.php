<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'name',
        'classType',
        'time',
        'date',
    ];

    protected $casts = [
        'time' => 'datetime:H:i:s',
        'date' => 'date',
    ];
}