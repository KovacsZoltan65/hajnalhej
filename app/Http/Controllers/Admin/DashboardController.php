<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\InertiaPage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render(InertiaPage::ADMIN_DASHBOARD->value, [
            'stats' => [
                'ordersToday' => 24,
                'weekRevenue' => '412 500 Ft',
                'topProduct' => 'Klasszikus kovaszos kenyer',
                'nextPickupSlot' => 'Hetfo 07:30 - 09:00',
            ],
        ]);
    }
}
