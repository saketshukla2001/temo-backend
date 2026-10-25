<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Web\BaseController;
use App\Models\Admin\Fleet;
use App\Models\Admin\SharingRide;
use App\Models\Admin\VehicleType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SharingRideController extends BaseController
{
    public function index($status = 'ongoing')
    {
        SharingRide::syncStatuses();

        $status = $this->normalizeStatus($status);
        $page = ucfirst($status).' Sharing Rides';
        $main_menu = 'sharing-rides';
        $sub_menu = $status;

        return view('admin.sharing-rides.index', compact('page', 'main_menu', 'sub_menu', 'status'));
    }

    public function fetch(Request $request, $status = 'ongoing')
    {
        SharingRide::syncStatuses();

        $status = $this->normalizeStatus($status);

        $results = SharingRide::with(['fleet.vehicleType', 'vehicleType', 'user'])
            ->companyKey()
            ->where('status', $status)
            ->when($request->search, function ($query) use ($request) {
                $keyword = $request->search;
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->whereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'LIKE', "%{$keyword}%")
                            ->orWhere('mobile', 'LIKE', "%{$keyword}%")
                            ->orWhere('email', 'LIKE', "%{$keyword}%");
                    })->orWhereHas('fleet', function ($fleetQuery) use ($keyword) {
                        $fleetQuery->where('license_number', 'LIKE', "%{$keyword}%")
                            ->orWhere('custom_make', 'LIKE', "%{$keyword}%")
                            ->orWhere('custom_model', 'LIKE', "%{$keyword}%");
                    })->orWhereHas('vehicleType', function ($vehicleTypeQuery) use ($keyword) {
                        $vehicleTypeQuery->where('name', 'LIKE', "%{$keyword}%");
                    });
                });
            })
            ->latest()
            ->paginate();

        return view('admin.sharing-rides._sharing-rides', compact('results', 'status'))->render();
    }

    public function create()
    {
        $page = 'Add Sharing Ride';
        $main_menu = 'sharing-rides';
        $sub_menu = '';
        $fleets = Fleet::with('vehicleType')->get();
        $vehicleTypes = VehicleType::get();
        $users = User::belongsToRole('user')->companyKey()->where('active', true)->get();

        return view('admin.sharing-rides.create', compact('page', 'main_menu', 'sub_menu', 'fleets', 'vehicleTypes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => 'required_without:fleet_id|exists:vehicle_types,id',
            'fleet_id' => 'nullable|exists:fleets,id',
            'user_id' => 'required|exists:users,id',
            'price_per_km' => 'required|numeric|min:0',
            'trip_start_time' => 'required|date',
        ]);

        $fleet = !empty($validated['fleet_id']) ? Fleet::with('vehicleType')->find($validated['fleet_id']) : null;
        $vehicleType = $fleet && $fleet->vehicleType
            ? $fleet->vehicleType
            : VehicleType::findOrFail($validated['vehicle_type_id']);
        $capacity = $vehicleType->capacity ? $vehicleType->capacity : 1;

        SharingRide::create([
            'fleet_id' => $validated['fleet_id'] ?? null,
            'vehicle_type_id' => $vehicleType->id,
            'user_id' => $validated['user_id'],
            'price_per_km' => $validated['price_per_km'],
            'total_capacity' => $capacity,
            'trip_start_time' => $validated['trip_start_time'],
            'status' => Carbon::parse($validated['trip_start_time'])->lte(Carbon::now()) ? 'ongoing' : 'upcoming',
            'company_key' => auth()->user()->company_key,
        ]);

        return redirect('sharing-rides')->with('success', 'Sharing ride added successfully');
    }

    public function toggleStatus(SharingRide $sharingRide)
    {
        $sharingRide->update([
            'active' => !$sharingRide->active,
        ]);

        return redirect('sharing-rides')->with('success', 'Sharing ride status updated successfully');
    }

    public function markCompleted(SharingRide $sharingRide)
    {
        $sharingRide->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'active' => false,
        ]);

        return redirect('sharing-rides/completed')->with('success', 'Sharing ride marked as completed');
    }

    public function markCancelled(Request $request, SharingRide $sharingRide)
    {
        $sharingRide->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancel_reason' => $request->cancel_reason,
            'active' => false,
        ]);

        return redirect('sharing-rides/cancelled')->with('success', 'Sharing ride marked as cancelled');
    }

    private function normalizeStatus($status)
    {
        return in_array($status, ['upcoming', 'ongoing', 'completed', 'cancelled']) ? $status : 'ongoing';
    }

}
