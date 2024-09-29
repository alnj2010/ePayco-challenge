<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SoapController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*Route::post('/register', [ClientController::class, 'register']);
Route::post('/charge', action: [ClientController::class, 'charge']);
Route::post('/check', [ClientController::class, 'check_balance']);
Route::post('/purchase', [ClientController::class, 'purchase']);
Route::get('/confirm-purchase', [ClientController::class, 'confirm']);*/

Route::get('/soap/wsdl', [SoapController::class, 'wsdlAction'])->name('soap-wsdl');
Route::post('/soap/server', [SoapController::class, 'serverAction'])->name('soap-server');
