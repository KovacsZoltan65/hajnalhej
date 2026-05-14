<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Export\ExportStatus;
use App\Models\ExportJob;
use App\Services\Export\ExportStorageService;
use Illuminate\Console\Command;

class PruneExpiredExportsCommand extends Command
{
    protected $signature = 'exports:prune';

    protected $description = 'Delete expired export files and mark completed export jobs as expired.';

    public function handle(ExportStorageService $storage): int
    {
        $count = 0;

        ExportJob::query()
            ->where('status', ExportStatus::Completed)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->chunkById(100, function ($jobs) use ($storage, &$count): void {
                foreach ($jobs as $job) {
                    if ($job->disk && $job->path) {
                        $storage->delete($job->disk, $job->path);
                    }

                    $job->update(['status' => ExportStatus::Expired]);
                    $count++;
                }
            });

        $this->info("Expired exports pruned: {$count}");

        return self::SUCCESS;
    }
}
