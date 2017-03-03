<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streamgroups', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('api_id');
            $table->string('slug');

            $table->string('title');
            $table->string('region_priority');
            $table->string('ad_url');
            $table->string('ad_image_url');

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streamgroups');
    }
}
