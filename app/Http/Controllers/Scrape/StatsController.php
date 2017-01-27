<?php

namespace App\Http\Controllers\Scrape;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatsController extends ScrapeController
{
    //
    protected $baseUri = 'http://api.champion.gg/';
    //http://api.champion.gg/stats?api_key=48c12f3fdac72fa934dd51edaf28d46c
    
    protected function get_data($url) {
        $ch = curl_init();
        $timeout = 5000;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:' => 70000000000000]);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    protected function rest_helper($url, $params = null, $verb = 'GET', $format = 'json')
    {
      $cparams = array(
        'http' => array(
          'method' => $verb,
          'ignore_errors' => true
        )
      );
      if ($params !== null) {
        $params = http_build_query($params);
        if ($verb == 'POST') {
          $cparams['http']['content'] = $params;
        } else {
          $url .= '?' . $params;
        }
      }
      
      $context = stream_context_create($cparams);
      $fp = fopen($url, 'rb', false, $context);
      if (!$fp) {
        $res = false;
      } else {
        // If you're trying to troubleshoot problems, try uncommenting the
        // next two lines; it will show you the HTTP response headers across
        // all the redirects:
        $meta = stream_get_meta_data($fp);
        var_dump($meta['wrapper_data']);
        $res = stream_get_contents($fp);
      }

      if ($res === false) {
        throw new \Exception("$verb $url failed: $php_errormsg");
      }

      switch ($format) {
        case 'json':
          $r = json_decode($res);
          if ($r === null) {
            throw new \Exception("failed to decode $res as json");
          }
          return $r;

        case 'xml':
          $r = simplexml_load_string($res);
          if ($r === null) {
            throw new \Exception("failed to decode $res as xml");
          }
          return $r;
      }
      return $res;
    }
    
    
    public function scrape() {
        
        
        try {
            $response = $this->client->request('GET', 'stats?api_key=48c12f3fdac72fa934dd51edaf28d46c');
        } catch (ClientException $e) {
            Log::error($e->getMessage()); return;
        } catch (ServerException $e) {
            Log::error($e->getMessage()); return;
        }

        $response = json_decode((string)$response->getBody());

        dd($response);


    }

}

