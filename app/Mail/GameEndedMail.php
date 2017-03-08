<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GameEndedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $teamOne;
    public $teamTwo;
    public $gameName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($p_name, $p_teamOne, $p_teamTwo, $p_gameName)
    {
        $this->name = $p_name;
        $this->teamOne = $p_teamOne;
        $this->teamTwo = $p_teamTwo;
        $this->gameName = substr($p_gameName, 1);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Game ' . $this->gameName . ' of ' .$this->teamOne. ' vs ' .$this->teamTwo. ' has ended. Betting for the next game begins soon!')
                    ->markdown('emails.game-ended');
    }
}
