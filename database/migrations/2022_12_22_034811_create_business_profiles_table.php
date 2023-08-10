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
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('business_category_id');
            $table->integer('business_country_id')->nullable();
            $table->integer('business_city_id')->nullable();
            $table->integer('account_type_id')->nullable();
            $table->integer('taxation_country_id')->nullable();
            $table->string('serial_number')->unique();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->integer('business_age')->nullable();
            $table->integer('time_response')->nullable();
            $table->string('pricing')->nullable();
            $table->text('bio')->nullable();
            $table->string('image')->nullable();
            $table->string('business_tax_number')->nullable();
            $table->tinyInteger('is_active')->default(1)->comment('1 for active, 0 for delete, 2 for deActive');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            // $table->foreign('business_category_id')->references('id')->on('business_categories')->onDelete('restrict');
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
        Schema::dropIfExists('business_profiles');
    }
};
