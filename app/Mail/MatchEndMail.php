<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MatchEndMail extends Mailable
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
            $subject = 'Check your bet results for ' . $this->teamOne . ' vs ' . $this->teamTwo;
        else
            $subject = $this->teamOne . ' vs ' . $this->teamTwo . ' has ended. Check the game stats!';

        return $this->subject($subject)
                    ->markdown('emails.match-end');
    }
}
