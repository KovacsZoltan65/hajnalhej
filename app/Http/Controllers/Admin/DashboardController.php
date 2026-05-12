<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\InertiaPage;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return InertiaPage::ADMIN_DASHBOARD->render([
            'stats' => [
                'ordersToday' => 24,
                'weekRevenue' => '412 500 Ft',
                'topProduct' => 'Klasszikus kovaszos kenyer',
                'nextPickupSlot' => 'Hetfo 07:30 - 09:00',
            ],
        ]);
    }
}
