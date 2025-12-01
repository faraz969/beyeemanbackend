<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'processing_fee_type',
        'processing_fee_value',
        'platform_fee_type',
        'platform_fee_value',
        'processing_fee_applicable_to',
        'platform_fee_applicable_to',
    ];

    protected $casts = [
        'processing_fee_value' => 'decimal:2',
        'platform_fee_value' => 'decimal:2',
    ];

    /**
     * Get the single fee setting instance (singleton pattern)
     */
    public static function getSettings()
    {
        $settings = self::first();
        
        // If no settings exist, create default ones
        if (!$settings) {
            $settings = self::create([
                'processing_fee_type' => 'percentage',
                'processing_fee_value' => 2.5,
                'platform_fee_type' => 'percentage',
                'platform_fee_value' => 5.0,
                'processing_fee_applicable_to' => 'customer',
                'platform_fee_applicable_to' => 'vendor',
            ]);
        }
        
        return $settings;
    }

    /**
     * Update or create fee settings (singleton pattern)
     */
    public static function updateSettings(array $data)
    {
        $settings = self::first();
        
        if ($settings) {
            $settings->update($data);
        } else {
            $settings = self::create($data);
        }
        
        return $settings;
    }
}
