<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\Payments\PaypalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocalizationController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/localization/{lang}', [LocalizationController::class, 'changeLocale'])->name('localization');

Route::group([
    'prefix' => 'message',
    'middleware' => ['version']
], function () {
    Route::get('/get', [MessageController::class, 'getMessage']);
    Route::post('/create', [MessageController::class, 'createMessage']);
    Route::match(['PUT', 'PATCH'], '/update', [MessageController::class, 'updateMessage']);
    Route::delete('/delete', [MessageController::class, 'deleteMessage']);
});

Route::group([
    'prefix' => 'payments',
    'as' => 'payments.'
], function () {
    Route::get('/paypal/checkout', [PaypalController::class, 'index'])->name('paypal.checkout');
    Route::get('/paypal/status/{refId?}', [PaypalController::class, 'status'])->name('paypal.status');
    Route::post('/paypal/checkout_validate', [PaypalController::class, 'checkoutValidate']);
});
