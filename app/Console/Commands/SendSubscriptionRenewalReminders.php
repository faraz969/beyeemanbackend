<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorSubscription;
use App\Services\ArkeselSmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSubscriptionRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-renewal-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS reminders to vendors whose subscriptions are expiring in 5 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for subscriptions expiring in 5 days...');

        // Calculate the date 5 days from now
        $fiveDaysFromNow = Carbon::now()->addDays(5)->startOfDay();
        $fiveDaysFromNowEnd = Carbon::now()->addDays(5)->endOfDay();

        // Find active subscriptions expiring in 5 days
        $expiringSubscriptions = VendorSubscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$fiveDaysFromNow, $fiveDaysFromNowEnd])
            ->with(['vendor.user', 'package'])
            ->get();

        if ($expiringSubscriptions->isEmpty()) {
            $this->info('No subscriptions expiring in 5 days.');
            return 0;
        }

        $this->info("Found {$expiringSubscriptions->count()} subscription(s) expiring in 5 days.");

        $smsService = new ArkeselSmsService();
        $successCount = 0;
        $failureCount = 0;

        foreach ($expiringSubscriptions as $subscription) {
            $vendor = $subscription->vendor;
            $user = $vendor->user;
            $package = $subscription->package;

            // Get vendor phone number
            $phoneNumber = $user->phone ?? $vendor->phone;

            if (!$phoneNumber) {
                $this->warn("Skipping vendor ID {$vendor->id}: No phone number found.");
                $failureCount++;
                continue;
            }

            // Format expiry date
            $expiryDate = $subscription->expires_at->format('d/m/Y');
            $packageName = $package->name ?? 'your subscription';

            // Create SMS message
            $message = "Your Package ({$packageName}) is about to expire on {$expiryDate}. Please renew it to continue using our services. - Beyeeman";

            // Send SMS
            $result = $smsService->sendSms($phoneNumber, $message);

            if ($result['success']) {
                $this->info("✓ SMS sent to vendor ID {$vendor->id} ({$phoneNumber})");
                $successCount++;
                
                Log::info('Subscription Renewal Reminder Sent', [
                    'vendor_id' => $vendor->id,
                    'subscription_id' => $subscription->id,
                    'phone' => $phoneNumber,
                    'expiry_date' => $subscription->expires_at->format('Y-m-d'),
                    'balance' => $result['balance'] ?? null,
                ]);
            } else {
                $this->error("✗ Failed to send SMS to vendor ID {$vendor->id} ({$phoneNumber}): {$result['message']}");
                $failureCount++;
                
                Log::error('Subscription Renewal Reminder Failed', [
                    'vendor_id' => $vendor->id,
                    'subscription_id' => $subscription->id,
                    'phone' => $phoneNumber,
                    'error' => $result['message'],
                ]);
            }
        }

        $this->info("\nSummary:");
        $this->info("Successfully sent: {$successCount}");
        $this->info("Failed: {$failureCount}");

        return 0;
    }
}
