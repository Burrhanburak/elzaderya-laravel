<?php

use App\Http\Controllers\Api\AwardController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\FreePurchaseController;
use App\Http\Controllers\Api\PaddleWebhookController;
use App\Http\Controllers\Api\PoemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Blog API Routes
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/featured', [BlogController::class, 'featured']);
    Route::get('/{slug}', [BlogController::class, 'show']);
});

// Poem API Routes
Route::prefix('poems')->group(function () {
    Route::get('/', [PoemController::class, 'index']);
    Route::get('/featured', [PoemController::class, 'featured']);
    Route::get('/{slug}', [PoemController::class, 'show']);
});

// Certificate API Routes
Route::prefix('certificates')->group(function () {
    Route::get('/', [CertificateController::class, 'index']);
    Route::get('/featured', [CertificateController::class, 'featured']);
    Route::get('/{slug}', [CertificateController::class, 'show']);
});

// Award API Routes
Route::prefix('awards')->group(function () {
    Route::get('/', [AwardController::class, 'index']);
    Route::get('/featured', [AwardController::class, 'featured']);
    Route::get('/{slug}', [AwardController::class, 'show']);
});
// Book API Routes
Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::get('/featured', [BookController::class, 'featured']);
    Route::get('/{slug}', [BookController::class, 'show']);
});

// Category API Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/featured', [CategoryController::class, 'featured']);
    Route::get('/{slug}', [CategoryController::class, 'show']);
});

// Checkout API Routes
Route::prefix('v1')->group(function () {
    Route::post('/checkout/{type}/{id}', [CheckoutController::class, 'create']);
    Route::post('/download/{type}/{id}', [DownloadController::class, 'verifyAccess']);
    Route::post('/free-purchase/{type}/{id}', [FreePurchaseController::class, 'create']);
});

// Paddle Webhook
Route::post('/paddle/webhook', [PaddleWebhookController::class, 'handle']);
