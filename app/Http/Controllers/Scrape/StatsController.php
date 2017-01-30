<?php

namespace App\Http\Controllers\Scrape;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatsController extends ScrapeController
{
	protected $baseUri = 'http://api.champion.gg/';
	protected $apiKey = '48c12f3fdac72fa934dd51edaf28d46c';
	
	public function scrape() {

		try {
			$response = $this->client->request('GET', 'stats?api_key=' . $this->apiKey);
		} catch (ClientException $e) {
			Log::error($e->getMessage()); return;
		} catch (ServerException $e) {
			Log::error($e->getMessage()); return;
		}

		$response = json_decode((string)$response->getBody());
		dd($response);
	}
}

