<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ExpireBenefitDeliveriesJob;

class ExpireBenefitDeliveriesCommand extends Command
{
    protected $signature = 'benefits:expire';
    protected $description = 'Atualiza o status de benefícios expirados';

    public function handle()
    {
        ExpireBenefitDeliveriesJob::dispatch();
        $this->info('Benefícios expirados atualizados.');
    }
}
