<?php

namespace App\Http\Controllers\Api\V1\SharingRide;

use App\Base\Constants\Masters\PushEnums;
use App\Http\Controllers\Api\V1\BaseController;
use App\Jobs\Notifications\SendPushNotification;
use App\Models\Admin\SharingRide;
use App\Models\Admin\SharingRideBooking;
use App\Models\Admin\VehicleType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharingRideController extends BaseController
{
    public function index(Request $request)
    {
        SharingRide::syncStatuses();

        $user = auth()->user();
        $status = $request->input('status');
        $isMyRide = filter_var($request->input('isMyride'), FILTER_VALIDATE_BOOLEAN);

        $query = SharingRide::with(['vehicleType', 'user', 'bookings.user']);

        if ($isMyRide) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('bookings', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
            });
        }

        if ($status) {
            if ($status === 'upcomming') {
                $status = 'upcoming';
            }

            if (!in_array($status, ['upcoming', 'ongoing', 'completed'])) {
                $this->throwCustomException('invalid sharing ride status');
            }

            $query->where('status', $status);

            if (in_array($status, ['upcoming', 'ongoing'])) {
                $query->where('active', true);
            }
        } else {
            $query->whereIn('status', ['upcoming', 'ongoing'])
                ->where('active', true);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('pick_address', 'LIKE', "%{$search}%")
                    ->orWhere('drop_address', 'LIKE', "%{$search}%");
            });
        }

        $pickAddress = trim((string) (
            $request->input('pick_address')
            ?? $request->input('pickup_address')
            ?? ''
        ));
        if ($pickAddress !== '') {
            $query->where('pick_address', 'LIKE', "%{$pickAddress}%");
        }

        $dropAddress = trim((string) $request->input('drop_address', ''));
        if ($dropAddress !== '') {
            $query->where('drop_address', 'LIKE', "%{$dropAddress}%");
        }

        $rides = $query->latest()->paginate();

        $rides->getCollection()->transform(function ($ride) use ($user) {
            $currentUserBooking = $ride->bookings
                ->where('user_id', $user->id)
                ->first();

            $isCreator = $ride->user_id === $user->id;
            $isJoiner = $currentUserBooking !== null;

            $ride->current_user_type = $isCreator ? 'creator' : ($isJoiner ? 'joiner' : null);
            $ride->is_creator = $isCreator;
            $ride->is_joiner = $isJoiner;
            $ride->current_user_booking = $currentUserBooking;

            return $ride;
        });

        return $this->respondSuccess($rides, 'sharing_rides_listed');
    }

    /**
     * Location suggestions for inline search dropdown (pickup / drop).
     * GET /api/v1/sharing-rides/locations?search=andheri&type=pick|drop|all
     */
    public function locations(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type = $request->input('type', 'all');

        if (!in_array($type, ['pick', 'drop', 'all'], true)) {
            $this->throwCustomException('invalid location type');
        }

        if ($search === '') {
            return $this->respondSuccess([], 'sharing_ride_locations_listed');
        }

        $limit = min((int) $request->input('limit', 10), 20);
        $locations = collect();

        $baseQuery = SharingRide::query()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('active', true);

        if ($type === 'pick' || $type === 'all') {
            $pickLocations = (clone $baseQuery)
                ->where('pick_address', 'LIKE', "%{$search}%")
                ->whereNotNull('pick_address')
                ->where('pick_address', '!=', '')
                ->select('pick_address as address', 'pick_lat as lat', 'pick_lng as lng')
                ->selectRaw("'pick' as type")
                ->distinct()
                ->limit($limit)
                ->get();

            $locations = $locations->merge($pickLocations);
        }

        if ($type === 'drop' || $type === 'all') {
            $dropLocations = (clone $baseQuery)
                ->where('drop_address', 'LIKE', "%{$search}%")
                ->whereNotNull('drop_address')
                ->where('drop_address', '!=', '')
                ->select('drop_address as address', 'drop_lat as lat', 'drop_lng as lng')
                ->selectRaw("'drop' as type")
                ->distinct()
                ->limit($limit)
                ->get();

            $locations = $locations->merge($dropLocations);
        }

        $locations = $locations
            ->unique(function ($item) {
                return strtolower(trim($item->address)) . '|' . $item->type;
            })
            ->values()
            ->take($limit);

        return $this->respondSuccess($locations, 'sharing_ride_locations_listed');
    }

    public function myRides()
    {
        SharingRide::syncStatuses();

        $user = auth()->user();

        $rides = SharingRide::with(['vehicleType', 'bookings.user'])
            ->where('user_id', $user->id)
            ->orWhereHas('bookings', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->paginate();

        return $this->respondSuccess($rides, 'my_sharing_rides_listed');
    }

    public function show(SharingRide $sharingRide)
    {
        SharingRide::syncStatuses();
        $sharingRide->refresh();

        $user = auth()->user();

        $sharingRide->load(['vehicleType', 'user', 'bookings.user']);

        $currentUserBooking = $sharingRide->bookings
            ->where('user_id', $user->id)
            ->first();

        $isCreator = $sharingRide->user_id === $user->id;
        $isJoiner = $currentUserBooking !== null;

        if (!$isCreator && !$isJoiner) {
            $this->throwAuthorizationException();
        }

        $details = [
            'current_user_type' => $isCreator ? 'creator' : 'joiner',
            'is_creator' => $isCreator,
            'is_joiner' => $isJoiner,
            'current_user_booking' => $currentUserBooking,
            'ride' => $sharingRide,
        ];

        return $this->respondSuccess($details, 'sharing_ride_detail');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'pick_address' => 'required|string|max:255',
            'pick_lat' => 'required|numeric',
            'pick_lng' => 'required|numeric',
            'drop_address' => 'required|string|max:255',
            'drop_lat' => 'required|numeric',
            'drop_lng' => 'required|numeric',
            'price_per_km' => 'required|numeric|min:0',
            'trip_start_time' => 'required|date',
            'total_capacity' => 'nullable|integer|min:1',
        ]);

        $vehicleType = VehicleType::findOrFail($validated['vehicle_type_id']);
        $capacity = $validated['total_capacity'] ?? ($vehicleType->capacity ?: 1);

        $ride = SharingRide::create([
            'vehicle_type_id' => $vehicleType->id,
            'user_id' => auth()->id(),
            'pick_address' => $validated['pick_address'],
            'pick_lat' => $validated['pick_lat'],
            'pick_lng' => $validated['pick_lng'],
            'drop_address' => $validated['drop_address'],
            'drop_lat' => $validated['drop_lat'],
            'drop_lng' => $validated['drop_lng'],
            'price_per_km' => $validated['price_per_km'],
            'total_capacity' => $capacity,
            'trip_start_time' => $validated['trip_start_time'],
            'status' => Carbon::parse($validated['trip_start_time'])->lte(Carbon::now()) ? 'ongoing' : 'upcoming',
            'company_key' => auth()->user()->company_key,
        ]);

        return $this->respondSuccess($ride->load(['vehicleType', 'user']), 'sharing_ride_created');
    }

    public function join(Request $request, SharingRide $sharingRide)
    {
        SharingRide::syncStatuses();
        $sharingRide->refresh();

        $validated = $request->validate([
            'seats' => 'nullable|integer|min:1',
            'payment_transaction_id' => 'nullable|string|max:255',
            'payment_status' => 'nullable|string|max:255',
        ]);

        if (!in_array($sharingRide->status, ['upcoming', 'ongoing']) || !$sharingRide->active) {
            $this->throwCustomException('sharing ride is not available');
        }

        $seats = $validated['seats'] ?? 1;

        $booking = DB::transaction(function () use ($sharingRide, $validated, $seats) {
            $ride = SharingRide::where('id', $sharingRide->id)->lockForUpdate()->first();

            if ($ride->available_seats < $seats) {
                $this->throwCustomException('not enough seats available');
            }

            return SharingRideBooking::create([
                'sharing_ride_id' => $ride->id,
                'user_id' => auth()->id(),
                'seats' => $seats,
                'otp' => (string) random_int(1000, 9999),
                'payment_transaction_id' => $validated['payment_transaction_id'] ?? null,
                'payment_status' => $validated['payment_status'] ?? null,
                'status' => 'pending',
            ]);
        });

        $this->notifySharingRideCreator(
            $sharingRide,
            'sharing_ride_joined_title',
            'sharing_ride_joined_body',
            PushEnums::SHARING_RIDE_JOINED,
            [
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]
        );

        return $this->respondSuccess($booking, 'sharing_ride_joined_otp_sent');
    }

    public function verifyOtp(Request $request, SharingRideBooking $booking)
    {
        $validated = $request->validate([
            'otp' => 'required|string',
        ]);

        $this->authorizeBookingCreator($booking);
        $this->ensureBookingStatus($booking, ['arrived']);

        if ($booking->otp !== $validated['otp']) {
            $this->throwCustomException('provided otp is invalid');
        }

        $booking->update([
            'otp_verified_at' => Carbon::now(),
            'status' => 'otp_verified',
        ]);

        $booking->loadMissing('sharingRide');
        if ($booking->sharingRide) {
            $booking->sharingRide->markOngoingFromJoinerStart();
        }

        $booking = $booking->fresh(['sharingRide', 'user']);
        $this->notifySharingRideJoiner(
            $booking,
            'sharing_ride_otp_verified_title',
            'sharing_ride_otp_verified_body',
            PushEnums::SHARING_RIDE_OTP_VERIFIED
        );

        return $this->respondSuccess($booking, 'sharing_ride_otp_verified');
    }

    public function arriveBooking(SharingRideBooking $booking)
    {
        $this->authorizeBookingCreator($booking);
        $this->ensureBookingStatus($booking, ['pending']);

        $booking->update([
            'arrived_at' => Carbon::now(),
            'status' => 'arrived',
        ]);

        $booking = $booking->fresh(['sharingRide', 'user']);
        $this->notifySharingRideJoiner(
            $booking,
            'sharing_ride_arrived_title',
            'sharing_ride_arrived_body',
            PushEnums::SHARING_RIDE_ARRIVED
        );

        return $this->respondSuccess($booking, 'sharing_ride_booking_arrived');
    }

    public function startBooking(SharingRideBooking $booking)
    {
        $this->authorizeBookingCreator($booking);
        $this->ensureBookingStatus($booking, ['otp_verified']);

        $booking->update([
            'started_at' => Carbon::now(),
            'status' => 'started',
        ]);

        $booking->loadMissing('sharingRide');
        if ($booking->sharingRide) {
            $booking->sharingRide->markOngoingFromJoinerStart();
        }

        $booking = $booking->fresh(['sharingRide', 'user']);
        $this->notifySharingRideJoiner(
            $booking,
            'sharing_ride_started_title',
            'sharing_ride_started_body',
            PushEnums::SHARING_RIDE_STARTED
        );

        return $this->respondSuccess($booking, 'sharing_ride_booking_started');
    }

    public function dropBooking(SharingRideBooking $booking)
    {
        $this->authorizeBookingCreator($booking);
        $this->ensureBookingStatus($booking, ['started']);

        $booking->update([
            'dropped_at' => Carbon::now(),
            'status' => 'dropped',
        ]);

        $booking = $booking->fresh(['sharingRide', 'user']);
        $this->notifySharingRideJoiner(
            $booking,
            'sharing_ride_dropped_title',
            'sharing_ride_dropped_body',
            PushEnums::SHARING_RIDE_DROPPED
        );

        return $this->respondSuccess($booking, 'sharing_ride_booking_dropped');
    }

    public function updateBookingPayment(Request $request, SharingRideBooking $booking)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,wallet,online',
            'payment_transaction_id' => 'nullable|string|max:255',
            'payment_status' => 'nullable|string|max:255',
        ]);

        if ($booking->user_id != auth()->id()) {
            $this->throwAuthorizationException();
        }

        $this->ensureBookingStatus($booking, ['dropped', 'payment_pending', 'paid']);

        $paymentStatus = $validated['payment_status'] ?? ($validated['payment_method'] === 'cash' ? 'pending' : null);
        $status = $validated['payment_method'] === 'cash'
            ? 'payment_pending'
            : ($paymentStatus === 'success' ? 'paid' : 'payment_pending');

        $booking->update([
            'payment_method' => $validated['payment_method'],
            'payment_transaction_id' => $validated['payment_transaction_id'] ?? $booking->payment_transaction_id,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);

        $booking = $booking->fresh(['sharingRide', 'user']);
        if ($booking->sharingRide) {
            $this->notifySharingRideCreator(
                $booking->sharingRide,
                'sharing_ride_payment_updated_title',
                'sharing_ride_payment_updated_body',
                PushEnums::SHARING_RIDE_PAYMENT_UPDATED,
                [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                ]
            );
        }

        return $this->respondSuccess($booking, 'sharing_ride_payment_updated');
    }

    public function markBookingCashCollected(SharingRideBooking $booking)
    {
        $this->authorizeBookingCreator($booking);
        $this->ensureBookingStatus($booking, ['payment_pending']);

        if ($booking->payment_method !== 'cash') {
            $this->throwCustomException('cash payment is not selected for this booking');
        }

        $booking->update([
            'payment_status' => 'success',
            'cash_collected_at' => Carbon::now(),
            'status' => 'cash_collected',
        ]);

        $booking = $booking->fresh(['sharingRide', 'user']);
        $this->notifySharingRideJoiner(
            $booking,
            'sharing_ride_cash_collected_title',
            'sharing_ride_cash_collected_body',
            PushEnums::SHARING_RIDE_CASH_COLLECTED
        );

        return $this->respondSuccess($booking, 'sharing_ride_cash_collected');
    }

    public function completeBooking(SharingRideBooking $booking)
    {
        $this->authorizeBookingCreator($booking);

        if (!in_array($booking->status, ['paid', 'cash_collected'])) {
            $this->throwCustomException('payment is not completed for this booking');
        }

        $booking->update([
            'completed_at' => Carbon::now(),
            'status' => 'completed',
        ]);

        $booking->loadMissing('sharingRide');
        $rideCompleted = false;
        if ($booking->sharingRide) {
            $rideCompleted = $booking->sharingRide->markCompletedIfAllJoinersDone();
        }

        $booking = $booking->fresh(['sharingRide', 'user']);
        $this->notifySharingRideJoiner(
            $booking,
            'sharing_ride_booking_completed_title',
            'sharing_ride_booking_completed_body',
            PushEnums::SHARING_RIDE_BOOKING_COMPLETED
        );

        if ($rideCompleted && $booking->sharingRide) {
            $this->notifySharingRideJoiners(
                $booking->sharingRide,
                'sharing_ride_completed_title',
                'sharing_ride_completed_body',
                PushEnums::SHARING_RIDE_COMPLETED
            );
        }

        return $this->respondSuccess($booking, 'sharing_ride_booking_completed');
    }

    public function complete(SharingRide $sharingRide)
    {
        if ($sharingRide->user_id !== auth()->id()) {
            $this->throwAuthorizationException();
        }

        if (!$sharingRide->allActiveJoinersCompleted()) {
            $this->throwCustomException('complete all joiners before completing the ride');
        }

        $sharingRide->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'active' => false,
        ]);

        $this->notifySharingRideJoiners(
            $sharingRide,
            'sharing_ride_completed_title',
            'sharing_ride_completed_body',
            PushEnums::SHARING_RIDE_COMPLETED
        );

        return $this->respondSuccess($sharingRide->fresh(), 'sharing_ride_completed');
    }

    public function cancel(SharingRide $sharingRide)
    {
        if ($sharingRide->user_id === auth()->id()) {
            $sharingRide->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'active' => false,
            ]);

            $this->notifySharingRideJoiners(
                $sharingRide,
                'sharing_ride_cancelled_by_creator_title',
                'sharing_ride_cancelled_by_creator_body',
                PushEnums::SHARING_RIDE_CANCELLED_BY_CREATOR
            );

            return $this->respondSuccess($sharingRide->fresh(), 'sharing_ride_cancelled');
        }

        $booking = $sharingRide->bookings()
            ->where('user_id', auth()->id())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->first();

        if (!$booking) {
            $this->throwAuthorizationException();
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        $sharingRide->markCompletedIfAllJoinersDone();

        $this->notifySharingRideCreator(
            $sharingRide,
            'sharing_ride_cancelled_by_joiner_title',
            'sharing_ride_cancelled_by_joiner_body',
            PushEnums::SHARING_RIDE_CANCELLED_BY_JOINER,
            [
                'booking_id' => $booking->id,
                'status' => 'cancelled',
            ]
        );

        return $this->respondSuccess($booking->fresh(['sharingRide', 'user']), 'sharing_ride_booking_cancelled');
    }

    private function authorizeBookingCreator(SharingRideBooking $booking)
    {
        $booking->loadMissing('sharingRide');

        if (!$booking->sharingRide || $booking->sharingRide->user_id != auth()->id()) {
            $this->throwAuthorizationException();
        }
    }

    private function ensureBookingStatus(SharingRideBooking $booking, array $allowedStatuses)
    {
        if (!in_array($booking->status, $allowedStatuses)) {
            $this->throwCustomException('invalid booking status');
        }
    }

    private function notifySharingRideCreator(
        SharingRide $sharingRide,
        string $titleKey,
        string $bodyKey,
        string $notificationEnum,
        array $extra = []
    ) {
        $sharingRide->loadMissing('user');

        $this->sendSharingRidePush(
            $sharingRide->user,
            $titleKey,
            $bodyKey,
            array_merge([
                'notification_enum' => $notificationEnum,
                'sharing_ride_id' => $sharingRide->id,
                'status' => $sharingRide->status,
            ], $extra)
        );
    }

    private function notifySharingRideJoiner(
        SharingRideBooking $booking,
        string $titleKey,
        string $bodyKey,
        string $notificationEnum
    ) {
        $booking->loadMissing(['user', 'sharingRide']);

        $this->sendSharingRidePush(
            $booking->user,
            $titleKey,
            $bodyKey,
            [
                'notification_enum' => $notificationEnum,
                'sharing_ride_id' => $booking->sharing_ride_id,
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]
        );
    }

    private function notifySharingRideJoiners(
        SharingRide $sharingRide,
        string $titleKey,
        string $bodyKey,
        string $notificationEnum
    ) {
        $sharingRide->loadMissing('bookings.user');

        foreach ($sharingRide->bookings as $booking) {
            if ($booking->status === 'cancelled' || !$booking->user) {
                continue;
            }

            $this->sendSharingRidePush(
                $booking->user,
                $titleKey,
                $bodyKey,
                [
                    'notification_enum' => $notificationEnum,
                    'sharing_ride_id' => $sharingRide->id,
                    'booking_id' => $booking->id,
                    'status' => $sharingRide->status,
                ]
            );
        }
    }

    private function sendSharingRidePush($user, string $titleKey, string $bodyKey, array $data = [])
    {
        if (!$user instanceof User) {
            return;
        }

        // Don't notify the same user who triggered the action.
        if ((int) $user->id === (int) auth()->id()) {
            return;
        }

        $lang = $user->lang ?: 'en';
        $title = trans('push_notifications.'.$titleKey, [], $lang);
        $body = trans('push_notifications.'.$bodyKey, [], $lang);

        dispatch(new SendPushNotification($user, $title, $body, $data));
    }
}
