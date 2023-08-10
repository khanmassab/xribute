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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('date_format_id')->default(1);
            $table->integer('account_type_id');
            // $table->foreign('date_format_id')->references('id')->on('date_formats')->onDelete('restrict')->onUpdate('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('phone')->nullable();
            $table->string('user_name')->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_human');
            $table->boolean('is_agreed_to_terms');
            $table->boolean('is_active')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
