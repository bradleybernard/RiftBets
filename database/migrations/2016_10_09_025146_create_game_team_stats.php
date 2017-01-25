<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameTeamStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_team_stats', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('team_id');
            $table->boolean('win');
            $table->boolean('first_blood');
            $table->boolean('first_tower');
            $table->boolean('first_inhibitor');
            $table->boolean('first_baron');
            $table->boolean('first_dragon');
            $table->boolean('first_rift_herald');
            $table->integer('tower_kills');
            $table->integer('inhibitor_kills');
            $table->integer('baron_kills');
            $table->integer('dragon_kills');
            $table->integer('vilemaw_kills');
            $table->integer('rift_herald_kills');
            $table->integer('dominion_victory_score');
            $table->integer('ban_1')->nullable();
            $table->integer('ban_1_pick')->nullable();
            $table->integer('ban_2')->nullable();
            $table->integer('ban_2_pick')->nullable();
            $table->integer('ban_3')->nullable();
            $table->integer('ban_3_pick')->nullable();
            $table->integer('ban_4')->nullable();
            $table->integer('ban_4_pick')->nullable();
            $table->integer('ban_5')->nullable();
            $table->integer('ban_5_pick')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_team_stats');
    }
}
