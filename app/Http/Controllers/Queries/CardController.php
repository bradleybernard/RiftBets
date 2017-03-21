<?php

namespace App\Http\Controllers\Queries;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Validator;
use \Carbon\Carbon;

class CardController extends Controller
{   
    /************************************************
    * Generates game card and JSON
    *
    * Paramaters: request
    * Returns: JSON with question cards and potential answers
    ************************************************/

    public function generate(Request $request)
    {
    	// $request.keys = [game_id, question_count, difficulty, reroll]

        // Initialize validator class
        // Validator checks:
        //      If game_id exists in DB
        //      If there is a question_count key in request
    	$validator = Validator::make($request->all(), [
            'api_game_id'       => 'exists:games,api_id_long',
            'question_count'    => 'required'       
        ]);
        
        $cardExists = DB::table('cards')
                ->where('user_id', $this->auth->user()->id)
                ->where('api_game_id', $request->input('api_game_id'))
                ->first();

        $card = (object)[];

        if(!$request->has('reroll') || ($request->has('reroll') && $request->input('reroll') == false)) {
            // no reroll give their card or create one if they dont have one

            if($cardExists) {
                return $this->getCard($request, $card, $cardExists->id);
            }
            
            return $this->insertCard($request, $card, true);

        } else if($request->has('reroll') && $request->input('reroll') == true) {
            // they want reroll unless they are out they get their card back
            // check if no card cant reroll nothing
            if(!$cardExists) {
                return $this->insertCard($request, $card, true);
            }

            $rrCard = DB::table('card_rerolls')->where('user_id', $this->auth->user()->id)->where('api_game_id', $request->input('api_game_id'))->first();

            if($rrCard->reroll_count >= 3) {
                return $this->getCard($request, $card, $cardExists->id);
            } else {
                return $this->insertCard($request, $card, false);
            }

        }
    }

    private function getCard($request, &$card, $cardId) {

        $questions = DB::table('card_details')
                                ->join('questions', 'card_details.question_id', '=', 'questions.id')
                                ->select(['questions.id as question_id', 'slug', 'difficulty','multiplier', 'type', 'description'])
                                ->where('card_details.card_id', $cardId)
                                ->get();    

        $rrCard = DB::table('card_rerolls')->where('user_id', $this->auth->user()->id)->where('api_game_id', $request->input('api_game_id'))->first();

        return $this->makeCard($request, $card, $questions, false, $rrCard->reroll_count, false);
    }

    private function insertCard($request, &$card, $new) {
        $questions = DB::table('questions')->select(['id as question_id', 'slug', 'difficulty','multiplier', 'type', 'description'])->get();

        // Save and remove default question from potential question list
        $defaultQuestion = $questions->get('1');
        $questions->forget('1');

        // If there is a question difficulty, select only that difficulty questions
        if($request->has('difficulty'))
        {
            $questions = $questions->where('difficulty', $request->input('difficulty'));
        }

        // Randomly pull the amount of questions
        $questions = $questions->random($request->input('question_count'));

        if($new) {
            DB::table('card_rerolls')->insert([
                'user_id'       => $this->auth->user()->id,
                'api_game_id'   => $request->input('api_game_id'),
                'reroll_count'  => 0,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);
        } else {
            DB::table('card_rerolls')
                ->where('user_id', $this->auth->user()->id)
                ->where('api_game_id', $request->input('api_game_id'))
                ->update([
                    'reroll_count'  => DB::raw('reroll_count + 1'),
                    'updated_at'    => Carbon::now(),
                ]);

            DB::table('card_details')
                ->join('cards', 'cards.id', '=', 'card_details.card_id')
                ->where('cards.user_id', $this->auth->user()->id)
                ->where('cards.api_game_id', $request->input('api_game_id'))
                ->delete();

            DB::table('cards')
                ->where('user_id', $this->auth->user()->id)
                ->where('api_game_id', $request->input('api_game_id'))
                ->delete();
        }

        $rrCount = DB::table('card_rerolls')
                        ->where('user_id', $this->auth->user()->id)
                        ->where('api_game_id', $request->input('api_game_id'))
                        ->first();

        return $this->makeCard($request, $card, $questions, $defaultQuestion, $rrCount->reroll_count, true);
    }

    private function makeCard($request, &$card, $questions, $defaultQuestion, $rrCount, $insert) {
        $questions = $this->generateQuestions($request, $card, $questions, $defaultQuestion);

        $card->user_id = $this->auth->user()->id;
        $card->reroll_count = $rrCount;
        $card->reroll_remaining = 3 - $rrCount;
        $card->questions = $questions;

        $card->champions = DB::table('ddragon_champions')
            ->join('math_stats', 'math_stats.api_id', '=', 'ddragon_champions.api_id')
            ->select(['ddragon_champions.api_id', 'ddragon_champions.champion_name', 'ddragon_champions.image_url', 'math_stats.ban_scale', 'math_stats.pick_scale'])
                        ->get()
                        ->toArray();

        $card->items = DB::table('ddragon_items')->select(['api_id', 'name as item_name', 'image_url'])
                        ->get()
                        ->toArray();

        $card->summmoners = DB::table('ddragon_summoners')->select(['api_id', 'name as summoner_name', 'image_url'])
                        ->get()
                        ->toArray();

        if($insert) {
            // Creates new game card and grabs the id
            $cardId = DB::table('cards')->insertGetId([
                'user_id'           => $card->user_id,
                'api_game_id'       => $request->input('api_game_id'),
                'details_placed'    => $request->input('question_count'),
                'created_at'        => Carbon::now()->toDateTimeString(),
            ]);

            // Inserts every question card into the DB
            foreach ($questions as $question)
            {
                DB::table('card_details')->insert([
                    'card_id'       => $cardId,
                    'question_id'   => $question->question_id,
                    'created_at'    => Carbon::now()->toDateTimeString(),
                ]);
            }
        }

        return $this->response->array((array)$card);
    }



     /************************************************
    * Generates question cards to the user
    *
    * Paramaters: request, card
    * Returns: JSON with question cards
    ************************************************/

    private function generateQuestions($request, &$card, $questions, $defaultQuestion)
    {

        // Adds libraries to fetch player and game data from
        $match = DB::table('games')->select('matches.*')->join('matches', 'matches.api_id_long', '=', 'games.api_match_id')
                ->where('games.api_id_long', $request['api_game_id'])
                ->get();

        $resources = $match->pluck('api_resource_id_one')->push($match->pluck('api_resource_id_two')->first());

        $teams = DB::table('rosters')
            ->join('teams', 'rosters.api_team_id', '=', 'teams.api_id')
            ->whereIn('rosters.api_id_long', $resources->all())
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

        // Adds default question to the list of questions
        if ($defaultQuestion)
            $questions->prepend($defaultQuestion);

        $teamOne = $teams->get($match->pluck('api_resource_id_one')->first());
        $teamTwo = $teams->get($match->pluck('api_resource_id_two')->first());

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


        $questions = $questions->reject(function($value, $index) {
            return ($value ? false : true);
        });

        $questions->transform(function ($value, $index) use ($replaces) {
            if(strpos($value->description, '%') !== false) {
                foreach($replaces as $replaceK => $replaceV) {
                    $value->description = str_replace($replaceK, $replaceV, $value->description);
                }
            }
            return $value;
        });

        $teams->transform(function ($value, $index)  use ($match) {
            if($value->api_id_long == $match->pluck('api_resource_id_one')->first()) {
                $value->match_team_id = 100;
            } else if($value->api_id_long == $match->pluck('api_resource_id_two')->first()) {
                $value->match_team_id = 200;
            }
            return $value;
        });

        $card->teams = $teams->keyBy('match_team_id');

        return $questions->toArray();
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
