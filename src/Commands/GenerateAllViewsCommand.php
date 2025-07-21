<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;

class GenerateAllViewsCommand extends Command
{
    protected $signature = 'wink:placeholder';
    protected $description = 'Placeholder command - will be implemented in next iteration';

    public function handle(): int
    {
        $this->info('Wink View Generator - GenerateAllViewsCommand');
        $this->comment('This command will be implemented in the next iteration.');
        
        return Command::SUCCESS;
    }
}
