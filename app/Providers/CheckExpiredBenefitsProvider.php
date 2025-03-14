<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BenefitDelivery;
use Carbon\Carbon;

class CheckExpiredBenefitsProvider extends ServiceProvider
{
    public function boot()
    {
        BenefitDelivery::where('status', 'PENDING')
            ->where('valid_until', '<', Carbon::now())
            ->update(['status' => 'EXPIRED']);
    }
}
