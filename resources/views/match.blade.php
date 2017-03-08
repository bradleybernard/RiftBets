@extends('layouts.app')

@section('content')
<div class="container">
    <game-display 
        match="{{ $match->api_id_long }}" 
        v-bind:best-of="{{ $match->match_best_of }}"
    ></game-display>
</div>
@endsection
