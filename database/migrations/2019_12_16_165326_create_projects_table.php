<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('branch');
            $table->string('path');
            $table->integer('status')->default(1);
            $table->mediumText('secret')->nullable();
            $table->string('pre_hook')->nullable();
            $table->string('post_hook')->nullable();
            $table->string('email_result')->nullable();
            $table->bigInteger('user_id');
            $table->integer('last_hook_status')->nullable();
            $table->dateTime('last_hook_time')->nullable();
            $table->integer('last_hook_duration')->nullable();
            $table->longText('last_hook_log')->nullable();
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
