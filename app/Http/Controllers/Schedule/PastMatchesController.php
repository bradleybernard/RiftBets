<?php

namespace App\Http\Controllers\Schedule;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Validator;
use \Carbon\Carbon;

class PastMatchesController extends Controller
{

    /************************************************
    * Generates past matches JSON
    *
    * Paramaters: request
    * Returns: JSON with past matches and record
    ************************************************/

    public function show(Request $request)
    {
    	//
        $teamID = $request['teamID'];

        // Initialize validator class
        // Checks if match id is in DB
        $validator = Validator::make($request->all(), [
            'teamID' => 'exists:teams,api_id'
        ]);

        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\ResourceException('Invalid match id.', $validator->errors());
        }

        $test = DB::table('teams')->select('name')->where('api_id', $teamID)->get();
    	dd($test);

        // schedule.scheduled_time, matches.score_one/two, rosters.name, teams.logo_url
        // game_team_stats -> win?-flag, 

    }
}
