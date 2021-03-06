<?php

namespace App\Http\Controllers\Scrape;

use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Exception\ServerException;

use DB;
use Log;

class MatchDetailsController extends ScrapeController
{
    public function scrape()
    {
        $matches = DB::table('matches')->select(['matches.api_id_long as match_id', 'brackets.api_tournament_id'])
                    ->join('brackets', 'brackets.api_id_long', '=', 'matches.api_bracket_id')->get();

        //attempt to gather and insert data of each match
        foreach ($matches as $match) {

            $gameMappings = [];
            $gameVideos   = [];

        	try {
                $response = $this->client->request('GET', 'v2/highlanderMatchDetails?tournamentId='. $match->api_tournament_id .'&matchId=' . $match->match_id);
            } catch (ClientException $e) {
                Log::error($e->getMessage()); return;
            } catch (ServerException $e) {
                Log::error($e->getMessage()); return;
            }

            $response = json_decode((string)$response->getBody());

            //insert data into game-match relational table
            foreach ($response->gameIdMappings as $mapping) 
            {
                $gameMappings[] = [
                    'api_match_id'  => $match->match_id,
                    'api_game_id'   => $mapping->id,
                    'game_id'       => DB::table('games')->where('api_id_long', $mapping->id)->pluck('game_id')[0],
                    'game_hash'     => $mapping->gameHash
                ];
            }

            //video urls for each game
            foreach($response->videos as $video) {
                $gameVideos[] = [
                    'api_id'            => $video->id,
                    'api_game_id'       => $video->game,
                    'locale'            => $video->locale,
                    'source'            => $video->source,
                    'api_created_at'    => (new \Carbon\Carbon($video->createdAt)),
                    'created_at'        => \Carbon\Carbon::now(),
                ];
            }

            DB::table('game_videos')->insert($gameVideos);
            DB::table('game_mappings')->insert($gameMappings);
        }
    }
}
