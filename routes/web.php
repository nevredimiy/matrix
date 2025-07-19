<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [TestController::class, 'index'])->name('test');

// Route::get('/admin/factory-missing-page', function () {
//     return view('/filament/pages/factory-missing-page');
// })->name('filament.pages.factory-missing-page');