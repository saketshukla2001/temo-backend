<?php

namespace App\Helpers\Subscriptions;

use App\Models\Admin\Driver;
use App\Models\Payment\DriverSubscription;
use Carbon\Carbon;

class DriverSubscriptionHelper
{
    public static function activeSubscriptionForDriver($driverId)
    {
        if (!$driverId) {
            return null;
        }

        // Day-wise expired subscriptions ko inactive mark karo.
        DriverSubscription::where('driver_id', $driverId)
            ->where('active', true)
            ->where('subscription_basis', 'day_wise')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', Carbon::now())
            ->update([
                'active' => false,
            ]);

        // Ride-wise exhausted subscriptions ko inactive mark karo.
        DriverSubscription::where('driver_id', $driverId)
            ->where('active', true)
            ->where('subscription_basis', 'ride_wise')
            ->where(function ($query) {
                $query->whereNull('remaining_rides')
                    ->orWhere('remaining_rides', '<=', 0);
            })
            ->update([
                'active' => false,
                'expired_at' => Carbon::now(),
            ]);

        return DriverSubscription::where('driver_id', $driverId)
            ->usable()
            ->latest()
            ->first();
    }

    public static function driverHasActiveSubscription($driverId)
    {
        return (bool) self::activeSubscriptionForDriver($driverId);
    }

    public static function applyToCalculatedBill(array $bill, ?Driver $driver)
    {
        if (!$driver || !self::driverHasActiveSubscription($driver->id)) {
            return $bill;
        }

        $adminFromDriver = $bill['admin_commision_from_driver'] ?? 0;

        $bill['driver_commision'] = ($bill['driver_commision'] ?? 0) + $adminFromDriver;
        $bill['admin_commision'] = 0;
        $bill['service_tax'] = 0;
        $bill['service_tax_percentage'] = 0;
        $bill['admin_commision_from_driver'] = 0;
        $bill['admin_commision_with_tax'] = 0;

        return $bill;
    }

    public static function consumeRideIfNeeded($driverId)
    {
        $subscription = self::activeSubscriptionForDriver($driverId);

        if (!$subscription || $subscription->subscription_basis !== 'ride_wise') {
            return;
        }

        $subscription->used_rides = ((int) $subscription->used_rides) + 1;
        $subscription->remaining_rides = max(0, ((int) $subscription->remaining_rides) - 1);

        if ($subscription->remaining_rides <= 0) {
            $subscription->active = false;
            $subscription->expired_at = Carbon::now();
        }

        $subscription->save();
    }
}
