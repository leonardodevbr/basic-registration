<?php

namespace App\Jobs;

//use App\Models\BenefitDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ExpireBenefitDeliveriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
//        BenefitDelivery::where('status', 'PENDING')
//            ->where('valid_until', '<', Carbon::now())
//            ->update(['status' => 'EXPIRED']);
    }
}
