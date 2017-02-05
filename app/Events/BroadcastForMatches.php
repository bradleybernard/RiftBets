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
        Log::info($game);
        $event = DB::table('matches')->select(['matches.name as teams_playing', 'matches.state as game_state', 'games.name', 'match_best_of', 'api_match_id', 'games.api_id_long as game_id'])
                ->join('games', 'games.api_match_id', '=', 'matches.api_id_long')
               ->join('brackets', 'brackets.api_id_long', '=', 'matches.api_bracket_id')
               ->where('matches.api_id_long', $game['api_match_id'])
               ->where('games.api_id_long', $game['api_game_id'])
               ->get();

        $this->game = [
            'gameNumber'    => $event[0]->name[1],
            'gameId'        => $game['api_game_id'],
            'matchId'       => $event[0]->api_match_id,
        ];

        Log::info('Game data dispatched');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return ['match.' . $this->game['matchId']];
    }

    public function broadcastAs()
    {
        return 'game.completed';
    }

    public function broadcastWith()
    {
        return ['gameId' => $this->game['gameId']];
    }
}