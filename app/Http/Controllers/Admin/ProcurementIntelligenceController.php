<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneratePurchaseDraftRequest;
use App\Http\Requests\Admin\ProcurementIntelligenceIndexRequest;
use App\Services\ProcurementIntelligenceService;
use App\Services\PurchaseDraftGenerationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class ProcurementIntelligenceController extends Controller
{
    public function __construct(
        private readonly ProcurementIntelligenceService $service,
        private readonly PurchaseDraftGenerationService $draftGenerationService,
    ) {
    }

    /**
     * @param ProcurementIntelligenceIndexRequest $request
     * @return \Inertia\Response
     */
    public function index(ProcurementIntelligenceIndexRequest $request): Response
    {
        $this->authorize('viewProcurementIntelligence');

        $filters = $request->validated();
        $days = (int) ($filters['days'] ?? 30);

        return Inertia::render('Admin/ProcurementIntelligence/Index', [
            'filters' => [
                'days' => $days,
                'ingredient_id' => $filters['ingredient_id'] ?? null,
                'supplier_id' => $filters['supplier_id'] ?? null,
                'urgency' => $filters['urgency'] ?? '',
                'alert_type' => $filters['alert_type'] ?? '',
            ],
            'dashboard' => $this->service->buildDashboard($filters + ['days' => $days]),
            'filter_options' => $this->service->filterOptions(),
        ]);
    }

    public function generatePurchaseDrafts(GeneratePurchaseDraftRequest $request): RedirectResponse
    {
        try {
            $drafts = $this->draftGenerationService->generateFromRecommendations($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $message = count($drafts) === 1
            ? 'Beszerzési tervezet létrehozva.'
            : count($drafts).' beszerzési tervezet létrehozva beszállítónként csoportosítva.';

        return redirect()
            ->route('admin.purchases.index', ['status' => 'draft'])
            ->with('success', $message);
    }
}
