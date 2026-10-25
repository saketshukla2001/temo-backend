<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Payment\PaymentCashbackCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group Cashback
 *
 * APIs to check and mark payment cashback codes
 */
class CashbackController extends BaseController
{
    /**
     * Check payment/cashback code.
     * Returns true when the code exists and is not used or expired.
     *
     * @queryParam code string required Payment code. Example: ABC000399
     * @bodyParam code string required Payment code. Example: ABC000399
     */
    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $cashbackCode = $this->findCode($request->input('code'));

        if (!$cashbackCode) {
            return $this->respondSuccess([
                'valid' => false,
                'code' => strtoupper(trim($request->input('code'))),
                'status' => null,
                'used' => false,
                'expired' => false,
                'message' => 'Cashback code not found',
            ], 'Cashback code not found');
        }

        $this->markExpiredIfNeeded($cashbackCode);

        $valid = $cashbackCode->isActive();

        return $this->respondSuccess([
            'valid' => $valid,
            'code' => $cashbackCode->code,
            'amount' => round((float) $cashbackCode->amount, 2),
            'status' => $cashbackCode->status,
            'used' => $cashbackCode->status === 'used',
            'expired' => $cashbackCode->status === 'expired' || $cashbackCode->isExpired(),
            'expires_at' => optional($cashbackCode->expires_at)->toDateTimeString(),
            'message' => $valid ? 'You are eligible to get cashback' : $this->invalidMessage($cashbackCode),
        ], $valid ? 'You are eligible to get cashback' : $this->invalidMessage($cashbackCode));
    }

    /**
     * Mark payment/cashback code as used.
     *
     * @bodyParam code string required Payment code. Example: ABC000399
     * @bodyParam used boolean required Pass true to mark the code as used. Example: true
     */
    public function markUsed(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'used' => 'required|boolean',
        ]);

        $cashbackCode = $this->findCode($request->input('code'));

        if (!$cashbackCode) {
            $this->throwCustomException('Cashback code not found');
        }

        $this->markExpiredIfNeeded($cashbackCode);

        if (!$request->boolean('used')) {
            return $this->respondSuccess([
                'valid' => $cashbackCode->isActive(),
                'code' => $cashbackCode->code,
                'amount' => round((float) $cashbackCode->amount, 2),
                'status' => $cashbackCode->status,
                'used' => $cashbackCode->status === 'used',
                'message' => 'Code not marked as used',
            ], 'Code not marked as used');
        }

        if ($cashbackCode->status === 'used') {
            $this->throwCustomException('Cashback code already used');
        }

        if ($cashbackCode->status === 'expired' || $cashbackCode->isExpired()) {
            $this->throwCustomException('Invalid or expired cashback code');
        }

        $cashbackCode->update([
            'status' => 'used',
            'verified_at' => Carbon::now(),
            'verified_by' => auth()->id(),
        ]);

        return $this->respondSuccess([
            'valid' => false,
            'code' => $cashbackCode->code,
            'amount' => round((float) $cashbackCode->amount, 2),
            'status' => 'used',
            'used' => true,
            'message' => 'Cashback code marked as used',
        ], 'Cashback code marked as used');
    }

    private function findCode($code)
    {
        return PaymentCashbackCode::where('code', strtoupper(trim($code)))->first();
    }

    private function markExpiredIfNeeded(PaymentCashbackCode $cashbackCode)
    {
        if ($cashbackCode->status === 'active' && $cashbackCode->isExpired()) {
            $cashbackCode->update(['status' => 'expired']);
            $cashbackCode->refresh();
        }
    }

    private function invalidMessage(PaymentCashbackCode $cashbackCode)
    {
        if ($cashbackCode->status === 'used') {
            return 'Cashback code already used';
        }

        return 'Invalid or expired cashback code';
    }
}
