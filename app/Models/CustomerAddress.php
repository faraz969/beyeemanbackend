<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'label',
        'address',
        'latitude',
        'longitude',
        'country',
        'street',
        'city',
        'is_default',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}

