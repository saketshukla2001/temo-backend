<?php

namespace App\Console\Commands;

use App\Models\Admin\SharingRide;
use Illuminate\Console\Command;

class CancelExpiredSharingRides extends Command
{
    protected $signature = 'cancel:expired-sharing-rides';

    protected $description = 'Auto cancel sharing rides with no status update within 24 hours of trip start time';

    public function handle()
    {
        SharingRide::moveDueUpcomingToOngoing();
        $cancelledCount = SharingRide::cancelExpiredWithoutStatusUpdate();

        $this->info("Auto cancelled {$cancelledCount} sharing ride(s).");

        return 0;
    }
}
