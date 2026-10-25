<?php

namespace App\Models\Payment;

use App\Base\Uuid\UuidModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaymentCashbackCode extends Model
{
    use UuidModel;

    protected $table = 'payment_cashback_codes';

    protected $fillable = [
        'user_id',
        'ride_id',
        'ride_type',
        'code',
        'amount',
        'status',
        'expires_at',
        'verified_at',
        'verified_by',
    ];

    protected $dates = [
        'expires_at',
        'verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isExpired()
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->expires_at && Carbon::now()->greaterThan($this->expires_at);
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
