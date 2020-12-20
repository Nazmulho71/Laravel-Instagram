<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\FollowsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PostsController::class, 'index'])->name('posts.index');

Route::get('/p/create', [PostsController::class, 'create'])->name('posts.create');
Route::post('/p/create', [PostsController::class, 'store']);

Route::get('/p/{post}', [PostsController::class, 'show'])->name('posts.show');

Auth::routes();

Route::get('/profile/{user:username}', [ProfilesController::class, 'show'])->name('profile.show');

Route::get('/profile/{user:username}/edit', [ProfilesController::class, 'edit'])->name('profile.edit');
Route::put('/profile/{user:username}/edit', [ProfilesController::class, 'update']);

Route::post('/follows/{user}', [FollowsController::class, 'store']);
