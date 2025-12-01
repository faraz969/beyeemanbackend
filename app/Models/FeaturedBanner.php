<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'link_type',
        'link_id',
        'external_url',
        'order',
        'is_active',
    ];
}

