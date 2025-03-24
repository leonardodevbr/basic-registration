<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateModelsWithAccessors extends Command
{
    protected $signature = 'generate:models-with-accessors';
    protected $description = 'Gera os models com Reliese e injeta os accessors nos modelos específicos.';

    public function handle(): int
    {
        $this->info('🔁 Gerando models com Reliese...');
        $this->call('code:models');

        $this->info('🧬 Injetando accessors no model Person...');
        $this->call('inject:person-accessors');

        $this->info('✅ Tudo pronto!');
        return CommandAlias::SUCCESS;
    }
}
