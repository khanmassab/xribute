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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('business_logo')->nullable();
            $table->string('business_registration_proof');
            $table->string('business_registration_no');
            $table->string('business_name');
            $table->string('business_country');
            $table->string('business_legal_type');
            $table->date('business_registration_date');
            $table->string('business_registered_address');
            $table->string('business_vat_no')->nullable();
            $table->string('business_registration_city');
            $table->string('business_tax_authority');

            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('businesses');
    }
};
