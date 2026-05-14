<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportStorageService
{
    public function disk(): string
    {
        return 'local';
    }

    public function relativePath(ExportType $type, ExportFormat $format, User $user, ?string $baseName = null): string
    {
        $name = Str::slug($baseName ?: $type->value, '_');

        return sprintf(
            'exports/%s_%s_%d.%s',
            $name,
            now()->format('Ymd_His'),
            $user->id,
            $format->value,
        );
    }

    public function absolutePath(string $path): string
    {
        Storage::disk($this->disk())->makeDirectory(dirname($path));

        return Storage::disk($this->disk())->path($path);
    }

    public function delete(string $disk, string $path): void
    {
        Storage::disk($disk)->delete($path);
    }
}
