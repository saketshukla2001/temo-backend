<?php

namespace App\Models\Payment;

use App\Base\Uuid\UuidModel;
use App\Models\Traits\HasActive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverSubscriptionPlan extends Model
{
    use UuidModel, HasActive, SoftDeletes;

    protected $fillable = [
        'name',
        'subscription_basis',
        'price',
        'duration_days',
        'total_rides',
        'active',
    ];
}
