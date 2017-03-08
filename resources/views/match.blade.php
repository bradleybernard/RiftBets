@extends('layouts.app')

@section('content')
<div class="container">
    <game-display match="{{ $matchId }}"></game-display>
</div>
@endsection
