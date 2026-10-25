<?php

namespace App\Http\Controllers\Api\V1\Subscription;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Payment\DriverSubscription;
use App\Models\Payment\DriverSubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends BaseController
{
    public function plans()
    {
        return $this->respondSuccess(
            DriverSubscriptionPlan::where('active', true)->latest()->get(),
            'subscription_plans_listed'
        );
    }

    public function mySubscription()
    {
        $driver = auth()->user()->driver;

        if (!$driver) {
            $this->throwCustomException('driver profile not found');
        }

        $subscription = DriverSubscription::where('driver_id', $driver->id)
            ->where('active', true)
            ->latest()
            ->first();

        return $this->respondSuccess($subscription, 'driver_subscription_detail');
    }

    public function buy(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:driver_subscription_plans,id',
            'payment_transaction_id' => 'required|string|max:255',
            'payment_status' => 'required|string|max:255',
        ]);

        $driver = auth()->user()->driver;

        if (!$driver) {
            $this->throwCustomException('driver profile not found');
        }

        $plan = DriverSubscriptionPlan::where('active', true)->findOrFail($validated['plan_id']);
        $startedAt = Carbon::now();
        $isPaid = strtolower($validated['payment_status']) === 'success' || strtolower($validated['payment_status']) === 'paid';

        DriverSubscription::where('driver_id', $driver->id)
            ->where('active', true)
            ->update(['active' => false]);

        $subscription = DriverSubscription::create([
            'driver_id' => $driver->id,
            'plan_id' => $plan->id,
            'subscription_basis' => $plan->subscription_basis,
            'subscription_type' => null,
            'paid_amount' => $plan->price,
            'duration_days' => $plan->duration_days,
            'total_rides' => $plan->total_rides,
            'used_rides' => 0,
            'remaining_rides' => $plan->total_rides,
            'started_at' => $startedAt,
            'expired_at' => $plan->subscription_basis === 'day_wise' ? $startedAt->copy()->addDays($plan->duration_days) : null,
            'transaction_id' => $validated['payment_transaction_id'],
            'payment_status' => $validated['payment_status'],
            'active' => $isPaid,
        ]);

        return $this->respondSuccess($subscription, 'subscription_purchased');
    }
}
