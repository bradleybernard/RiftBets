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

        $events = (object) [];

        $gamePlayers = DB::table('game_player_stats')->select(['participant_id', 'summoner_name'])
                            ->where('game_id', $gameId)
                            ->get()
                            ->keyBy('participant_id'); 
       
        $events->kills = $this->fetchKillEvents($gameId, $gamePlayers);

        $events->players = $this->fetchPlayerData($gameId, $gamePlayers);

        $events->building_kills = $this->fetchBuildingKillEvents($gameId, $gamePlayers);

        return $this->response->array((array)$events);
    }

    public function fetchBuildingKillEvents($game_id, $gamePlayers)
    {
        $gameId = $game_id;

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->where('type', 'building_kill')
                        ->get();

        $team1 = explode(' ',$gamePlayers[1]->summoner_name);
        $team1 = $team1[0];

        $team2 = explode(' ',$gamePlayers[6]->summoner_name);
        $team2 = $team2[0];

        $eventIds = [];

        foreach($gameEvents as $event)
        {
            array_push($eventIds, $event->unique_id);
        }

        $totalBuildingKills = [];

        $masterDetails = DB::table('game_event_details')->select(['event_unique_id','key','value'])
                            ->whereIn('event_unique_id', $eventIds)
                            ->get()
                            ->groupBy('event_unique_id');

        foreach ($gameEvents as $event) 
        {
            $details = $masterDetails[$event->unique_id]->keyBy('key');

            $building = (object) [];

            $building->timestamp = $event->timestamp;
            $building->game_time = $this->milliToTime($event->timestamp);
            $building->killer_name = ($details['killer_id']->value == 0 ? 'Minions' : $gamePlayers[$details['killer_id']->value]->summoner_name);
            $building->victim_team = ($details['team_id']->value == 100 ? $team1 : $team2);
            $building->building_type = $details['building_type']->value; 
            $building->lane_type = $details['lane_type']->value;
            $building->tower_type = $details['tower_type']->value;

            array_push($totalBuildingKills, $building);      
        }

        return $totalBuildingKills;
    }

    public function fetchPlayerData($game_id, $gamePlayers)
    {
        $gameId = $game_id;

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->whereIn('type', ['item_purchased','item_sold'])
                        ->get();

        $participant = [];

        for ($i=1; $i <= 10; $i++) 
        { 
            $player = (object) [];
            $player->participant_id = $i;
            $player->name = $gamePlayers[$i]->summoner_name;
            $player->purchase_history = [];
            $player->skill_order = [];
            array_push($participant, $player);
        }

        $eventIds = [];

        foreach($gameEvents as $event)
        {
            array_push($eventIds, $event->unique_id);
        }

        $masterDetails = DB::table('game_event_details')->select(['event_unique_id','key','value'])
                            ->whereIn('event_unique_id', $eventIds)
                            ->get()
                            ->groupBy('event_unique_id');

        $masterItems = DB::table('ddragon_items')->select(['api_id','name','image_url'])
                        ->get()
                        ->keyBy('api_id');

        foreach ($gameEvents as $event) 
        {
            $purchaseEvent = (object) [];

            $details = $masterDetails[$event->unique_id]->keyBy('key');

            $purchaseEvent->type = $event->type;
            $purchaseEvent->timestamp = $event->timestamp;
            $purchaseEvent->game_time = $this->milliToTime($event->timestamp);
            $purchaseEvent->item_id = $details['item_id']->value;
            
            $item = $masterItems[$details['item_id']->value];

            $purchaseEvent->item_name = $item->name;
            $purchaseEvent->image_url = $item->image_url;

            array_push($participant[$details['participant_id']->value - 1]->purchase_history, $purchaseEvent);

        }

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->where('type', 'skill_level_up')
                        ->get();

        $eventIds = [];

        foreach($gameEvents as $event)
        {
            array_push($eventIds, $event->unique_id);
        }

        $masterDetails = DB::table('game_event_details')->select(['event_unique_id','key','value'])
                            ->whereIn('event_unique_id', $eventIds)
                            ->get()
                            ->groupBy('event_unique_id');

        foreach ($gameEvents as $event)
        {

            $skill = (object) [];

            $details = $masterDetails[$event->unique_id]->keyBy('key');

            $skill->timestamp = $event->timestamp;
            $skill->game_time = $this->milliToTime($event->timestamp);
            $skill->skill_slot = $details['skill_slot']->value;
            $skill->level_up_type = $details['level_up_type']->value;

            array_push($participant[$details['participant_id']->value-1]->skill_order, $skill);
        }

        return $participant;
    }

    public function fetchKillEvents($game_id, $gamePlayers)
    {
        $gameId = $game_id;

        $gameEvents = DB::table('game_events')
                        ->where('game_id', $gameId)
                        ->where('type', 'champion_kill')
                        ->get();

        $kills = [];

        $eventIds = [];

        foreach($gameEvents as $event)
        {
            array_push($eventIds, $event->unique_id);
        }

        $masterDetails = DB::table('game_event_details')->select(['event_unique_id','key','value'])
                            ->whereIn('event_unique_id', $eventIds)
                            ->get()
                            ->groupBy('event_unique_id');

        foreach ($gameEvents as $event) 
        {  

            $killEvent = (object) [];

            $details = $masterDetails[$event->unique_id]->groupBy('key');

            $killEvent->unique_id = $event->unique_id;
            $killEvent->timestamp = $event->timestamp;
            $killEvent->game_time = $this->milliToTime($event->timestamp);

            $killEvent->killer = $gamePlayers[$details['killer_id'][0]->value]->summoner_name;

            $killEvent->victim = $gamePlayers[$details['victim_id'][0]->value]->summoner_name;

            if($details->has('assisting_participant_ids'))
            {
                $assistingPlayers = [];

                foreach ($details['assisting_participant_ids'] as $assist) 
                {
                    array_push($assistingPlayers, $gamePlayers[$assist->value]->summoner_name);
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
