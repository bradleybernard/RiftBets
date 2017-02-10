<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMathStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('math_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_id');
            $table->string('champion_name');
            $table->string('role');
            $table->decimal('ban_rate', 4, 2);
            $table->decimal('play_rate', 4, 2);
            $table->decimal('win_rate', 4, 2);
            $table->integer('overall_rank');
            $table->timestamp('created_at');
            $table->decimal('ban_scale', 4, 2);
            $table->decimal('pick_scale', 4, 2);      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('math_stats');
    }
}
