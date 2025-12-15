<?php

declare(strict_types=1);

return [
    'name' => 'MyClubHub',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL),
];
