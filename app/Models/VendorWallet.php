<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'payment_type',
        'provider',
        'momo_number',
        'account_name',
        'bank_name',
        'account_number',
        'branch',
        'balance',
        'recipient_code',
        'bank_code',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}

