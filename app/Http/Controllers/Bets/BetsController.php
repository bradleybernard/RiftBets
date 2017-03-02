<?php

namespace App\Http\Controllers\Bets;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use \Carbon\Carbon;

use Validator;
use DB;

class BetsController extends Controller
{
	protected $games;

	public function bet(Request $request)
	{
		$request->merge(['user_credits' => $this->auth->user()->credits]);

		//retrieve if player has already input a bet on the game id specified
		$previousBet = DB::table('bets')
				->where('user_id', $this->auth->user()->id)
				->where('api_game_id', $request['bets'][0]['api_game_id'])
				->get();

		if(!$previousBet)
		{
			throw new \Dingo\Api\Exception\ResourceException('User has already bet on game');
		}

		//retrieve game ID from request
		$gameId = $request['bets'][0]['api_game_id'];

		//check if all game id's are consistent
		foreach ($request['bets'] as $entry) {
			if($entry['api_game_id'] != $gameId)
				throw new \Dingo\Api\Exception\ResourceException('Game ID must match for all bets'); 
		}

		//check if all contents are present in request
		//also validate basic things such as credits bet is positive number
		$validator = Validator::make($request->all(), [
			'bets.*'					=> 'required',
		    'bets.*.api_game_id' 		=> 'required|same:bets.*.api_game_id',
		    'bets.0.api_game_id'		=> 'exists:games,api_id_long',
		    'bets.*.question_slug' 		=> 'required|distinct',
		    'bets.*.user_answer' 		=> 'required',
		    'bets.*.credits_placed' 	=> 'required|integer|min:1',
		    'user_credits'				=> 'integer|min:' . count($request->input('bets.*')),
		]);

		if ($validator->fails()) {
            throw new \Dingo\Api\Exception\ResourceException('Invalid request sent.', $validator->errors());
        }

        //collect and check if user has enough credits to make the bet
		$sum = collect($request->input('bets.*.credits_placed'))->sum();
		$request->merge(['credits_placed' => $sum]);

		$perBetMaximum = ($this->auth->user()->credits - count($request->input('bets.*'))) + 1;

		//validate that question exists and credits placed in the bet are within the acceptable range
		$validator = Validator::make($request->all(), [
		    'bets.*.question_slug' 		=> 'required|exists:questions,slug',
		    'bets.*.credits_placed' 	=> 'required|integer|min:1|max:' . $perBetMaximum,
		    'credits_placed'			=> 'required|integer|max:' . $this->auth->user()->credits,
		]);

		if ($validator->fails()) {
            throw new \Dingo\Api\Exception\ResourceException('Invalid request sent.', $validator->errors());
        }

        //gather match id
        $match = DB::table('games')->select('api_match_id')
        			->where('api_id_long', $request->input('bets.0.api_game_id'))
        			->first();

        //gather games in the match
        $games = DB::table('schedule')
        			->select('games.*')
					->join('matches', 'matches.api_id_long', '=', 'schedule.api_match_id')
					->join('games', 'games.api_match_id', '=', 'matches.api_id_long')
					->where('matches.api_id_long', $match->api_match_id)
					->get()
					->keyBy('api_id_long');

		if(!$games) {
			throw new \Dingo\Api\Exception\ResourceException('Invalid match ID.', $validator->errors());
		}

		//check state of match to see if it's already resolved
		$matchState = DB::table('matches')->select('state')
						->where('api_id_long', $match->api_match_id)
						->first();

		if($matchState->state == 'resolved')
		{
			throw new \Dingo\Api\Exception\ResourceException('Match has already resolved.', $validator->errors());
		}

		$gameStart = DB::table('schedule')->select('scheduled_time')
						->where('api_match_id', $match->api_match_id)
						->first();

		$gameName = $games[$request->input('bets.0.api_game_id')]->name;

		$matchGames = DB::table('games')->select(['name as game_name', 'game_id'])
						->where('api_match_id', $match->api_match_id)
						->get()
						->unique('game_name')
						->keyBy('game_name');

		//compare game time scheduled to time bet is placed
		$mytime = Carbon::now();

		if ($gameName == 'G1')
		{
			$gameStart = Carbon::parse($gameStart->scheduled_time);

			$difference = $mytime->gt($gameStart);

			if ($difference){
				throw new \Dingo\Api\Exception\ResourceException('Invalid bet interval', $validator->errors());
			}
		} else
		{
			$prevGameName = 'G'.(($gameName[1])-1);

			if($matchGames[$prevGameName]->game_id == null)
			{
				throw new \Dingo\Api\Exception\ResourceException('Previous game has not resolved yet', $validator->errors());
			}

			$prevGame = DB::table('game_mappings')->select('created_at')
							->where('game_id', $matchGames[$gameName]->game_id)
							->first();

			$nextGame = Carbon::parse($prevGame->created_at);
			$nextGame->addMinutes(15);

			$difference = $mytime->gt($nextGame);

			if($difference){
				throw new \Dingo\Api\Exception\ResourceException('Invalid bet interval', $validator->errors());
			}
		}


		//insert data into bets table
		$betId = DB::table('bets')->insertGetId([
			'user_id'			=> $this->auth->user()->id,
			'credits_placed'	=> $request['credits_placed'],
			'api_game_id'		=> $request['bets'][0]['api_game_id'],
			'details_placed'	=> count($request['bets'])
		]);


		//assemble and insert data into bet_details table
		$questions = [];

		foreach ($request['bets'] as $bet) {
			array_push($questions, $bet['question_slug']);
		}

		$questionIds = DB::table('questions')->select('id')
						->whereIn('slug', $questions)
						->get();

		$details = [];

		for ($i=0; $i < count($request['bets']); $i++) {
			$details[$i]['question_id'] = $questionIds[$i]->id;
			$details[$i]['bet_id'] = $betId;
			$details[$i]['user_answer'] = $request['bets'][$i]['user_answer'];
			$details[$i]['credits_placed'] = $request['bets'][$i]['credits_placed'];
		}

		DB::table('bet_details')->insert($details);

		$matchId = DB::table('games')->select('api_match_id')
						->where('api_id_long', $request['bets'][0]['api_game_id'])
						->get();

		$matchId = $matchId[0]->api_match_id;

		$fetchSub = DB::table('subscriptions')
        		  ->where('user_id', $this->auth->user()->id)
        		  ->where('api_match_id', $request->input('match_id'))
        		  ->first();

       	if(!$fetchSub)
       	{
       		DB::table('subscriptions')->insert([
    				'user_id'		=> $this->auth->user()->id,
    				'api_match_id'	=> $matchId,
    				'is_bet'		=> true,
    				'is_active'		=> true
    			]);
       	}
       	else
       	{
       		DB::table('subscriptions')
    			->where('user_id', $this->auth->user()->id)
    			->where('api_match_id', $matchId)
    			->update(['is_active' => true, 'is_bet' => true]);
       	}
	}

	public function respond(Request $request)
	{
		$user = $this->auth->user();
		$game_id = $request['game_id'];

		$bets = DB::table('bets')
					->where('bets.is_complete', true)
					->where('bets.user_id', $user->id)
					->where('bets.game_id', $game_id)
					->join('bet_details', 'bet_details.bet_id', '=', 'bets.id')
					->join('questions', 'questions.id', '=', 'bet_details.question_id')
					->join('question_answers', 'question_answers.id', '=', 'bet_details.answer_id')
					->get();
		// dd($bets);
		$summoners = [];
		$items = [];
		$champions = [];
		
		$ddragon = [];

		// Gather answers and user_answers for game
		foreach ($bets as $bet) {
			if($bet->type == 'champion_id') {
				$ddragon['champion_id'] = true;
				$champions[] = $bet->answer;
				$champions[] = $bet->user_answer;
			} if($bet->type == 'champion_id_list_3') {
				$ddragon['champion_id_list_3'] = true;
				$championList = explode(',', $bet->answer);
				foreach ($championList as $champ) {
					$champions[] = $champ;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($userList as $champ) {
					$champions[] = $champ;
				}

			} if($bet->type == 'champion_id_list_5') {
				$ddragon['champion_id_list_5'] = true;
				$championList = explode(',', $bet->answer);
				// dd($championList);
				foreach ($championList as $champ) {
					$champions[] = $champ;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($userList as $champ) {
					$champions[] = $champ;
				}

			} if($bet->type == 'item_id_list') {
				$ddragon['item_id_list'] = true;
				$itemList = explode(',', $bet->answer);
				foreach ($itemList as $item) {
					$items[] = $item;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($userList as $item) {
					$items[] = $item;
				}

			} if($bet->type == 'summoner_id_list') {
				$ddragon['summoner_id_list'] = true;
				$summonerList = explode(',', $bet->answer);
				foreach ($summonerList as $summoner) {
					$summoners[] = $summoner;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($summonerList as $summoner) {
					$summoners[] = $summoner;
				}
			}
		}

		// Make champs, summoners, and items unique
		$summoners = collect($summoners)->unique();
		$items = collect($items)->unique();
		$champions = collect($champions)->unique();

		// dd($champions);


		// Gather data from table that corresponds to values
		if(count($summoners) > 0) {
			$summoners = DB::table('ddragon_summoners')
							->whereIn('api_id', $summoners)
							->get();
		}
		if(count($items) > 0) {
			$items = DB::table('ddragon_items')
							->whereIn('api_id', $items)
							->get();
		}
		if(count($champions) > 0) {
			$champions = DB::table('ddragon_champions')
							->whereIn('api_id', $champions)
							->get();
		}
		$summoners = $summoners->keyBy('api_id');
		$items = $items->keyBy('api_id');
		$champions = $champions->keyBy('api_id');
		// dd($champions);

		foreach ($bets as $bet) {
			if($bet->type == 'champion_id') {
				$bet->user_answer = $champions[$bet->user_answer];
				$bet->answer = $champions[$bet->answer];
				
			} if($bet->type == 'champion_id_list_3' || $bet->type == 'champion_id_list_5') {
				$bet->answer = explode(',', $bet->answer);
				for ($i = 0; $i < count($bet->answer); ++$i) {
					$bet->answer[$i] = $champions[$bet->answer[$i]];
				}
				$bet->user_answer = explode(',', $bet->user_answer);
				for ($i = 0; $i < count($bet->user_answer); ++$i) {
					$bet->user_answer[$i] = $champions[$bet->user_answer[$i]];
				}

			} if($bet->type == 'item_id_list') {
				$bet->answer = explode(',', $bet->answer);
				for ($i = 0; $i < count($bet->answer); ++$i) {
					$bet->answer[$i] = $items[$bet->answer[$i]];
				}
				$bet->user_answer = explode(',', $bet->user_answer);
				for ($i = 0; $i < count($bet->user_answer); ++$i) {
					$bet->user_answer[$i] = $items[$bet->user_answer[$i]];
				}

			} if($bet->type == 'summoner_id_list') {
				$bet->answer = explode(',', $bet->answer);
				for ($i = 0; $i < count($bet->answer); ++$i) {
					$bet->answer[$i] = $summoners[$bet->answer[$i]];
				}
				$bet->user_answer = explode(',', $bet->user_answer);
				for ($i = 0; $i < count($bet->user_answer); ++$i) {
					$bet->user_answer[$i] = $summoners[$bet->user_answer[$i]];
				}
			}
		}
		// dd($bets);

		return $bets;
	}

	public function gameBet(Request $request)
	{
		$user = $this->auth->user();
		$game_id = $request['api_game_id'];

		// dd($user);

		$bets = DB::table('bets')
					->where('bets.is_complete', true)
					->where('bets.user_id', $user->id)
					->where('bets.api_game_id', $game_id)
					->join('bet_details', 'bet_details.bet_id', '=', 'bets.id')
					->join('questions', 'questions.id', '=', 'bet_details.question_id')
					->leftJoin('question_answers', 'question_answers.id', '=', 'bet_details.answer_id')
					->get();

		$match = DB::table('matches')
					->join('games', 'games.api_match_id', '=', 'matches.api_id_long')
					->where('games.api_id_long', $game_id)
					->first();


		$teams = DB::table('teams')
					->join('rosters', 'teams.api_id', '=', 'rosters.api_team_id')
					->whereIn('rosters.api_id_long', [$match->api_resource_id_one, $match->api_resource_id_two])
					->get()
					->keyBy('api_id_long');

		$players = DB::table('players')->join('team_players', 'team_players.api_player_id', '=', 'players.api_id')
                    ->where('team_players.is_starter', true)
                    ->whereIn('team_players.api_team_id', $teams->pluck('api_id'))
                    ->get()
                    ->groupBy('api_team_id');

        $players->transform(function ($value, $index) {
            $value = $value->keyBy('role_slug');
            return $value;
        });


        $teamOne = $teams->get($match->api_resource_id_one);
        $teamTwo = $teams->get($match->api_resource_id_two);

        // Replaces term in question with data from library
        $replaces = [
            '%team_one%'                => $teamOne->acronym,
            '%team_two%'                => $teamTwo->acronym,
            '%team_one_top_player%'     => $this->formatPlayer($teamOne, $players->get($teamOne->api_team_id)->get('toplane')),
            '%team_two_top_player%'     => $this->formatPlayer($teamTwo, $players->get($teamTwo->api_team_id)->get('toplane')),
            '%team_one_jungle_player%'  => $this->formatPlayer($teamOne, $players->get($teamOne->api_team_id)->get('jungle')),
            '%team_two_jungle_player%'  => $this->formatPlayer($teamTwo, $players->get($teamTwo->api_team_id)->get('jungle')),
            '%team_one_mid_player%'     => $this->formatPlayer($teamOne, $players->get($teamOne->api_team_id)->get('midlane')),
            '%team_two_mid_player%'     => $this->formatPlayer($teamTwo, $players->get($teamTwo->api_team_id)->get('midlane')),
            '%team_one_adc_player%'     => $this->formatPlayer($teamOne, $players->get($teamOne->api_team_id)->get('adcarry')),
            '%team_two_adc_player%'     => $this->formatPlayer($teamTwo, $players->get($teamTwo->api_team_id)->get('adcarry')),
            '%team_one_support_player%' => $this->formatPlayer($teamOne, $players->get($teamOne->api_team_id)->get('support')),
            '%team_two_support_player%'  => $this->formatPlayer($teamTwo, $players->get($teamTwo->api_team_id)->get('support')),
        ];

        $bets->transform(function ($value, $index) use ($replaces) {
            if(strpos($value->description, '%') !== false) {
                foreach($replaces as $replaceK => $replaceV) {
                    $value->description = str_replace($replaceK, $replaceV, $value->description);
                }
            }
            return $value;
        });

        // dd($bets);

		// dd($bets);
		$summoners = [];
		$items = [];
		$champions = [];
		
		$ddragon = [];

		// Gather answers and user_answers for game
		foreach ($bets as $bet) {
			if($bet->type == 'champion_id') {
				$ddragon['champion_id'] = true;
				$champions[] = $bet->answer;
				$champions[] = $bet->user_answer;
			} else if($bet->type == 'champion_id_list_3') {
				$ddragon['champion_id_list_3'] = true;
				$championList = explode(',', $bet->answer);
				foreach ($championList as $champ) {
					$champions[] = $champ;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($userList as $champ) {
					$champions[] = $champ;
				}

			} else if($bet->type == 'champion_id_list_5') {
				$ddragon['champion_id_list_5'] = true;
				$championList = explode(',', $bet->answer);
				// dd($championList);
				foreach ($championList as $champ) {
					$champions[] = $champ;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($userList as $champ) {
					$champions[] = $champ;
				}

			} else if($bet->type == 'item_id_list') {
				$ddragon['item_id_list'] = true;
				$itemList = explode(',', $bet->answer);
				foreach ($itemList as $item) {
					$items[] = $item;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($userList as $item) {
					$items[] = $item;
				}

			} else if($bet->type == 'summoner_id_list') {
				$ddragon['summoner_id_list'] = true;
				$summonerList = explode(',', $bet->answer);
				foreach ($summonerList as $summoner) {
					$summoners[] = $summoner;
				}
				$userList = explode(',', $bet->user_answer);
				foreach ($summonerList as $summoner) {
					$summoners[] = $summoner;
				}
			}
		}

		// Make champs, summoners, and items unique
		$summoners = collect($summoners)->unique();
		$items = collect($items)->unique();
		$champions = collect($champions)->unique();

		// dd($champions);


		// Gather data from table that corresponds to values
		if(count($summoners) > 0) {
			$summoners = DB::table('ddragon_summoners')
							->whereIn('api_id', $summoners)
							->get();
		}
		if(count($items) > 0) {
			$items = DB::table('ddragon_items')
							->whereIn('api_id', $items)
							->get();
		}
		if(count($champions) > 0) {
			$champions = DB::table('ddragon_champions')
							->whereIn('api_id', $champions)
							->get();
		}
		$summoners = $summoners->keyBy('api_id');
		$items = $items->keyBy('api_id');
		$champions = $champions->keyBy('api_id');
		// dd($champions);

		foreach ($bets as $bet) {
			if($bet->type == 'team_id') {
				$bet->user_answer = $teams->where('api_id_long', ($bet->user_answer == 100 ? $match->api_resource_id_one : $match->api_resource_id_two))->first();
				$bet->answer = $teams->where('api_id_long', ($bet->answer == 100 ? $match->api_resource_id_one : $match->api_resource_id_two))->first();
			} 

			if($bet->type == 'champion_id') {
				$bet->user_answer = $champions[$bet->user_answer];
				$bet->answer = $champions[$bet->answer];
				
			} if($bet->type == 'champion_id_list_3' || $bet->type == 'champion_id_list_5') {
				$bet->answer = explode(',', $bet->answer);
				for ($i = 0; $i < count($bet->answer); ++$i) {
					$bet->answer[$i] = $champions[$bet->answer[$i]];
				}
				$bet->user_answer = explode(',', $bet->user_answer);
				for ($i = 0; $i < count($bet->user_answer); ++$i) {
					$bet->user_answer[$i] = $champions[$bet->user_answer[$i]];
				}

			} if($bet->type == 'item_id_list') {
				$bet->answer = explode(',', $bet->answer);
				for ($i = 0; $i < count($bet->answer); ++$i) {
					$bet->answer[$i] = $items[$bet->answer[$i]];
				}
				$bet->user_answer = explode(',', $bet->user_answer);
				for ($i = 0; $i < count($bet->user_answer); ++$i) {
					$bet->user_answer[$i] = $items[$bet->user_answer[$i]];
				}

			} if($bet->type == 'summoner_id_list') {
				$bet->answer = explode(',', $bet->answer);
				for ($i = 0; $i < count($bet->answer); ++$i) {
					$bet->answer[$i] = $summoners[$bet->answer[$i]];
				}
				$bet->user_answer = explode(',', $bet->user_answer);
				for ($i = 0; $i < count($bet->user_answer); ++$i) {
					$bet->user_answer[$i] = $summoners[$bet->user_answer[$i]];
				}
			}
		}
		// dd($bets);

		return $bets;
	}

	 /************************************************
    * Formats player name with their slug
    *
    * Paramaters: team, player
    * Returns: string of player name with team prefix
    * Examples: $team = 'TSM', $player = 'Doublelift'
    *           Returns 'TSM Doublelift'
    ************************************************/
    private function formatPlayer($team, $player) 
    {
        return $team->acronym . ' ' . $player->name;
    }
}
