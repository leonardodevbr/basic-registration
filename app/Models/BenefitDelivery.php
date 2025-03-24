<?php

namespace App\Models;

use App\Models\Base\BenefitDelivery as BaseBenefitDelivery;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitDelivery extends BaseBenefitDelivery
{
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_id');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by_id');
    }
}
