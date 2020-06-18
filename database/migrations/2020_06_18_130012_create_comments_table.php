<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->softDeletes();
            $table->increments('id');
            $table->string('message');
            $table->string('comment');
            $table->integer('group')->nullable();
            $table->integer('owner');
            $table->integer('user')->nullable();
            $table->foreign('message')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('owner')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
