<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use \Tymon\JWTAuth\Token;
use App\User;
use Auth;

class LoginIfJwtInCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->ajax()) {
            $this->doAjax($request, $next);
        } else {
            $this->doNormal($request, $next);
        }

        return $next($request);
    }

    public function doAjax($request, Closure $next) 
    {
        if($cookie = $request->cookie('jwt')) {

            try {
                $cookie = decrypt($cookie);
            } catch (\DecryptException $e) { }

            $request->headers->set('Authorization', 'Bearer: ' . $cookie);
        }
    }

    public function doNormal($request, Closure $next)
    {
        if(!Auth::check() && $cookie = $request->cookie('jwt')) {

            $token = new Token($cookie);
            $jwt = JWTAuth::decode($token);
            $user = User::find($jwt->get('sub'));

            if($user) {
                Auth::login($user);
            }
        }
    }
}
