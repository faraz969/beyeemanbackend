<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeSetting;
use Illuminate\Http\Request;

class FeeSettingController extends Controller
{
    /**
     * Show the form for editing fee settings.
     */
    public function edit()
    {
        $settings = FeeSetting::getSettings();
        
        return view('admin.fee-settings.edit', [
            'settings' => $settings
        ]);
    }

    /**
     * Update fee settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'processing_fee_type' => 'required|in:percentage,fixed',
            'processing_fee_value' => 'required|numeric|min:0',
            'platform_fee_type' => 'required|in:percentage,fixed',
            'platform_fee_value' => 'required|numeric|min:0',
            'processing_fee_applicable_to' => 'required|in:customer,vendor,both',
            'platform_fee_applicable_to' => 'required|in:customer,vendor,both',
        ]);

        // Validate percentage values (should be 0-100)
        if ($validated['processing_fee_type'] === 'percentage' && $validated['processing_fee_value'] > 100) {
            return back()->withErrors(['processing_fee_value' => 'Percentage cannot exceed 100%'])->withInput();
        }

        if ($validated['platform_fee_type'] === 'percentage' && $validated['platform_fee_value'] > 100) {
            return back()->withErrors(['platform_fee_value' => 'Percentage cannot exceed 100%'])->withInput();
        }

        FeeSetting::updateSettings($validated);

        return redirect()->route('admin.fee-settings.edit')
            ->with('success', 'Fee settings updated successfully.');
    }
}
