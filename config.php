<?php

declare(strict_types=1);

return [
    'app_name' => 'Mock Backend',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'data_file' => __DIR__ . '/data/items.json',
];
