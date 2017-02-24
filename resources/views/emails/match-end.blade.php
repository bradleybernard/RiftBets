@component('mail::message')

<div style="text-align:center; font-weight:bold;">
{{$teamOne}} vs {{$teamTwo}} has ended.
</div>
<br>

@if($is_bet)
	Check the game recap and see which bets you have won!
@else
	Check the game recap at RiftBets!
@endif

@component('mail::button', ['url' => ''])
Go to RiftBets
@endcomponent

@endcomponent
