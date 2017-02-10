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
		$collection = collect();

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

            foreach($response->data as $item) {
                $collection->push([
                	'api_id'			=> DB::table('ddragon_champions')->where('champion_id', $item->key)->select('api_id')->first()->api_id,
                    'champion_name'     => $item->key,
                    'role'				=> $item->role,
                    'ban_rate' 			=> $item->general->banRate,
                    'play_rate'      	=> $item->general->playPercent,
                    'win_rate'       	=> $item->general->winPercent,
                    'overall_rank' 		=> $item->general->overallPosition,
                    'created_at'        => \Carbon\Carbon::now(),
                ]);
            }          

        }

        $collection = $collection->groupBy('api_id');
        $champions = collect([]);

        foreach($collection as $champ) {
        	$champions->push([
        		'ban_rate' => $champ->max('ban_rate'),
        		
        	]);
        	dd($champ->max('ban_rate'));
        }

        $collection = $collection->max('api_id');
        dd($collection);


        DB::table('math_stats')->insert($colection->toArray());

	}
}

