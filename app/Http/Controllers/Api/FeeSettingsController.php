<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeSetting;

class FeeSettingsController extends Controller
{
    /**
     * Get fee settings (public endpoint for checkout calculations)
     */
    public function index()
    {
        $feeSettings = FeeSetting::getSettings();

        return response()->json([
            'success' => true,
            'data' => [
                'processing_fee_type' => $feeSettings->processing_fee_type,
                'processing_fee_value' => (float)$feeSettings->processing_fee_value,
                'processing_fee_applicable_to' => $feeSettings->processing_fee_applicable_to,
                'platform_fee_type' => $feeSettings->platform_fee_type,
                'platform_fee_value' => (float)$feeSettings->platform_fee_value,
                'platform_fee_applicable_to' => $feeSettings->platform_fee_applicable_to,
            ],
        ]);
    }
}

