@extends('layouts.app')

@section('content')
<div class="container">
    <game-nav match="{{ $matchId }}"></game-nav>
    <game-display></game-display>
</div>
@endsection
