<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->softDeletes();
            $table->increments('id');
            $table->string('message');
            $table->integer('user')->nullable();
            $table->enum('status', ['comment', 'message'])->default('message');
            $table->integer('comment_on')->nullable();
            $table->integer('group')->nullable();
            $table->integer('owner');
            $table->foreign('comment_on')->references('id')->on('messages')->onDelete('cascade');
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
        Schema::dropIfExists('messages');
    }
}
