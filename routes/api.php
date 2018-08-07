<?php

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

//IMAGE
Route::get('/images/{image}', 'ImageController@show');
Route::get('/images', 'ImageController@index');
Route::post('/images', 'ImageController@store');
Route::put('/images/{image}', 'ImageController@update');
Route::delete('/images/{image}', 'ImageController@destroy');

//TAG
Route::get('/tags/{tag}', 'TagController@show');
Route::get('/tags', 'TagController@index');
Route::post('/tags', 'TagController@store');
Route::put('/tags/{tag}', 'TagController@update');
Route::delete('/tags/{tag}', 'TagController@destroy');

// POSTS
Route::post('/posts', 'PostController@store');
Route::get('/posts/{post}', 'PostController@show');
Route::get('/posts', 'PostController@index');
Route::put('/posts/{post}', 'PostController@update');
Route::delete('/posts/{post}', 'PostController@destroy');
