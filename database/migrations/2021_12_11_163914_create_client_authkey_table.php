<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientAuthkeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_authkey', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ClientId')->references('id')->on('client');
            $table->string('AuthKey')->unique()->index();
            $table->date('ExpirationDate');
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
        Schema::dropIfExists('client_authkey');
    }
}
