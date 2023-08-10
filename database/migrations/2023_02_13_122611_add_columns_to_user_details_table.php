<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            // $table->string('prefix_id')->nullable();
            // $table->string('language_id')->nullable();
            // $table->string('gender_id')->nullable();
            // $table->string('place_of_birth_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('user_details', function (Blueprint $table) {
        //     $table->dropColumn(['prefix_id', 'language_id', 'gender_id', 'place_of_birth_id']);
        // });
    }
}
