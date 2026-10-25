<?php

namespace App\Models\Admin;

use App\Base\Uuid\UuidModel;
use App\Models\Traits\HasActive;
use App\Models\Traits\HasActiveCompanyKey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SharingRide extends Model
{
    use UuidModel, SoftDeletes, HasActive, HasActiveCompanyKey;

    protected $fillable = [
        'fleet_id',
        'vehicle_type_id',
        'user_id',
        'pick_address',
        'pick_lat',
        'pick_lng',
        'drop_address',
        'drop_lat',
        'drop_lng',
        'price_per_km',
        'total_capacity',
        'trip_start_time',
        'status',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
        'active',
        'company_key',
    ];

    protected $appends = [
        'booked_seats',
        'available_seats',
        'distance_in_km',
        'total_price',
    ];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleet_id', 'id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(SharingRideBooking::class, 'sharing_ride_id', 'id');
    }

    public function getBookedSeatsAttribute()
    {
        return (int) $this->bookings()
            ->whereIn('status', [
                'pending',
                'arrived',
                'otp_verified',
                'started',
                'dropped',
                'payment_pending',
                'paid',
                'cash_collected',
                'completed',
                'booked',
            ])
            ->sum('seats');
    }

    public function getAvailableSeatsAttribute()
    {
        return max(0, (int) $this->total_capacity - $this->booked_seats);
    }

    public function getDistanceInKmAttribute()
    {
        if (
            $this->pick_lat === null ||
            $this->pick_lng === null ||
            $this->drop_lat === null ||
            $this->drop_lng === null
        ) {
            return 0;
        }

        $earthRadius = 6371;
        $pickLat = deg2rad((float) $this->pick_lat);
        $pickLng = deg2rad((float) $this->pick_lng);
        $dropLat = deg2rad((float) $this->drop_lat);
        $dropLng = deg2rad((float) $this->drop_lng);

        $latDifference = $dropLat - $pickLat;
        $lngDifference = $dropLng - $pickLng;

        $angle = sin($latDifference / 2) ** 2
            + cos($pickLat) * cos($dropLat) * sin($lngDifference / 2) ** 2;

        $distance = 2 * $earthRadius * atan2(sqrt($angle), sqrt(1 - $angle));

        return round($distance, 2);
    }

    public function getTotalPriceAttribute()
    {
        return round($this->distance_in_km * (float) $this->price_per_km, 2);
    }

    public static function joinerStartedStatuses()
    {
        return [
            'otp_verified',
            'started',
            'dropped',
            'payment_pending',
            'paid',
            'cash_collected',
            'completed',
        ];
    }

    public static function moveDueUpcomingToOngoing()
    {
        static::where('status', 'upcoming')
            ->whereNotNull('trip_start_time')
            ->where('trip_start_time', '<=', Carbon::now())
            ->update([
                'status' => 'ongoing',
                'active' => true,
            ]);
    }

    /**
     * Move upcoming ride to ongoing when any joiner OTP is verified / trip started.
     */
    public function markOngoingFromJoinerStart()
    {
        if ($this->status !== 'upcoming') {
            return false;
        }

        $hasStartedJoiner = $this->bookings()
            ->whereIn('status', static::joinerStartedStatuses())
            ->exists();

        if (!$hasStartedJoiner) {
            return false;
        }

        $this->update([
            'status' => 'ongoing',
            'active' => true,
        ]);

        return true;
    }

    /**
     * Complete ride only when every non-cancelled joiner booking is completed.
     */
    public function markCompletedIfAllJoinersDone()
    {
        if (!in_array($this->status, ['upcoming', 'ongoing'], true)) {
            return false;
        }

        $activeJoinersCount = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($activeJoinersCount === 0) {
            return false;
        }

        $completedJoinersCount = $this->bookings()
            ->where('status', 'completed')
            ->count();

        if ($activeJoinersCount !== $completedJoinersCount) {
            return false;
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'active' => false,
        ]);

        return true;
    }

    public function allActiveJoinersCompleted()
    {
        $activeJoinersCount = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($activeJoinersCount === 0) {
            return true;
        }

        $completedJoinersCount = $this->bookings()
            ->where('status', 'completed')
            ->count();

        return $activeJoinersCount === $completedJoinersCount;
    }

    /**
     * Auto-cancel rides that stay upcoming/ongoing for 24 hours after trip_start_time
     * without moving to completed/cancelled.
     */
    public static function cancelExpiredWithoutStatusUpdate()
    {
        $cutoff = Carbon::now()->subHours(24);

        $expiredRideIds = static::whereIn('status', ['upcoming', 'ongoing'])
            ->whereNotNull('trip_start_time')
            ->where('trip_start_time', '<=', $cutoff)
            ->pluck('id');

        if ($expiredRideIds->isEmpty()) {
            return 0;
        }

        static::whereIn('id', $expiredRideIds)->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancel_reason' => 'Auto cancelled: no status update within 24 hours of trip start time',
            'active' => false,
        ]);

        SharingRideBooking::whereIn('sharing_ride_id', $expiredRideIds)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->update([
                'status' => 'cancelled',
            ]);

        return $expiredRideIds->count();
    }

    public static function syncStatuses()
    {
        static::moveDueUpcomingToOngoing();

        // Keep joiner-based ongoing in sync when APIs/cron run.
        $upcomingWithStartedJoiners = static::where('status', 'upcoming')
            ->whereHas('bookings', function ($query) {
                $query->whereIn('status', static::joinerStartedStatuses());
            })
            ->get();

        foreach ($upcomingWithStartedJoiners as $ride) {
            $ride->markOngoingFromJoinerStart();
        }

        static::cancelExpiredWithoutStatusUpdate();
    }
}
