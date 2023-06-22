<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Admin routes
Route::middleware('web')->group(function () {
    // Your admin routes here

    //Admin Routes
    Auth::routes();
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/dashboard/admin/category', App\Http\Controllers\CategoriesController::class, ['names' => [
        'index'=>'admin.category',
        'create' => 'admin.category.create',
        'edit' => 'admin.category.edit'
    ]]);
    Route::resource('/dashboard/admin/brand', App\Http\Controllers\BrandsController::class, ['names' => [
        'index'=>'admin.brand',
        'create' => 'admin.brand.create',
        'edit' => 'admin.brand.edit'
    ]]);
    Route::resource('/dashboard/admin/bulk', App\Http\Controllers\BulkController::class, ['names' => [
        'index'=>'admin.bulk',
        'create' => 'admin.bulk.create',
        'edit' => 'admin.bulk.edit'
    ]]);
    Route::resource('/dashboard/admin/product', App\Http\Controllers\ProductsController::class, ['names' => [
        'index'=>'admin.product',
        'create' => 'admin.product.create',
        'edit' => 'admin.product.edit'
    ]]);
    Route::resource('/dashboard/admin/foodvendor', App\Http\Controllers\FoodVendorsController::class, ['names' => [
        'index'=>'admin.foodVendor',
        'create' => 'admin.foodVendor.create',
        'edit' => 'admin.foodVendor.edit'
    ]]);
    Route::resource('/dashboard/admin/food', App\Http\Controllers\FoodsController::class, ['names' => [
        'index'=>'admin.food',
        'create' => 'admin.food.create',
        'edit' => 'admin.food.edit'
    ]]);
    Route::resource('/dashboard/admin/drink', App\Http\Controllers\DrinksController::class, ['names' => [
        'index'=>'admin.drink',
        'create' => 'admin.drink.create',
        'edit' => 'admin.drink.edit'
    ]]);
    Route::resource('/dashboard/admin/extra', App\Http\Controllers\ExtrasController::class, ['names' => [
        'index'=>'admin.extra',
        'create' => 'admin.extra.create',
        'edit' => 'admin.extra.edit'
    ]]);
    Route::get('/dashboard/admin/{id}/{reference}/order', [App\Http\Controllers\OrderController::class, 'getOrder'])->name('admin.order');
    Route::get('/dashboard/admin/{id?}/{reference?}/bulkorder', [App\Http\Controllers\OrderController::class, 'getBulkOrder'])->name('admin.bulkorders');
    Route::get('/dashboard/admin/orderDetails', [App\Http\Controllers\OrderController::class, 'getOrderDetails'])->name('admin.orderDetails');
    Route::get('/dashboard/admin/bulkBuyOrderDetails', [App\Http\Controllers\OrderController::class, 'getBulkOrderDetails'])->name('admin.bulkBuyOrderDetails');
    Route::get('/dashboard/admin/foodorderDetails', [App\Http\Controllers\OrderController::class, 'getFoodOrderDetails'])->name('admin.foodOrderDetails');
    Route::get('/dashboard/admin/receipt', [App\Http\Controllers\ReceiptController::class, 'index'])->name('admin.receipt');
    Route::get('/dashboard/admin/bulk-receipt', [App\Http\Controllers\ReceiptController::class, 'bulkIndex'])->name('admin.bulk-receipt');
    Route::get('/dashboard/admin/confirm-receipt/{id}', [App\Http\Controllers\ReceiptController::class, 'confirmReceipt'])->name('admin.confirm-receipt');
    Route::get('/dashboard/admin/delete-receipt/{id}', [App\Http\Controllers\ReceiptController::class, 'deleteReceipt'])->name('admin.delete-receipt');
    Route::get('dynamicModal/{id}', [App\Http\Controllers\ReceiptController::class, 'loadModal'])->name('dynamicModal');
    //Admin Routes end
});
