<?php

declare(strict_types=1);

namespace Wink\ViewGenerator\Commands;

use Illuminate\Console\Command;

class GenerateCrudViewsCommand extends Command
{
    protected $signature = 'wink:views:crud {table}
                            {--framework=bootstrap : UI framework to use}
                            {--components : Generate reusable components}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate complete CRUD views for a table';

    public function handle(): int
    {
        $this->info('Wink View Generator - CRUD Views Command');
        $this->comment('This command will be implemented in the next iteration.');
        
        return self::SUCCESS;
    }
}