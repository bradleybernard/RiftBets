<?php

namespace App\Events;

use DB;
use Log;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BroadcastForMatches implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($game)
    {
        $event = DB::table('matches')->select(['matches.name as teams_playing', 'matches.state as game_state', 'games.name', 'match_best_of', 'api_match_id'])->join('games', 'games.api_match_id', '=', 'matches.api_id_long')
               ->join('brackets', 'brackets.api_id_long', '=', 'matches.api_bracket_id')
               ->where('matches.api_id_long', $game->api_match_id)
               ->where('games.api_id_long', $game->api_game_id)
               ->get();

        if($event[0]->game_state == "resolved")
            $flag = true;
        else
            $flag = false;

        // $this->game = (object)[];
        $this->game = json_encode([
            'gameId'    => $event[0]->name[1],
            'matchId'   => $event[0]->api_match_id,
            'resolved'  => $flag
        ]);

        Log::info('Game data dispatched');

        // $this->game = (object)[];
        // $this->game->gameId = $event[0]->name[1];
        // $this->game->matchId = $event[0]->api_match_id;
        // $this->game->resolved = $flag;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $game = json_decode($this->game);
        return ['match.' . $game->matchId];
    }

    public function broadcastAs()
    {
        return 'game.completed';
    }
}