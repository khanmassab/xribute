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
        Schema::create('business_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_id');
            $table->integer('business_category_id');
            $table->string('name');
            $table->string('mrp')->nullable();
            $table->string('srp')->nullable();
            $table->string('details')->nullable();
            $table->string('image')->nullable();
            $table->string('minimum_order_limit')->nullable();
            $table->string('unit')->nullable();
            $table->tinyInteger('type')->comment('1 for offers, 2 for services, 3 for assets');
            $table->tinyInteger('is_active')->default(1)->comment('1 for active, 2 for deActive, 3 for delete');
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
        Schema::dropIfExists('business_products');
    }
};
