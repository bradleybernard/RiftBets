@component('mail::message')

<div style="text-align:center; font-weight:bold;">
{{$teamOne}} vs {{$teamTwo}}  is beginning soon.
</div>
<br>

@if($is_bet)
	Check your bets and watch the game at Riftbets!
@else
	It's not too late to bet! Bet on {{$teamOne}} vs {{$teamTwo}} and watch the game at Riftbets!
@endif

@component('mail::button', ['url' => ''])
Go to RiftBets
@endcomponent

@endcomponent
