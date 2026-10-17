<?php

return [
    // Public URL of the Vue frontend. Used in QR codes and password reset links.
    'frontend_url' => rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/'),

    // Access token lifetime (minutes). Applied to Laravel Sanctum.
    'token_ttl' => (int) env('TOKEN_TTL_MINUTES', 480),

    'institution' => [
        'name' => env('INSTITUTION_NAME', 'Example Institute of Technology'),
        'signatory' => env('INSTITUTION_SIGNATORY', 'Jane Doe'),
        'signatory_role' => env('INSTITUTION_SIGNATORY_ROLE', 'Academic Director'),
    ],
];
