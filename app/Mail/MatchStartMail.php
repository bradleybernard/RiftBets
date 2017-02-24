<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MatchStartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $is_bet;
    public $teamOne;
    public $teamTwo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($p_name, $p_is_bet, $p_teamOne, $p_teamTwo)
    {
        $this->name = $p_name;
        $this->is_bet = $p_is_bet;
        $this->teamOne = $p_teamOne;
        $this->teamTwo = $p_teamTwo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        if($this->is_bet)
            $subject = $this->teamOne . ' vs ' . $this->teamTwo . ' is beginning soon.';
        else
            $subject = 'Betting for ' . $this->teamOne . ' vs ' . $this->teamTwo .' closes in 5 minutes!';

        return $this->subject($subject)
                    ->markdown('emails.match-start'); 
    }
}
