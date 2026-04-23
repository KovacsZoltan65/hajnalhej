<?php

namespace App\Console\Commands;

use App\Mail\CeoExecutiveReportMail;
use App\Models\User;
use App\Services\CeoDashboardService;
use App\Support\PermissionRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCeoExecutiveReportCommand extends Command
{
    protected $signature = 'report:ceo-executive {--days= : Jelentes idoszaka napokban}';

    protected $description = 'Napi CEO executive riport kuldese emailben';

    public function __construct(
        private readonly CeoDashboardService $ceoDashboardService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('analytics.executive_report.default_days', 30));
        if ($days <= 0) {
            $days = 30;
        }

        /** @var array<int, string> $configuredRecipients */
        $configuredRecipients = config('analytics.executive_report.recipients', []);
        $recipients = collect($configuredRecipients)
            ->filter(static fn (mixed $email): bool => is_string($email) && $email !== '')
            ->values();

        if ($recipients->isEmpty()) {
            $recipients = User::query()
                ->role(PermissionRegistry::ROLE_ADMIN)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->unique()
                ->values();
        }

        if ($recipients->isEmpty()) {
            $this->warn('Nincs cimzett a CEO executive reporthoz.');

            return self::SUCCESS;
        }

        $dashboard = $this->ceoDashboardService->buildDashboard($days);
        $mail = new CeoExecutiveReportMail($dashboard);

        foreach ($recipients as $recipient) {
            Mail::to((string) $recipient)->send($mail);
        }

        $this->info(sprintf(
            'CEO executive report kikuldve %d cimzettre (%d napos idoszak).',
            $recipients->count(),
            $days,
        ));

        return self::SUCCESS;
    }
}
