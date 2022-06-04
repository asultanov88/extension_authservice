<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientJiraControllersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_jira_controllers', function (Blueprint $table) {
            $table->foreignId('ClientId')->references('id')->on('client')->index();
            $table->integer('ClientJiraControllerId')->unique();
            $table->longtext('JiraDomain');
            $table->longtext('JiraUserName');
            $table->longtext('JiraApiKey');
            $table->string('JiraIssueType');
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
        Schema::dropIfExists('client_jira_controllers');
    }
}
