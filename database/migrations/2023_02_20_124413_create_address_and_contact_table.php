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
        Schema::create('address_and_contact', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('address_label');
            $table->string('address');
            $table->string('building_no');
            $table->string('town_city');
            $table->string('postal_code');
            $table->string('country');
            $table->string('website')->nullable();
            $table->string('contact_label');
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
        Schema::dropIfExists('addresses_and_contacts');
    }
};
