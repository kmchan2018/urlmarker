<?php

use Illuminate\Support\Facades\Route;

// Home
Route::get('/home', '\App\Http\Controllers\HomeController@show')->name('home');
Route::get('/', '\App\Http\Controllers\HomeController@redirect');

// Marker
Route::get('/markers', '\App\Http\Controllers\MarkerController@index')->name('markers');
Route::put('/markers', '\App\Http\Controllers\MarkerController@create');
Route::patch('/markers/{id}', '\App\Http\Controllers\MarkerController@update');

// Trashcan
Route::get('/trashcan', '\App\Http\Controllers\TrashcanController@index')->name('trashcan');
Route::patch('/trashcan/{id}', '\App\Http\Controllers\TrashcanController@update');

// Users
Route::get('/users/', '\App\Http\Controllers\UserController@index')->name('users');
Route::patch('/users/{id}', '\App\Http\Controllers\UserController@update');

// Invites
Route::get('/invites/', '\App\Http\Controllers\InviteController@index')->name('invites');
Route::put('/invites/', '\App\Http\Controllers\InviteController@create');
Route::delete('/invites/{id}', '\App\Http\Controllers\InviteController@delete');

// Resets
Route::get('/resets/', '\App\Http\Controllers\ResetController@index')->name('resets');
Route::delete('/resets/{id}', '\App\Http\Controllers\ResetController@delete');

// Register
Route::get('/register', '\App\Http\Controllers\RegisterController@show')->name('register');
Route::post('/register', '\App\Http\Controllers\RegisterController@handle');

// Login
Route::get('/login', '\App\Http\Controllers\LoginController@show')->name('login');
Route::post('/login', '\App\Http\Controllers\LoginController@handle');

// Logout
Route::get('/logout', '\App\Http\Controllers\LogoutController@handle')->name('logout');
Route::post('/logout', '\App\Http\Controllers\LogoutController@handle');

// Password Update
Route::get('/password/update', '\App\Http\Controllers\PasswordUpdateController@show')->name('password_update');
Route::post('/password/update', '\App\Http\Controllers\PasswordUpdateController@handle');

// Password Reset
Route::get('/password/reset', '\App\Http\Controllers\PasswordResetController@show')->name('password_reset');
Route::post('/password/reset', '\App\Http\Controllers\PasswordResetController@handle');

// Userscript Framework
Route::get('/userscripts/framework.js', '\App\Http\Controllers\UserscriptFrameworkController');

