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
        Schema::create('business_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('branch_address');
            $table->string('address');
            $table->string('building_no');
            $table->string('town_city');
            $table->string('postal_code');
            $table->string('country');
            $table->string('website')->nullable();
            $table->string('branch_contact');
            $table->string('mobile');
            $table->string('telefone');
            $table->string('fax')->nullable();
            $table->string('email');
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
        Schema::dropIfExists('business_branches');
    }
};
