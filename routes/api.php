<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Route::restifyAuth();
Route::restifyAuth(['login', 'register']);

// try {
//     throw new \Exception("An error occurred");
// } catch (\Exception $exception) {
//     return $this->response()
//         ->message('An error occurred while processing your request.')
//         ->line($exception->getLine())
//         ->file($exception->getFile())
//         ->stack($exception->getTraceAsString())
//         ->errors(['Something went wrong'])
//         ->addError('Specific error message')
//         ->toResponse();
// }
// return $this->response()
// ->paginate($users, 'users')
// ->message('Users retrieved successfully.')
// ->toResponse();     
//    $filePath = $this->upload($file, 'uploads/documents');
// php artisan vendor:publish --tag=restapikit-stubs


