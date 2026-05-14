<?php

declare(strict_types=1);

namespace App\Enums\Export;

enum ExportStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';
}
