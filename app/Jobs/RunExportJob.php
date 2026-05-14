<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ExportJob;
use App\Services\Export\ExportManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunExportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ExportJob $exportJob,
    ) {}

    public function handle(ExportManager $manager): void
    {
        $manager->run($this->exportJob);
    }
}
