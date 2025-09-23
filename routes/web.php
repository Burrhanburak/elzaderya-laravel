<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test checkout page
Route::get('/checkout/test', function (Illuminate\Http\Request $request) {
    $type = $request->get('type', 'book');
    $id = $request->get('id', '1');
    $title = $request->get('title', 'Test Product');
    $price = $request->get('price', '0');
    $email = $request->get('email', 'test@example.com');
    $paddlePriceId = $request->get('paddle_price_id', 'pri_01k5phkk6fs472f0rxp3yvd05r');
    
    return view('checkout.test', compact('type', 'id', 'title', 'price', 'email', 'paddlePriceId'));
});

// Success page
Route::get('/success', function () {
    return view('checkout.success');
});

// Cancel page
Route::get('/cancel', function () {
    return view('checkout.cancel');
});
