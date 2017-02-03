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

Route::get('/auth/facebook', 'Auth\AuthController@redirectToProvider');
Route::get('/auth/facebook/callback', 'Auth\AuthController@handleProviderCallback');
<<<<<<< HEAD
Route::get('stats', 'Scrape\StatsController@scrape');
Route::get('/questions', 'Questions\QuestionsController@insertQuestions');

// Route::get('/', function () {
//     return view('welcome');
// });
=======
>>>>>>> fe8199a5eac51b93cbfa1a5b4803a37a1cef4c9d

//Delete soon plz
Route::get('stats', 'Scrape\StatsController@scrape');
Route::get('/test', 'TestController@test');
