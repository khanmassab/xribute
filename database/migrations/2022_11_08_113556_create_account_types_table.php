<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('account_type_name');
            $table->tinyInteger('is_active')->default(1)->comment('0 => in-active, 1 => active');
            $table->timestamps();
        });
        DB::table('account_types')->insert(
            array(
                ['account_type_name' => 'User Account'],
                ['account_type_name' => 'Business Account'],
                ['account_type_name' => 'Organization Account'],
                ['account_type_name' => 'Regulation Body / Authorities Account'],
                ['account_type_name' => 'XSAVY Employee Account'],
                ['account_type_name' => 'Service Provider Account'],
                ['account_type_name' => 'External Consultant Account'],
                ['account_type_name' => 'Employee Account'],
                ['account_type_name' => 'Mediators Account'],
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
        Schema::dropIfExists('account_types');
    }
};
