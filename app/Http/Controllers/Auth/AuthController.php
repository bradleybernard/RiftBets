<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\UserStats;
use App\Leaderboard;

use JWTAuth;
use Redis;
use Socialite;
use Cookie;

class AuthController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleProviderCallback()
    {
        $facebook = Socialite::driver('facebook')->user();

        if(!$user = User::where('facebook_id', $facebook->id)->first()) {
            $user = User::create([
                'facebook_id'   => $facebook->id,
                'name'          => $this->pry($facebook, 'name'),
                'email'         => $this->pry($facebook, 'email'),
                'gender'        => $this->pryObj($facebook, 'user', 'gender'),
                'verified'      => $this->pryObj($facebook, 'user', 'verified'),
                'avatar_url'    => $this->pry($facebook, 'avatar'),
            ]);

            UserStats::create([
                'user_id'       => $user->id,
                'created_at'    => \Carbon\Carbon::now(),
                'updated_at'    => \Carbon\Carbon::now(),
            ]);

            $redis = Redis::connection();
            $leaderboards = Leaderboard::all();

            foreach($leaderboards as $leaderboard) {
                $redis->ZADD($leaderboard['redis_key'], 0, $user->id);
            }
        }

        $token = JWTAuth::fromUser($user);
        Cookie::queue('jwt', $token, 60 * 24 * 365);

        return $this->response->array([
            'token' => $token,
            'user'  => $user,
        ]);
    }
}
