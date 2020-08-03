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

//Carga de clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

//API-ROUTES TEST

//Route::get('user','UserController@index');
//Route::get('test','PostController@testOrm');
//Route::get('post','PostController@index');
//Route::get('category','CategoryController@index');

// USER-ROUTES
Route::post('api/register','UserController@register');
Route::post('api/login','UserController@login');
Route::put ('api/user/update','UserController@update');
Route::post ('api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/avatar/{filename}','UserController@getImage');
Route::get('api/user/profile/{id}','UserController@profile');

// CATEGORIES-ROUTES
Route::resource('api/category','CategoryController');

// POST-ROUTES
Route::resource('api/post','PostController');
