<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class GamePageController extends Controller
{
    public function view($apiIdLong)
    {
        $match = DB::table('matches')
                ->select(['matches.*', 'brackets.match_best_of'])
                ->join('brackets', 'brackets.api_id_long', '=', 'matches.api_bracket_id')
                ->where('matches.api_id_long', $apiIdLong)
                ->first();
        
        if(!$match) {
            return redirect('/schedule');
        }

    	return view('match')->with(['match' => $match]);
    }
}
