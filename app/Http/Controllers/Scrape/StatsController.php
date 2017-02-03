<?php

namespace App\Http\Controllers\Scrape;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use \Carbon\Carbon;
use DB;

class StatsController extends ScrapeController
{
	protected $baseUri = 'http://api.champion.gg/';
	protected $apiKey = '48c12f3fdac72fa934dd51edaf28d46c';
    
	public function scrape() {

		DB::table('math_stats')->truncate();

		for ($i = 1; ; ++$i) {

			try {
				$response = $this->client->request('GET', 'stats/champs/mostBanned?api_key=' . $this->apiKey . '&page=' . $i . '&limit=100');
			} catch (ClientException $e) {
				Log::error($e->getMessage()); return;
			} catch (ServerException $e) {
				Log::error($e->getMessage()); return;
			}

			$response = json_decode((string)$response->getBody());

			if (count($response->data) == 0)
				break;

			$champs = [];
            foreach($response->data as $item) {
                $champs[] = [
                    'champion_name'     => $item->key,
                    'role'				=> $item->role,
                    'ban_rate' 			=> $item->general->banRate,
                    'play_rate'      	=> $item->general->playPercent,
                    'win_rate'       	=> $item->general->winPercent,
                    'overall_rank' 		=> $item->general->overallPosition,
                    'created_at'        => \Carbon\Carbon::now(),
                ];
            }

            DB::table('math_stats')->insert($champs);

        }
	}
}

