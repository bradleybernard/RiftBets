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

Route::get('/', function() {
    return  'Home page';
});

Route::get('/auth/facebook', 'Auth\AuthController@redirectToProvider');
Route::get('/auth/facebook/callback', 'Auth\AuthController@handleProviderCallback');
Route::get('stats', 'Scrape\StatsController@scrape');
Route::get('/test', 'TestController@test');

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

// Route::get('/home', 'HomeController@index');