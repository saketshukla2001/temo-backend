<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Admin\PlatformFee;
use App\Transformers\Common\PlatformFeeTransformer;
use Illuminate\Http\Request;

/**
 * @group Platform Fees
 *
 * APIs for active platform fees
 */
class PlatformFeeController extends BaseController
{
    /**
     * List active platform fees.
     *
     * @queryParam user_type string Filter by user type. Example: user
     */
    public function index(Request $request)
    {
        $request->validate([
            'user_type' => 'nullable|in:user,driver',
        ]);

        $query = PlatformFee::active()
            ->when($request->user_type, function ($query) use ($request) {
                $query->where('user_type', $request->user_type);
            })
            ->latest();

        $platformFees = filter($query, new PlatformFeeTransformer)->get();

        return $this->respondSuccess($platformFees);
    }
}
