<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tracking\StoreConversionEventRequest;
use App\Services\ConversionTrackingService;
use Illuminate\Http\JsonResponse;

class ConversionTrackingController extends Controller
{
    public function __construct(private readonly ConversionTrackingService $service)
    {
    }

    public function store(StoreConversionEventRequest $request): JsonResponse
    {
        $this->service->trackFromRequest($request->validated(), $request);

        return response()->json([
            'status' => 'ok',
        ]);
    }
}

