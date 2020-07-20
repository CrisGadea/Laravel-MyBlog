<?php

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

Route::get('/', function () {
    return view('welcome');
});

//API-ROUTES

Route::get('user','UserController@index');
Route::get('test','PostController@testOrm');
Route::get('post','PostController@index');
Route::get('category','CategoryController@index');

// USER-ROUTES
Route::post('api/register','UserController@register');
Route::post('api/login','UserController@login');
