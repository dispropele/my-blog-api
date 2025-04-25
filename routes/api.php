<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Первая версия апишки
Route::prefix('v1')->group(function () {

    //Аутентификация /api/v1/auth/
   Route::prefix('auth')->group(function () {
      Route::post('/register', [AuthController::class, 'register']);
      Route::post('/login', [AuthController::class, 'login']);

      //Защита маршрута Sanctum
      Route::middleware('auth:sanctum')->group(function () {
          Route::get('/user', [AuthController::class, 'user']);
          Route::get('/logout', [AuthController::class, 'user']);
      });
   });

       Route::middleware('auth:sanctum')->group(function () {
           //Добавление поста
           Route::post('/posts', [PostController::class, 'store']);
           //Обновление поста
           Route::put('/posts/{post}', [PostController::class, 'update']);
           Route::patch('/posts/{post}', [PostController::class, 'update']);
           //Удаление поста
           Route::delete('/posts/{post}', [PostController::class, 'destroy']);

           //Комментарии
           Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
           Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

           //Подписка на пользователя
           Route::post('/users/{user}/follow', [FollowController::class, 'store']);
           //Отписка от пользователя
           Route::delete('/users/{user}/follow', [FollowController::class, 'destroy']);

       });

       //Публичные маршруты

       //Получение всех постов
       Route::get('/posts', [PostController::class, 'index']);
       //Получение поста по слагу с id
       Route::get('/posts/{slugWithId}', [PostController::class, 'show'])
       ->where('slugWithId', '[a-z0-9-]+-\d+');

       //Комментарии
       Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

        //Список подписок
        Route::get('/users/{user}/following', [FollowController::class, 'followingIndex']);
        //Список подписчиков
        Route::get('/users/{user}/followers', [FollowController::class, 'followerIndex']);

    //Профиль пользователя
    Route::get('/profiles/{user}', [UserProfileController::class, 'show']);

   //Маршруты с категориями
   Route::middleware('auth:sanctum')->group(function () {
       Route::post('/categories', [CategoryController::class, 'store']);
       Route::put('/categories/{category}', [CategoryController::class, 'update']);
       Route::patch('/categories/{category}', [CategoryController::class, 'update']);
       Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
   });

   Route::get('/categories', [CategoryController::class, 'index']);
   Route::get('/categories/{category}', [CategoryController::class, 'show']);

});
