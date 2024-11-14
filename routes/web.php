<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackgroundJobController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [BackgroundJobController::class, 'index'])->name('background_jobs.index');
Route::post('/background-jobs/{id}/cancel', [BackgroundJobController::class, 'cancel'])->name('background_jobs.cancel');
Route::post('/background-jobs/{id}/retry', [BackgroundJobController::class, 'retry'])->name('background_jobs.retry');

