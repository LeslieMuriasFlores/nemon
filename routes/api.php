<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EnergyPriceController;
use App\Http\Controllers\PowerPriceController;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/example', function () {
    return response()->json(['message' => 'Hello, this is the API!']);
});

Route::post('/prices/energy', [EnergyPriceController::class, 'store']);
Route::post('/prices/power', [PowerPriceController::class, 'store']);
Route::post('/generatebill', [InvoiceController::class, 'generateBill']);
Route::get('/invoices', [InvoiceController::class, 'index']);
Route::get('/prices/energy', [EnergyPriceController::class, 'index']);
Route::get('/prices/power', [PowerPriceController::class, 'index']);
