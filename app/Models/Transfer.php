<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'vendor_wallet_id',
        'transfer_reference',
        'recipient_code',
        'transfer_code',
        'amount',
        'processing_fee',
        'currency',
        'status',
        'reason',
        'failure_reason',
        'paystack_response',
        'transferred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'paystack_response' => 'array',
        'transferred_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vendorWallet()
    {
        return $this->belongsTo(VendorWallet::class);
    }
}
