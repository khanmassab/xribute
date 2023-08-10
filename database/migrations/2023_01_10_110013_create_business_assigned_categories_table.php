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
        Schema::create('business_assigned_categories', function (Blueprint $table) {
            $table->bigInteger('business_id');
            $table->bigInteger('category_id');
            $table->tinyInteger('is_active')->default(1)->comment('0 => in-active , 1 => active');
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
        Schema::dropIfExists('business_assigned_categories');
    }
};
