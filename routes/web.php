<?php

use Illuminate\Support\Facades\Route;

// Todo: add fallback route
Route::get('/phpinfo', function () {
  return phpinfo();
});