<?php

namespace App\Http\Controllers\Scrape;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StreamsController extends ScrapeController
{
    public function scrape()
    {
	    try {
    	    	$response = $this->client->request('GET', 'v2/streamgroups');
    	    } catch (ClientException $e) {
    		    Log::error($e->getMessage()); return;
    	    } catch (ServerException $e) {
    	        Log::error($e->getMessage()); return;
    	    }

	    $response = json_decode((string)$response->getBody());

	    $streamgroups = $response->streamgroups;
	    $streams = collect($response->streams)->keyBy('id');

	    foreach ($streamgroups as $group) {
	    	foreach ($group->streams as $streamId => $stream) {
	    		dd($streams[$stream]);
	    	}
	    }

	    dd($streamgroups);
    }
}
