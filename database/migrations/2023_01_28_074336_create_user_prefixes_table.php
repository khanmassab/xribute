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
        Schema::create('user_prefixes', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->timestamps();
        });
        DB::table('user_prefixes')->insert(
            array(
                ['prefix' => 'Mr.'],
                ['prefix' => 'Miss'],
                ['prefix' => 'Mrs.'],
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
        Schema::dropIfExists('user_prefixes');
    }
};
