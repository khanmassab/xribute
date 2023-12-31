<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('messages', function(Blueprint $table){
            $table->increments('id');
            $table->integer('chat_room_id')->references('id')->on('chat_rooms');
            $table->integer('sender_id')->references('id')->on('users');
            $table->bigInteger('product_id')->nullable();
            $table->text('message');
            $table->bigInteger('type')->default('0')->comment('0 for message, 1 for offers, 2 for quotations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('messages');
    }
}
