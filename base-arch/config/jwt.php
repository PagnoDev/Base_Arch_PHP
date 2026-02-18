<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'ttl_minutes' => (int) env('JWT_TTL_MINUTES', 120),
];
