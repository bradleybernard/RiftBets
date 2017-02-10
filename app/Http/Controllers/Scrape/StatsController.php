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
                'api_id' => $champ->max('api_id'),
                'champion_name' => $champ->max('champion_name'),
                'role' => $champ->max('role'),
                'ban_rate' => $champ->max('ban_rate'),
                'play_rate' => $champ->max('play_rate'),
                'win_rate' => $champ->max('win_rate'),
                'overall_rank' => $champ->max('overall_rank'),
                'created_at' => $champ->max('created_at'), 
                // This is the actual math
                // The formula is completly made up, but seems quite reasonable
                'ban_scale' => (20/$champ->max('ban_rate') < 10) ? 20/$champ->max('ban_rate') : 10,   
                'pick_scale' => (20/$champ->max('play_rate') * (100-$champ->max('win_rate')) / 30 < 10) ? (20/$champ->max('play_rate') * (100-$champ->max('win_rate')) / 30) : 10,   		
        	]);
        }
        
        DB::table('math_stats')->insert($champions->toArray());

	}
}

