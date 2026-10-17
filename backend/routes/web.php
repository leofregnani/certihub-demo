<?php

use Illuminate\Support\Facades\Route;

// The backend is API-only. The user interface lives in the Vue app (see FRONTEND_URL).
Route::get('/', fn () => response()->json([
    'name' => 'CertiHub API',
    'status' => 'ok',
    'frontend' => config('certihub.frontend_url'),
    'docs' => 'See docs/API.md in the repository.',
]));
