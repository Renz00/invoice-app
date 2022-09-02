<?php

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;

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

Route::controller(InvoiceController::class)->group(function () {
    Route::get('/get_all_invoice', 'get_all_invoice');
    Route::get('/search_invoice', 'search_invoice');
    Route::get('/create_invoice', 'create_invoice');
    Route::post('/add_invoice', 'add_invoice');
    Route::get('/show_invoice/{id}', 'show_invoice');
    Route::get('/edit_invoice/{id}', 'show_invoice');
    Route::get('/delete_invoice_items/{id}', 'delete_invoice_items');
    Route::post('/update_invoice/{id}', 'update_invoice');
});


Route::get('/customers', [CustomerController::class, 'all_customer']);

Route::get('/products', [ProductController::class, 'all_products']);

