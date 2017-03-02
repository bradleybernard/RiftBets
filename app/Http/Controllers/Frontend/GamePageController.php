<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

class GamePageController extends Controller
{
    public function view($apiIdLong)
    {
    	return view('match', ['matchId' => $apiIdLong]);
    }
}
