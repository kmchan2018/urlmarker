<?php

use Illuminate\Support\Facades\Route;

// API
Route::post('/action', '\App\Http\Controllers\ActionController')->name('action');

