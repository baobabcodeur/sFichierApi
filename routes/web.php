<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    return 'Connectz-vous';
})->name('login');

Route::get('register', function () {
    return 'vous etes connecter';
})->name('register');
