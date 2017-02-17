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

    public function show()
    {
    	//
    	dd('hello');

    }
}
