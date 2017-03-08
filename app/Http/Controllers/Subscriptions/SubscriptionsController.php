<?php

namespace App\Http\Controllers\Subscriptions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Validator;
use App\Jobs\SendMail;

class SubscriptionsController extends Controller
{
	// Check if user has already subscribed to match
	// or if a subscription is active.
	// Returns false if not subscribed and true if subscribed
	public function checkSubscription(Request $request)
	{
		// Initialize validator class
        // Checks if match id is in DB
        $this->validateRequest($request);

        $subscription = $this->fetchSubscription($request);

        $response = [
        	'match_id' => $request->input('match_id'),
        	'is_susbscribed' => ($subscription ? ($subscription->is_active ? true : false) : false)
        ];

        return $this->response->array($response);
	}

    public function modifySubcription(Request $request)
    {
    	$this->validateRequest($request);

    	$validator = Validator::make($request->all(), [
    		'status'	=> 'required|boolean'
    	]);

    	if ($validator->fails()) {
            throw new \Dingo\Api\Exception\ResourceException('Invalid status.', $validator->errors());
        }

        if($this->fetchSubscription($request)) {
        	DB::table('subscriptions')
    			->where('user_id', $this->auth->user()->id)
    			->where('api_match_id', $request->input('match_id'))
    	 		->update(['is_active' => $request->input('status')]);
        } else {
        	if($request->input('status')) {
        		$subId = DB::table('subscriptions')->insertGetId([
    				'user_id'		=> $this->auth->user()->id,
    				'api_match_id'	=> $request->input('match_id'),
    				'is_active'		=> true
    			]);

                $games = DB::table('games')->select('api_id_long', 'name')
                            ->where('api_match_id', $request->input('match_id'))
                            ->get();

                foreach($games as $game)
                {
                    DB::table('subscription_details')->insert([
                        'subscription_id'   => $subId,
                        'api_game_id'       => $game->api_id_long,
                        'name'              => $game->name
                    ]);
                }

        	} else {
        		throw new \Dingo\Api\Exception\ResourceException('No subscription to remove.', $validator->errors());
        	}
        }

        $subscription = $this->fetchSubscription($request);

        $response = [
        	'match_id' 	=> $subscription->api_match_id,
        	'is_susbscribed'	=> $subscription->is_active
        ];

        return $this->response->array($response);
    }


    public function fetchSubscription(Request $request)
    {
    	return DB::table('subscriptions')
        					->where('user_id', $this->auth->user()->id)
        					->where('api_match_id', $request->input('match_id'))
        					->first();
    }

    public function validateRequest(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'match_id' => 'required|exists:matches,api_id_long'
        ]);

        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\ResourceException('Invalid match id.', $validator->errors());
        }
    }
}
