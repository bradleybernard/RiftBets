<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Dingo\Api\Routing\Helpers;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Helpers;

    protected function clean($input)
    {
        $input = trim($input);
        
        if($input == "" || $input == "--") {
            return null;
        }

        return $input;
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

    protected function pryArr($array, $key, $attr) 
    {
        if(isset($array[$key])) {
            if(isset($array[$key]->$attr)) {
                return $array[$key]->$attr;
            }
        }

        return null;
    }

    protected function pryObj($object, $arr, $key)
    {
        if(property_exists($object, $arr)) {
            if(isset($object->{$arr}[$key])) {
                return $object->$arr[$key];
            }
        }

        return null;
    }
}
