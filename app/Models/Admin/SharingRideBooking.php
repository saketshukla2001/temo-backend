<?php

namespace App\Models\Admin;

use App\Base\Uuid\UuidModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SharingRideBooking extends Model
{
    use UuidModel, SoftDeletes;

    protected $fillable = [
        'sharing_ride_id',
        'user_id',
        'seats',
        'otp',
        'otp_verified_at',
        'arrived_at',
        'started_at',
        'dropped_at',
        'cash_collected_at',
        'completed_at',
        'payment_method',
        'payment_transaction_id',
        'payment_status',
        'status',
    ];

    public function sharingRide()
    {
        return $this->belongsTo(SharingRide::class, 'sharing_ride_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
