<?php

use App\Http\Controllers\api\FoodsController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\FoodVendorsController;
use App\Http\Controllers\api\ProductsController;
use App\Http\Controllers\api\CategoriesController;
use App\Http\Controllers\api\BrandsController;
use App\Http\Controllers\api\CheckoutController;
use App\Http\Controllers\api\CustomerDashboardController;
use App\Http\Controllers\api\VerifyPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('api')->group(function () {
    // Customer authentication routes


    //Authentication Route
    Route::group(['middleware' => ['api']], function () {
        Route::post('/v2/register-customer', [AuthController::class, 'register']);
        Route::post('/v2/customer-registration', [AuthController::class, 'newRegister']);
        Route::post('/v2/login-customer', [AuthController::class, 'login']);
        Route::get('/v2/profile-customer', [AuthController::class, 'profile']);
        Route::post('/v2/logout-customer', [AuthController::class, 'logout']);
    });
    //Authentication Route Ends

    //Customer Dashboard Routes
    Route::group(['middleware' => ['api']], function () {
        Route::get('/v2/customer-order', [CustomerDashboardController::class, 'orders']);
        Route::get('/v2/customer-single-order/{reference?}', [CustomerDashboardController::class, 'singleOrders']);
        Route::get('/v2/customer-refers', [CustomerDashboardController::class, 'refers']);
        Route::get('/v2/customer-details', [CustomerDashboardController::class, 'details']);
        Route::post('/v2/customer-change-password', [CustomerDashboardController::class, 'changePassword']);
        Route::post('/v2/customer-change-location', [CustomerDashboardController::class, 'changeLocation']);
        Route::delete('/v2/customer-delete-account', [CustomerDashboardController::class, 'deleteAccount']);
        Route::get('/v2/customer-location', [CustomerDashboardController::class, 'customerLocation']);
    });
    //Customer Dashboard Routes Ends

    //Food Route
    Route::get('/v2/foods', [FoodsController::class, 'index']);
    Route::get('/v2/foods/{id}', [FoodsController::class, 'singleFood']);
    Route::post('/v2/foods/search', [FoodsController::class, 'searchFood']);
    Route::get('/v2/food-drinks/{id}', [FoodsController::class, 'foodDrinks']);
    Route::get('/v2/food-toppings/{id}', [FoodsController::class, 'foodExtras']);
    Route::get('/v2/drinks', [FoodsController::class, 'drinks']);
    Route::get('/v2/drink/{id}', [FoodsController::class, 'singleDrink']);
    Route::get('/v2/extras', [FoodsController::class, 'extras']);
    Route::get('/v2/extra/{id}', [FoodsController::class, 'singleExtra']);
    Route::post('/v2/food/payment-gateway', [FoodsController::class, 'getLink']);
    Route::get('/v2/food/paystack/callback', [FoodsController::class, 'paystackFoodCallback']);
    Route::post('/v2/bank/food/uploadReceipt', [FoodsController::class, 'uploadFoodReceipt']);
    //Food Route Ends

    //Food Vendor Route
    Route::get('/v2/food-vendors', [FoodVendorsController::class, 'index']);
    Route::get('/v2/food-vendor/{id}', [FoodVendorsController::class, 'singleFoodVendor']);
    Route::get('/v2/food-vendor/vendor/{id}', [FoodVendorsController::class, 'foodByVendor']);
    Route::post('/v2/food-vendors/search', [FoodVendorsController::class, 'searchFoodVendor']);
    //Food Vendor Route Ends

    //Verify Payments
    Route::get('/v2/verify-orders', [VerifyPaymentController::class, 'verify']);
    //Verify Payments Ends

    //product Route
    Route::get('/v2/products', [ProductsController::class, 'index']);
    Route::get('/v2/product/{id}', [ProductsController::class, 'singleProduct']);
    Route::post('/v2/product/search', [ProductsController::class, 'searchProduct']);
    Route::get('/v2/product/brand/{id}', [ProductsController::class, 'productByBrand']);
    Route::post('/v2/product/payment-gateway', [ProductsController::class, 'getLink']);
    Route::get('/v2/product/paystack/callback', [ProductsController::class, 'paystackProductCallback']);
    Route::post('/v2/bank/product/uploadReceipt', [ProductsController::class, 'uploadProductReceipt']);
    Route::get('/v2/product', [ProductsController::class, 'priceRange']);
    Route::get('/v2/product-sorting', [ProductsController::class, 'sortPrice']);
    Route::get('/v2/product-brand-filtering', [ProductsController::class, 'brandFilter']);
    Route::get('/v2/recommend-products', [ProductsController::class, 'recommendProducts']);
    Route::get('/v2/latest-products', [ProductsController::class, 'latestProducts']);
    //product Route Ends

    // Categories Route
    Route::get('/v2/categories', [CategoriesController::class, 'index']);
    Route::get('/v2/category/{id}', [CategoriesController::class, 'singleCategory']);
    Route::post('/v2/category/search', [CategoriesController::class, 'searchCategory']);
    Route::get('/v2/products/category/{id}', [CategoriesController::class, 'productByCategory']);
    // Categories Route Ends

    // Brands Route
    Route::get('/v2/brands', [BrandsController::class, 'index']);
    Route::get('/v2/brand/{id}', [BrandsController::class, 'singleBrand']);
    Route::post('/v2/brand/search', [BrandsController::class, 'searchBrand']);
    // Brands Route Ends

    //Checkout Route
    Route::post('/v2/checkout/payment-gateway', [CheckoutController::class, 'getPaystackLink']);
    Route::post('/v2/checkout/uploadReceipt', [CheckoutController::class, 'uploadProductReceipt']);
    Route::get('/v2/checkout/paystack-callback', [CheckoutController::class, 'paystackCallback']);
    //Checkout Route Ends
});
