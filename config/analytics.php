<?php

return [
    'executive_report' => [
        'recipients' => array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', (string) env('CEO_REPORT_RECIPIENTS', '')),
        ))),
        'send_time' => env('CEO_REPORT_SEND_TIME', '07:30'),
        'timezone' => env('CEO_REPORT_TIMEZONE', 'Europe/Budapest'),
        'default_days' => (int) env('CEO_REPORT_DAYS', 30),
    ],
];
