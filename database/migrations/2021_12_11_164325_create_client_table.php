<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client', function (Blueprint $table) {
            $table->id();
            $table->string('EntityName');
            $table->string('Email')->unique();
            $table->string('PhoneNumber')->unique();
            $table->string('Address1');
            $table->string('Address2')->nullable();
            $table->string('County');
            $table->string('State');
            $table->string('Zip');
            $table->string('Country');
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
        Schema::dropIfExists('client');
    }
}
