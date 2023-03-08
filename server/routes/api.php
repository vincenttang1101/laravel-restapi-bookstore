<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\PublisherController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* For Guest */

Route::get('book/filter', [BookController::class, 'filterMain']);
Route::get('book/statistic', [BookController::class, 'statisticMain']);

/* For Authentication */
Route::group([
    'middleware' => 'api',
], function () {
    Route::group(
        [
            'prefix' => 'auth',
        ],
        function () {
            Route::post('/register', [AuthenticationController::class, 'register']);
            Route::post('/login', [AuthenticationController::class, 'login']);
            Route::post('/refresh-token', [AuthenticationController::class, 'refreshToken']);
            Route::post('/logout', [AuthenticationController::class, 'logout']);
        }
    );
    Route::put('user/change-password', [UserController::class, 'changePassword']);

    /* For Admin */
    Route::group([
        'middleware' => ['can:isAdmin']
    ], function () {
        Route::resource('user', UserController::class);
        Route::resource('book', BookController::class);
        Route::resource('author', AuthorController::class);
        Route::resource('genre', GenreController::class);
        Route::resource('publisher', PublisherController::class);
        Route::resource('order', OrderController::class);
    });

    /* For Member */
    Route::post('order', [OrderController::class, "store"]);
    Route::resource('orderdetail', OrderDetailController::class);
});

/* For Guest */
Route::get('author/{author}', [AuthorController::class, 'show']);
Route::get('book/{book}', [BookController::class, 'show']);
Route::get('genre/{genre}', [GenreController::class, 'show']);
Route::get('publisher/{publisher}', [PublisherController::class, 'show']);


Route::get('book/search/{keyword}', [BookController::class, 'searchMain']);

Route::get('publisher/paginate/{perPage}', [PublisherController::class, 'paginateTemplate']);
Route::get('genre/paginate/{id}', [GenreController::class, 'paginateTemplate']);
Route::get('author/paginate/{perPage}', [AuthorController::class, 'paginateTemplate']);
Route::get('book/paginate/{perPage}', [BookController::class, 'paginateTemplate']);
Route::get('user/paginate/{perPage}', [UserController::class, 'paginateTemplate']);

Route::get('book', [BookController::class, 'index']);
Route::get('author', [AuthorController::class, 'index']);
Route::get('genre', [GenreController::class, 'index']);
Route::get('publisher', [PublisherController::class, 'index']);
