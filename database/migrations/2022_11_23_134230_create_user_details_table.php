<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('prefix_id')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();

            $table->integer('country_id')->nullable();
            // $table->string('title')->nullable();
            // $table->string('gender')->nullable();
            $table->string('birth_name')->nullable();
            // $table->date('date_of_birth');
            // $table->string('place_of_birth');
            // $table->string('country');
            // $table->string('city');
            $table->timestamp('date_of_birth')->nullable();
            $table->unsignedBigInteger('place_of_birth_id')->nullable();
//            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->mediumText('profile_image')->nullable();
            $table->mediumText('cover_image')->nullable();
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
        Schema::dropIfExists('user_details');
    }
};
