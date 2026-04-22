<?php

namespace App\Http\Controllers;

use App\Services\WeeklyMenuService;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(private readonly WeeklyMenuService $weeklyMenuService)
    {
    }

    /**
     * Summary of home
     * @return \Inertia\Response
     */
    public function home(): Response
    {
        return Inertia::render('Home');
    }

    public function weeklyMenu(): Response
    {
        return Inertia::render('WeeklyMenu', $this->weeklyMenuService->getPublicWeeklyMenuPayload());
    }

    public function about(): Response
    {
        return Inertia::render('About');
    }
}
