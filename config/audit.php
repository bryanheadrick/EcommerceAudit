<?php

return [
    'default_max_pages' => env('AUDIT_MAX_PAGES', 50),

    'crawler' => [
        'concurrency' => env('CRAWLER_CONCURRENCY', 5),
        'delay_between_requests' => env('CRAWLER_DELAY_MS', 100),
        'timeout' => env('CRAWLER_TIMEOUT', 30),
        'max_depth' => env('CRAWLER_MAX_DEPTH', 3),
        'user_agent' => env('CRAWLER_USER_AGENT', 'EcommerceAuditBot/1.0 (Conversion Audit Tool)'),
        'respect_robots_txt' => env('CRAWLER_RESPECT_ROBOTS', false),
    ],

    'lighthouse_path' => env('LIGHTHOUSE_PATH', 'lighthouse'),

    'chrome_path' => env('CHROME_PATH', ''),

    'puppeteer' => [
        'executable_path' => env('PUPPETEER_EXECUTABLE_PATH', ''),
        'timeout' => env('PUPPETEER_TIMEOUT', 60000),
        'viewport' => [
            'width' => env('PUPPETEER_VIEWPORT_WIDTH', 1920),
            'height' => env('PUPPETEER_VIEWPORT_HEIGHT', 1080),
        ],
    ],

    'scoring' => [
        'weights' => [
            'performance' => 0.30,
            'mobile' => 0.25,
            'seo' => 0.20,
            'checkout' => 0.15,
            'links' => 0.10,
        ],

        'severity_penalties' => [
            'critical' => 20,
            'high' => 10,
            'medium' => 5,
            'low' => 2,
            'info' => 0,
        ],
    ],

    'thresholds' => [
        'lcp' => [
            'good' => 2.5,
            'needs_improvement' => 4.0,
        ],
        'fid' => [
            'good' => 100,
            'needs_improvement' => 300,
        ],
        'cls' => [
            'good' => 0.1,
            'needs_improvement' => 0.25,
        ],
        'performance_score' => [
            'good' => 75,
            'poor' => 50,
        ],
    ],
];
