@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Testing matches -->
    <game-nav match="{{$matchId}}"></game-nav>

	<iframe 
	    src="http://player.twitch.tv/?channel=eulcs1" 
	    height="720" 
	    width="1280" 
	    frameborder="0" 
	    scrolling="no"
	    allowfullscreen="true"
	    style="max-width: 80em;">
	</iframe>
</div>
@endsection