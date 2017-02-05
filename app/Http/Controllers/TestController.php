<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\BroadcastForMatches;

class TestController extends Controller
{
    public function test()
    {
    	$game = [
    		'api_match_id' => '14cecce8-f45b-42cb-8478-45c357a2d099',
    		'api_game_id'  => '0e023eeb-4959-41da-8ce4-289b3b5e2e04'
    	];
    	event(new BroadcastForMatches($game));
    }
}
