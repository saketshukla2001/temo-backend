<?php

namespace App\Models\Payment;

use App\Base\Uuid\UuidModel;
use App\Models\Admin\Driver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DriverSubscription extends Model
{
    use UuidModel;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'driver_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'driver_id',
        'plan_id',
        'subscription_type',
        'subscription_basis',
        'active',
        'paid_amount',
        'duration_days',
        'total_rides',
        'used_rides',
        'remaining_rides',
        'started_at',
        'expired_at',
        'transaction_id',
        'payment_status',
    ];

    /**
     * The relationships that can be loaded with query string filtering includes.
     *
     * @var array
     */
    public $includes = [

    ];

    /**
    * The accessors to append to the model's array form.
    *
    * @var array
    */
    protected $appends = [
        'converted_expired_at'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function scopeUsable($query)
    {
        return $query->where('active', true)
            ->where(function ($subscriptionQuery) {
                $subscriptionQuery->where(function ($dayQuery) {
                    $dayQuery->where('subscription_basis', 'day_wise')
                        ->where('expired_at', '>=', Carbon::now());
                })->orWhere(function ($rideQuery) {
                    $rideQuery->where('subscription_basis', 'ride_wise')
                        ->where('remaining_rides', '>', 0);
                });
            });
    }

    public function getConvertedExpiredAtAttribute()
    {
        return $this->expired_at ? Carbon::parse($this->expired_at)->format('d M Y h:i A') : '-';
    }
}
