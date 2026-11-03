<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Single Page Application (SPA) Host
|--------------------------------------------------------------------------
| All web routes render the single Blade layout mounting React in #app.
| React Router handles client-side routing across /admin/* and /jobs/*.
*/

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '^(?!api|storage).*$');
