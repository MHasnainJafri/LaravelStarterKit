<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mhasnainjafri\RestApiKit\API;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// API::SUCCESS;
Route::restifyAuth();
// Route::restifyAuth(['login', 'register']);

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

// public function render($request, Exception $exception)
// {
//     if ($exception instanceof ValidationException) {
//         return $this->response()
//             ->errors($exception->errors())
//             ->status(422);
//     }

//     if ($exception instanceof UnauthorizedException) {
//         return $this->response()
//             ->message('Unauthorized access')
//             ->status(403);
//     }

//     return $this->response()
//         ->message('Server error')
//         ->status(500);
// }

// use Mhasnainjafri\RestApiKit\ActionMacroManager;

// // In your AppServiceProvider or any bootstrapping file:
// app(ActionMacroManager::class)->macro('greetUser', function ($name) {
//     return "Hello, {$name}!";
// });

// public function index()
// {
//     // Use cacheResponse to cache or retrieve the data
//     return $this->cacheResponse('users.index', function () {
//         return User::all();
//     }, 30); // Cache for 30 minutes
// }
