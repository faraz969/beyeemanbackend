<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_verified_at',
        'otp',
        'otp_expires_at',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function availabilityRequests()
    {
        return $this->hasMany(AvailabilityRequest::class, 'customer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'raised_by_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }
}
