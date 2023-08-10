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
        Schema::create('user_gender', function (Blueprint $table) {
            $table->id();
            $table->string('gender');
            $table->timestamps();
        });
        DB::table('user_gender')->insert(
            array(
                ['gender' => 'Male'],
                ['gender' => 'Female'],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_genders');
    }
};
