<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return 'Migrations run successfully! Output: <pre>' . Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running migrations: ' . $e->getMessage();
    }
});

Route::get('/run-seed', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return 'Database seeding run successfully! Output: <pre>' . Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running seed: ' . $e->getMessage();
    }
});
