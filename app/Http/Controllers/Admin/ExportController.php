<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportFormat;
use App\Enums\Export\ExportStatus;
use App\Enums\Export\ExportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExportRequest;
use App\Jobs\RunExportJob;
use App\Models\ExportJob;
use App\Repositories\ExportJobRepository;
use App\Services\Export\ExportManager;
use App\Support\InertiaPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportJobRepository $repository,
        private readonly ExportManager $manager,
    ) {}

    public function index(Request $request): JsonResponse|Response
    {
        $this->authorize('viewAny', ExportJob::class);

        $exports = $this->repository->paginateForUser($request->user(), (int) $request->integer('per_page', 15));

        if ($request->expectsJson()) {
            return response()->json(['exports' => $exports]);
        }

        return InertiaPage::ADMIN_EXPORTS_INDEX->render([
            'exports' => $exports,
            'types' => array_map(fn (ExportType $type): array => ['value' => $type->value, 'label' => $type->label()], ExportType::cases()),
            'formats' => array_map(fn (ExportFormat $format): string => $format->value, ExportFormat::cases()),
        ]);
    }

    public function store(StoreExportRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $type = ExportType::from($validated['type']);
        $format = ExportFormat::from($validated['format']);

        $this->authorize('create', [ExportJob::class, $type]);

        $exportJob = $this->manager->createJob(new ExportRequestData(
            type: $type,
            format: $format,
            filters: $this->cleanFilters($validated['filters'] ?? []),
        ), $request->user());

        RunExportJob::dispatch($exportJob);

        if ($request->expectsJson()) {
            return response()->json(['export_job' => $exportJob], 202);
        }

        return back(303)->with('success', __('common.export_started'));
    }

    public function download(ExportJob $exportJob): StreamedResponse
    {
        $this->authorize('download', $exportJob);

        abort_unless($exportJob->isDownloadable(), 404);
        abort_unless($exportJob->status === ExportStatus::Completed, 404);
        abort_unless(Storage::disk($exportJob->disk ?? 'local')->exists((string) $exportJob->path), 404);

        $this->manager->logDownloaded($exportJob);

        return Storage::disk($exportJob->disk ?? 'local')->download((string) $exportJob->path, $exportJob->filename);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function cleanFilters(array $filters): array
    {
        return collect($filters)
            ->reject(static fn (mixed $value): bool => $value === null || $value === '')
            ->all();
    }
}
