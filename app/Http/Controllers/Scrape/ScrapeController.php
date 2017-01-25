<?php

namespace App\Http\Controllers\Scrape;

use App\Http\Controllers\Controller;

use \GuzzleHttp\Client;
use DB;

class ScrapeController extends Controller
{
    const WORLDS_2016_LEAGUE = 9;
    const NA_LCS_LEAGUE = 2;
    const NA_LCS_SPRING_2017 = 'na_2017_spring';
    const NA_LCS_SPRING_2017_ID = 'ce8e57ab-9804-496d-941a-9d04558f7bb4';

    protected $client   = null;
    protected $baseUri  = 'http://api.lolesports.com/api/';
    protected $options  = [];
    
    protected $tables   = [];

    public function __construct()
    {
        $options = array_merge(['base_uri'  => $this->baseUri], $this->options);
        $this->client = new Client($options);
    }

    protected function insertUnique($table, $keys, $rows)
    {
        $rows = collect($rows); 

        if($rows->count() < 1) {
            throw new Exception('Bad data given to insertUnique($table, $key, $rows)');
        }

        $filter = collect([]);

        foreach($rows as $row) {
            foreach($keys as $key) {
                if(property_exists($row, $key)) {
                    $filter->push([
                        'key'   => $key,
                        'value' => $row->{$key}
                    ]);
                }
            }
        }

        $filter->groupBy('key');
    
        $match = DB::table($table)->select($keys);

        foreach($keys as $key) {
            $match = $match->whereIn($key, $filter->get($key));
        }

        $new = $rows->reject(function ($value, $index) use ($match) {
            return in_array($value[$key], $match);
        });

        DB::table($table)->insert($new->toArray());
    }

    protected function reset()
    {
        foreach($this->tables as $table) {
            DB::table($table)->truncate();
        }
    }

    protected function clean($input)
    {
        $input = trim($input);
        
        if($input == "" || $input == "--") {
            return null;
        }

        return $input;
    }

    protected function pryArr($array, $key, $attr) 
    {
        if(isset($array[$key])) {
            if(isset($array[$key]->$attr)) {
                return $array[$key]->$attr;
            }
        }

        return null;
    }

    protected function pry($object, $path)
    {
        $parts = explode('->', $path);

        foreach($parts as $part) {
            if(property_exists($object, $part)) {
                $object = $object->{$part};
            } else {
                return null;
            }
        }

        return (is_string($object) ? $this->clean($object) : $object);
    }

    protected function pluckResource($object)
    {
        $keys = ['roster', 'breakpoint', 'match', 'bracket'];

        foreach($keys as $key) {
            if(property_exists($object, $key)) {
                return $object->{$key};
            }
        }

        return null;
    }

    protected function pluckResourceType($object)
    {
        $keys = ['roster', 'breakpoint', 'match', 'bracket'];

        foreach($keys as $key) {
            if(property_exists($object, $key)) {
                return $key;
            }
        }

        return null;
    }
}
