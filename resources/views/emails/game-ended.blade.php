@component('mail::message')

<div style="text-align:center; font-weight:bold;">
Game {{$gameName}} of {{$teamOne}} vs {{$teamTwo}} has ended. <br>
Game {{$gameName + 1}} is beginning soon!
</div>
<br>

Betting for Game  is coming soon!

Check last game stats or bet on Game {{$gameName + 1}} at Riftbets!

@component('mail::button', ['url' => 'http://riftbets.dev'])
Go to RiftBets
@endcomponent

@endcomponent
