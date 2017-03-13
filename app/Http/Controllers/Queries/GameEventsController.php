<?php

namespace App\Http\Controllers\Queries;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use DB;
use Validator;

class GameEventsController extends Controller
{
    public function query(Request $request)
    {
    	$gameId = 1002080062;

    	$gameEvents = DB::table('game_events')
    					->where('game_id', $gameId)
    					->where('type', 'champion_kill')
    					->get();

        $events = (object) [];
       
        $events->kills = $this->fetchKillEvents($gameId);

        $events->players = $this->fetchPlayerData($gameId);

        $events->building_kills = $this->fetchBuildingKillEvents($gameId);

        return $this->response->array((array)$events);
    }

    public function fetchBuildingKillEvents($game_id)
    {
        $gameId = $game_id;

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->where('type', 'building_kill')
                        ->get();   

        $totalBuildingKills = [];

        foreach ($gameEvents as $event) 
        {
            $details = DB::table('game_event_details')->select(['key','value'])
                            ->where('event_unique_id', $event->unique_id)
                            ->get()
                            ->keyBy('key');

            $gamePlayers = DB::table('game_player_stats')->select(['participant_id', 'summoner_name'])
                            ->where('game_id', $gameId)
                            ->get()
                            ->keyBy('participant_id');

            $building = (object) [];

            $building->timestamp = $event->timestamp;
            $building->game_time = $this->milliToTime($event->timestamp);
            $building->killer_id = $details['killer_id']->value;
            $building->killer_name = ($details['killer_id']->value == 0 ? 'Minions' : $gamePlayers[$details['killer_id']->value]->summoner_name);
            $vicTeam = $this->findPlayerTeam($gamePlayers[($details['team_id']->value == 100 ? 1 : 6)]->summoner_name);
            $building->victim_team = $vicTeam->name;
            $building->victim_team_full = DB::table('teams')->select(['name'])
                                                ->where('api_id', $vicTeam->api_team_id)
                                                ->get()
                                                ->first()
                                                ->name;
            $building->building_type = $details['building_type']->value; 
            $building->lane_type = $details['lane_type']->value;
            $building->tower_type = $details['tower_type']->value;

            array_push($totalBuildingKills, $building);      
        }

        return $totalBuildingKills;
    }

    public function fetchPlayerData($game_id)
    {
        $gameId = $game_id;

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->whereIn('type', ['item_purchased','item_sold'])
                        ->get();

        $gamePlayers = DB::table('game_player_stats')->select(['participant_id', 'summoner_name'])
                            ->where('game_id', $gameId)
                            ->get()
                            ->keyBy('participant_id');

        $participant = [];

        for ($i=1; $i <= 10; $i++) 
        { 
            $player = (object) [];
            $player->participant_id = $i;
            $player->name = $gamePlayers[$i]->summoner_name;
            $player->api_id = $this->findPlayerApiId($player->name);
            $player->purchase_history = [];
            $player->skill_order = [];
            array_push($participant, $player);
        }

        foreach ($gameEvents as $event) 
        {
            $purchaseEvent = (object) [];

            $details = DB::table('game_event_details')->select(['key','value'])
                            ->where('event_unique_id', $event->unique_id)
                            ->get()
                            ->keyBy('key');

            $purchaseEvent->type = $event->type;
            $purchaseEvent->timestamp = $event->timestamp;
            $purchaseEvent->game_time = $this->milliToTime($event->timestamp);
            $purchaseEvent->item_id = $details['item_id']->value;
            
            $item = DB::table('ddragon_items')->select(['name','image_url'])
                    ->where('api_id', $details['item_id']->value)
                    ->get()
                    ->first();

            $purchaseEvent->item_name = $item->name;
            $purchaseEvent->image_url = $item->image_url;

            array_push($participant[$details['participant_id']->value - 1]->purchase_history, $purchaseEvent);

        }

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->where('type', 'skill_level_up')
                        ->get();

        foreach ($gameEvents as $event)
        {

            $skill = (object) [];

            $details = DB::table('game_event_details')->select(['key','value'])
                            ->where('event_unique_id', $event->unique_id)
                            ->get()
                            ->keyBy('key');

            $skill->timestamp = $event->timestamp;
            $skill->game_time = $this->milliToTime($event->timestamp);
            $skill->skill_slot = $details['skill_slot']->value;
            $skill->level_up_type = $details['level_up_type']->value;

            array_push($participant[$details['participant_id']->value-1]->skill_order, $skill);
        }

        return $participant;
    }

    public function fetchKillEvents($game_id)
    {
        $gameId = $game_id;

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->where('type', 'champion_kill')
                        ->get();

        $kills = [];

        foreach ($gameEvents as $event) 
        {  

            $killEvent = (object) [];

            $details = DB::table('game_event_details')->select(['key', 'value'])
                        ->where('event_unique_id', $event->unique_id)
                        ->get()
                        ->keyBy('key');

            $gamePlayers = DB::table('game_player_stats')->select(['participant_id', 'summoner_name'])
                            ->where('game_id', $gameId)
                            ->get()
                            ->keyBy('participant_id');

            $killEvent->unique_id = $event->unique_id;
            $killEvent->timestamp = $event->timestamp;
            $killEvent->game_time = $this->milliToTime($event->timestamp);
            $killEvent->position_x = $details['position_x']->value;
            $killEvent->position_y = $details['position_y']->value;

            $killer = (object) [];

            $killer->killer_player = $gamePlayers[$details['killer_id']->value]->summoner_name;
            $killer->killer_player_id =  $this->findPlayerApiId($killer->killer_player);

            $killTeam = $this->findPlayerTeam($killer->killer_player);
            $killer->killer_team = $killTeam->name;
            $killer->killer_team_full = DB::table('teams')->select(['name'])
                                                ->where('api_id', $killTeam->api_team_id)
                                                ->get()
                                                ->first()
                                                ->name;

            $killEvent->killer = $killer;

            $victim = (object) [];

            $victim->victim_player = $gamePlayers[$details['victim_id']->value]->summoner_name;
            $victim->victim_player_id = $this->findPlayerApiId($victim->victim_player);

            $vicTeam = $this->findPlayerTeam($victim->victim_player);
            $victim->victim_team = $vicTeam->name;
            $victim->victim_team_full = DB::table('teams')->select(['name'])
                                                ->where('api_id', $vicTeam->api_team_id)
                                                ->get()
                                                ->first()
                                                ->name;

            $killEvent->victim = $victim;

            $assists = DB::table('game_event_details')->select(['key', 'value'])
                        ->where('event_unique_id', $event->unique_id)
                        ->where('key', 'assisting_participant_ids')
                        ->get()
                        ->toArray();

            if($assists)
            {
                $assistingPlayers = [];

                foreach ($assists as $assist) 
                {
                    $player = (object) [];
                    $player->assisting_player = $gamePlayers[$assist->value]->summoner_name;
                    $player->assisting_player_id = $this->findPlayerApiId($player->assisting_player);
                    array_push($assistingPlayers, $player);
                }
                $killEvent->assisting_players = $assistingPlayers;
            }

            array_push($kills, $killEvent);

        }

        return $kills;
    }

    public function milliToTime($time) {
        $input = floor($time / 1000);
        $seconds = $input % 60;
        $input = floor($input / 60);
        $minutes = $input % 60;

        if($seconds < 10) $seconds = '0' .$seconds;

        return $minutes . ':' . $seconds;
    }

    public function findPlayerApiId($input) {
        $plArray = explode(' ', $input);

        return DB::table('players')->select(['api_id'])
                    ->where('name', $plArray[1])
                    ->get()
                    ->first()
                    ->api_id;
    }

    public function findPlayerTeam($input) {
        $plArray = explode(' ', $input);

        return DB::table('rosters')
                ->where('name', $plArray[0])
                ->get()
                ->first();
    }
}
