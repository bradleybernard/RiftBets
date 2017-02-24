<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DB;
use Mail;
use App\Mail\MatchStartMail;
use App\Mail\MatchEndMail;
use \Carbon\Carbon;

class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subscriptions = DB::table('subscriptions')
                                ->where('is_active', true)
                                ->get();

        foreach($subscriptions as $sub)
        {
            $user = DB::table('users')
                                ->where('id', $sub->user_id)
                                ->get()
                                ->first();

            $scheduledBlock = DB::table('schedule')
                                ->where('api_match_id', $sub->api_match_id)
                                ->get()
                                ->first();

            $match = DB::table('matches')
                                ->where('api_id_long', $sub->api_match_id)
                                ->get()
                                ->first();

            $teamOne = DB::table('rosters')
                                ->where('api_id_long', $match->api_resource_id_one)
                                ->get()
                                ->first();

            $teamTwo = DB::table('rosters')
                                ->where('api_id_long', $match->api_resource_id_two)
                                ->get()
                                ->first();

            if(!$sub->sent_start) // Has not sent 'Match Starting' mail
            {
                $gameStart = Carbon::parse($scheduledBlock->scheduled_time);

                if($gameStart->diffInMinutes(null, false) > -5 )
                {
                    Mail::to($user->email)
                      ->queue(new MatchStartMail($user->name, $sub->is_bet, $teamOne->name, $teamTwo->name));

                    DB::table('subscriptions')
                        ->where('id', $sub->id)
                        ->update(['sent_start'  => true]);
                }
            }
            elseif(!$sub->sent_end) //Already sent 'Match Starting' mail and "Match Ended" not sent
            {
                if($match->state == 'resolved') 
                {
                    Mail::to($user)
                      ->queue(new MatchEndMail($user->name, $sub->is_bet, $teamOne->name, $teamTwo->name));

                    DB::table('subscriptions')
                            ->where('id', $sub->id)
                            ->update([
                                'sent_end'  => true,
                                'is_active' => false]);
                }
            }
        }
    }
}
