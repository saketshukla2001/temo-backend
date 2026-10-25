<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\BaseController;
use App\Models\Payment\DriverSubscriptionPlan;
use Illuminate\Http\Request;

class DriverSubscriptionController extends BaseController
{
    public function index()
    {
        $page = 'Subscription Plans';
        $main_menu = 'driver-subscriptions';
        $sub_menu = '';

        return view('admin.driver-subscriptions.index', compact('page', 'main_menu', 'sub_menu'));
    }

    public function fetch(Request $request)
    {
        $results = DriverSubscriptionPlan::query()
            ->when($request->search, function ($query) use ($request) {
                $keyword = $request->search;
                $query->where('name', 'LIKE', "%{$keyword}%");
            })
            ->latest()
            ->paginate();

        return view('admin.driver-subscriptions._subscriptions', compact('results'))->render();
    }

    public function create()
    {
        $page = 'Add Subscription Plan';
        $main_menu = 'driver-subscriptions';
        $sub_menu = '';

        return view('admin.driver-subscriptions.create', compact('page', 'main_menu', 'sub_menu'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subscription_basis' => 'required|in:day_wise,ride_wise',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required_if:subscription_basis,day_wise|nullable|integer|min:1',
            'total_rides' => 'required_if:subscription_basis,ride_wise|nullable|integer|min:1',
        ]);

        DriverSubscriptionPlan::create([
            'name' => $validated['name'],
            'subscription_basis' => $validated['subscription_basis'],
            'price' => $validated['price'],
            'duration_days' => $validated['duration_days'] ?? null,
            'total_rides' => $validated['total_rides'] ?? null,
            'active' => true,
        ]);

        return redirect('driver-subscriptions')->with('success', 'Subscription plan added successfully');
    }

    public function edit(DriverSubscriptionPlan $subscription)
    {
        if ((float) $subscription->price <= 0) {
            return redirect('driver-subscriptions')->with('warning', 'Free plans cannot be edited');
        }

        $page = 'Edit Subscription Plan';
        $main_menu = 'driver-subscriptions';
        $sub_menu = '';

        return view('admin.driver-subscriptions.edit', compact('page', 'main_menu', 'sub_menu', 'subscription'));
    }

    public function update(Request $request, DriverSubscriptionPlan $subscription)
    {
        if ((float) $subscription->price <= 0) {
            return redirect('driver-subscriptions')->with('warning', 'Free plans cannot be edited');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subscription_basis' => 'required|in:day_wise,ride_wise',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required_if:subscription_basis,day_wise|nullable|integer|min:1',
            'total_rides' => 'required_if:subscription_basis,ride_wise|nullable|integer|min:1',
        ]);

        $subscription->update([
            'name' => $validated['name'],
            'subscription_basis' => $validated['subscription_basis'],
            'price' => $validated['price'],
            'duration_days' => $validated['subscription_basis'] === 'day_wise' ? ($validated['duration_days'] ?? null) : null,
            'total_rides' => $validated['subscription_basis'] === 'ride_wise' ? ($validated['total_rides'] ?? null) : null,
        ]);

        return redirect('driver-subscriptions')->with('success', 'Subscription plan updated successfully');
    }

    public function toggleStatus(DriverSubscriptionPlan $subscription)
    {
        $subscription->update([
            'active' => !$subscription->active,
        ]);

        return redirect('driver-subscriptions')->with('success', 'Subscription plan status updated successfully');
    }
}
