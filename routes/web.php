<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/dashboard', function () {
    return view('app');
})->middleware('auth')->name('dashboard');

Route::get('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout.get');

Route::fallback(function () {
    return view('app');
})->middleware('auth');
