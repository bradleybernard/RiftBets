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
Route::get('/questions', 'Questions\QuestionsController@insertQuestions');
Route::get('/grade', 'Schedule\GradingController@grade');

Route::get('/', function () {
    return view('home');
});

Route::get('/schedule', function () {
    return view('schedule');
});

Route::get('/userbets', function() {
    return view('userbets');
});

Route::get('/bets', function () {
    return view('bets');
});

Route::get('match/{apiIdLong}', 'Frontend\GamePageController@view');

Route::get('/leaderboard', function () {
    return view('leaderboard');
});

Route::get('/clear', 'Bets\BetsController@clear');

//Delete soon plz
Route::get('stats', 'Scrape\StatsController@scrape');
Route::get('/betsTest', 'Schedule\GradingController@bets');
Route::get('/test', 'TestBroadcastController@test');
Route::get('/streams', 'Scrape\StreamsController@scrape');
Route::get('/event', 'Queries\GameEventsController@query');
