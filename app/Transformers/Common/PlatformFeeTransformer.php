<?php

namespace App\Transformers\Common;

use App\Models\Admin\PlatformFee;
use App\Transformers\Transformer;

class PlatformFeeTransformer extends Transformer
{
    public function transform(PlatformFee $platformFee)
    {
        return [
            'id' => $platformFee->id,
            'user_type' => $platformFee->user_type,
            'fee_type' => $platformFee->fee_type,
            'amount' => (float) $platformFee->amount,
            'active' => (bool) $platformFee->active,
        ];
    }
}
