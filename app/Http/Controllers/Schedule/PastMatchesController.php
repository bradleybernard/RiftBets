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


        // These are the matches where the interesting team was team one
        $team_one = DB::table('teams as t1')            
            ->join('rosters as r1', 't1.api_id', '=', 'r1.api_team_id')
            ->join('matches', 'matches.api_resource_id_one', '=', 'r1.api_id_long')
            ->join('rosters as r2', 'matches.api_resource_id_two', '=', 'r2.api_id_long')
            ->join('teams as t2', 't2.api_id', '=', 'r2.api_team_id')
            ->join('schedule', 'schedule.api_match_id', '=', 'matches.api_id_long')
            ->select(DB::raw('
                r1.name as team_one, t1.logo_url as team_one_logo,
                r2.name as team_two, t2.logo_url as team_two_logo,
                matches.score_one, matches.score_two, 
                (matches.score_one > matches.score_two) as won, matches.api_id_long, schedule.scheduled_time
                '))
            ->where('t1.api_id', $teamID)
            ->whereNotNull('matches.score_one');

        // These are the matches where the interesting team was team two
        $team_two = DB::table('teams as t2')            
            ->join('rosters as r2', 't2.api_id', '=', 'r2.api_team_id')
            ->join('matches', 'matches.api_resource_id_two', '=', 'r2.api_id_long')
            ->join('rosters as r1', 'matches.api_resource_id_one', '=', 'r1.api_id_long')
            ->join('teams as t1', 't1.api_id', '=', 'r1.api_team_id')
            ->join('schedule', 'schedule.api_match_id', '=', 'matches.api_id_long')
            ->select(DB::raw('
                r1.name as team_one, t1.logo_url as team_one_logo,
                r2.name as team_two, t2.logo_url as team_two_logo,
                matches.score_one, matches.score_two, 
                (matches.score_one < matches.score_two) as won, matches.api_id_long, schedule.scheduled_time
                '))
            ->where('t2.api_id', $teamID)
            ->whereNotNull('matches.score_one');

        $all_matches = $team_two        
            ->union($team_one)
            ->get();

            // sample entry
            // {#370 ▼
            //   +"team_one": "FLY"
            //   +"team_one_logo": "https://lolstatic-a.akamaihd.net/esports-assets/production/team/flyquest-89bnqpyh.png"
            //   +"team_two": "TSM"
            //   +"team_two_logo": "https://lolstatic-a.akamaihd.net/esports-assets/production/team/team-solomid-cg2byxoe.png"
            //   +"score_one": 0
            //   +"score_two": 1
            //   +"WON": 1
            //   +"api_id_long": "bbbad703-2f40-4b4d-b9a5-a1c7ccf7e9dc"
            //   +"scheduled_time": "2017-02-19 20:00:00"
            // }

    	$all_matches = $all_matches->sortByDesc('scheduled_time')->values();
        $all_matches = $all_matches->slice(0, 5);  

        foreach ($all_matches as $match) {

            $team_one_wins = DB::table('games')
            ->join('game_team_stats as stats', 'games.game_id', '=', 'stats.game_id')
            ->where('games.api_match_id', '=', $match->api_id_long)
            ->where('stats.team_id', '=', 100)
            ->select(DB::raw('
                sum(win) as sum
                '))
            ->get()
            ->toArray();
            $match->score_one = $team_one_wins[0]->sum;

            $team_two_wins = DB::table('games')
            ->join('game_team_stats as stats', 'games.game_id', '=', 'stats.game_id')
            ->where('games.api_match_id', '=', $match->api_id_long)
            ->where('stats.team_id', '=', 200)
            ->select(DB::raw('
                sum(win) as sum
                '))
            ->get()
            ->toArray();
            $match->score_two = $team_two_wins[0]->sum;

        }
        // dd($all_matches);
        return $this->response->array($all_matches->toArray());

    }
}
