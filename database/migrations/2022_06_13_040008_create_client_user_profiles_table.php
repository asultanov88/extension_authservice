<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_user_profiles', function (Blueprint $table) {
            $table->id('UserProfileId');            
            $table->foreignId('ClientId')->references('id')->on('client');
            $table->longtext('UserEmail');
            $table->longtext('UserAppId')->nullable();
            $table->foreignId('UserConfirmationId')->nullable()
                                                   ->references('UserConfirmationId')
                                                   ->on('user_confirmations');
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
        Schema::dropIfExists('client_user_profiles');
    }
}
