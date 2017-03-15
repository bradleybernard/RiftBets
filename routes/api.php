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

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => 'jwt'], function ($api) 
{
    $api->post('auth/facebook', 'App\Http\Controllers\Facebook\FacebookController@facebook');

    $api->get('schedule', 'App\Http\Controllers\Queries\ScheduleController@query');
    $api->get('match', 'App\Http\Controllers\Queries\MatchDetailsController@query');
    $api->get('schedule/past', 'App\Http\Controllers\Schedule\PastMatchesController@show');

    // $api->get('match/bettable', 'App\Http\Controllers\Queries\MatchDetailsController@bettable');
    $api->get('leaderboards', 'App\Http\Controllers\Leaderboards\LeaderboardsController@leaderboards');
    $api->get('leaderboards/rank', 'App\Http\Controllers\Leaderboards\LeaderboardsController@rank');
    $api->get('leaderboards/around', 'App\Http\Controllers\Leaderboards\LeaderboardsController@around');
    $api->get('profile', 'App\Http\Controllers\Queries\UserProfileController@query');

    $api->group(['middleware' => 'api.auth'], function ($api) {
        $api->get('bets/gamebet', 'App\Http\Controllers\Bets\BetsController@gameBet');
        $api->get('bets/reply', 'App\Http\Controllers\Bets\BetsController@respond');
        $api->post('bets/create', 'App\Http\Controllers\Bets\BetsController@bet');
        $api->get('bets/response', 'App\Http\Controllers\Queries\UserProfileController@query');
        $api->get('user/bets', 'App\Http\Controllers\Queries\UserBetsController@query');
        $api->post('cards/create', 'App\Http\Controllers\Queries\CardController@generate');
        $api->get('subscribe/check', 'App\Http\Controllers\Subscriptions\SubscriptionsController@checkSubscription');
        $api->get('subscribe/modify', 'App\Http\Controllers\Subscriptions\SubscriptionsController@modifySubcription');
        $api->get('leaderboards/signedin', 'App\Http\Controllers\Leaderboards\LeaderboardsController@leaderboards_signedin');
        $api->post('auth/test', 'App\Http\Controllers\Facebook\FacebookController@add_friends');
    });
});
