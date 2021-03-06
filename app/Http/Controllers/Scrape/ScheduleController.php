<?php

namespace App\Http\Controllers\Scrape;

use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Exception\ServerException;

use \Carbon\Carbon;
use DB;
use Log;

class ScheduleController extends ScrapeController
{
    protected $tables = ['schedule'];

    //gather and insert the scheduled matches for the league
    public function scrape()
    {        
        $leagues = DB::table('leagues')->pluck('api_id');

        foreach($leagues as $leagueId) {

            try {
                $response = $this->client->request('GET', 'v1/scheduleItems?leagueId=' . $leagueId);
            } catch (ClientException $e) {
                Log::error($e->getMessage()); continue;
            } catch (ServerException $e) {
                Log::error($e->getMessage()); continue;
            }

            $response = json_decode((string) $response->getBody());
            $schedules = [];

            foreach($response->scheduleItems as $item) {
                $schedules[] = [
                    'api_league_id'     => $leagueId,
                    'api_id_long'       => $item->id,
                    'api_tournament_id' => $item->tournament,
                    'api_match_id'      => $this->pry($item, 'match'),
                    'block_label'       => $this->pry($item, 'tags->blockLabel'),
                    'block_prefix'      => $this->pry($item, 'tags->blockPrefix'),
                    'sub_block_label'   => $this->pry($item, 'tags->subBlockLabel'),
                    'sub_block_prefix'  => $this->pry($item, 'tags->subBlockPrefix'),
                    'scheduled_time'    => new Carbon($item->scheduledTime),
                ];
            }

            DB::table('schedule')->insert($schedules);
        }
    }
}
