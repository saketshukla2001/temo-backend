<?php

namespace App\Helpers\Billing;

use App\Models\Admin\PlatformFee;

class PlatformFeeHelper
{
    public static function applyToCalculatedBill(array $bill)
    {
        $userFee = self::calculate('user', $bill['total_amount'] ?? 0);
        $driverFee = self::calculate('driver', $bill['driver_commision'] ?? 0);

        $bill['platform_fee_for_user'] = $userFee;
        $bill['platform_fee_for_driver'] = $driverFee;
        $bill['total_amount'] = ($bill['total_amount'] ?? 0) + $userFee;
        $bill['driver_commision'] = max(0, ($bill['driver_commision'] ?? 0) - $driverFee);
        $bill['admin_commision_with_tax'] = ($bill['admin_commision_with_tax'] ?? 0) + $driverFee;

        return $bill;
    }

    private static function calculate($userType, $baseAmount)
    {
        $platformFee = PlatformFee::where('user_type', $userType)
            ->where('active', true)
            ->latest()
            ->first();

        if (!$platformFee) {
            return 0;
        }

        if ($platformFee->fee_type === 'percentage') {
            return round($baseAmount * ($platformFee->amount / 100), 2);
        }

        return round($platformFee->amount, 2);
    }
}
