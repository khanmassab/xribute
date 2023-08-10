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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('type')->comment('1 for offers, 2 for service, 3 for assets');
            $table->tinyInteger('is_active')->default(1)->comment('0 => in-active, 1 => active');
            $table->timestamps();
        });
//        DB::table('categories')->insert(
//            array(
//                ['name' => 'Hotel Rooms', 'type' => 1],
//                ['name' => 'Residential Hotel', 'type' => 1],
//                ['name' => 'Accommodation', 'type' => 1],
//                ['name' => 'Parties', 'type' => 2],
//                ['name' => 'Events', 'type' => 2],
//                ['name' => 'Catering', 'type' => 2],
//                ['name' => 'Vans', 'type' => 3],
//                ['name' => 'BBQ', 'type' => 3],
//                ['name' => 'Sweats', 'type' => 3],
//            )
//        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
