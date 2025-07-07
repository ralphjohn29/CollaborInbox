<?php

use Illuminate\Support\Facades\Route;

// Simple test route
Route::get('/simple-test', function() {
    return response('Simple test route is working!');
}); 