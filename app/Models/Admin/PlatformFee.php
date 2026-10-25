<?php

namespace App\Models\Admin;

use App\Base\Uuid\UuidModel;
use App\Models\Traits\HasActive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformFee extends Model
{
    use UuidModel, HasActive, SoftDeletes;

    protected $fillable = [
        'user_type',
        'fee_type',
        'amount',
        'active',
    ];
}
