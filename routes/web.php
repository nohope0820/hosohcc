<?php

use App\Http\Controllers\BundleController;
use App\Http\Controllers\FormController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/mau18');
Route::get('/bundle', [BundleController::class, 'show']);
Route::post('/bundle', [BundleController::class, 'generate'])->name('bundle.generate');
Route::get('/{form}', [FormController::class, 'show'])->whereIn('form', ['mau18', 'lptb', 'tncn', 'sddpnn']);
Route::post('/{form}', [FormController::class, 'generate'])->whereIn('form', ['mau18', 'lptb', 'tncn', 'sddpnn'])->name('form.generate');
