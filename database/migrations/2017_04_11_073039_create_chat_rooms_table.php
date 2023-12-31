<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRoomsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('chat_rooms', function(Blueprint $table){
            $table->increments('id');
            $table->enum('room_type', ['private', 'group', 'public']);
            $table->string('user_ids')->nullable();
            $table->bigInteger('type')->default('0')->comment('0 by default, 1 for message, 2 for quotations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('chat_rooms');
    }
}
