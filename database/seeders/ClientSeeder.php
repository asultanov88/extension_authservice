<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Models\ClientServer;
use App\Models\ClientAuth;
use App\Models\ClientJiraController;
use App\Models\ClientUserProfile;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this -> seedClient();
    }

    private function seedClient(){

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Tables that reference the 'client' table must be truncated first.
        ClientAuth::truncate();
        ClientServer::truncate();
        ContactPerson::truncate();
        ClientJiraController::truncate();
        Client::truncate();
        ClientUserProfile::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $clientId = 1;

        $client = [

            'id' => $clientId,
            'uuid' => '70ca7b25-24b4-40a5-8320-a9e105f65fb3',
            'EntityName' => 'My Test Company',
            'Email' => 'test_email@email.com',
            'PhoneNumber' => '+15617777777',
            'Address1' => '123 Awesome St, Apt 111',
            'Address2' => null,
            'County' => 'Palm BEach County',
            'State' => 'FL',
            'Zip' => '33408',
            'Country' => 'United Stated of America',
            'JiraUser' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];


        $contactPerson = [

            'ClientId' => $clientId,
            'FirstName' => 'John',
            'LastName' => 'Wayne',
            'Email' => 'john_wayne@email.com',
            'PhoneNumber' => '+15618888888',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        $clientServer = [

            'ClientId' => $clientId,
            'RepositoryServer' => 'https://extension-service.evendor.app/api',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        $clientAuth = [

            'ClientId' => $clientId,
            'AuthKey' => '61b589b5f03c42.30439098',
            'ExpirationDate' => '2022-12-12',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        $clientJiraController = [
            'ClientId' => $clientId,
            'ClientJiraControllerId' => 1,
            'JiraDomain' => 'eyJpdiI6IkwwbktZOWtpb2IrSE53UHk2UGloQ0E9PSIsInZhbHVlIjoiVGdHMTBDRGpoQnlJeCtyZm9RUU9SSTVGSGRLWE5DTlpybFV2RzhrNkRXWT0iLCJtYWMiOiI2MDJiNWQ4MTE3ZmE1ZmVjOGQ4ZDQyZmQ5ZWViZDk3ZWJkYzg0MjkxMThhZWQzZDcwYTMwNDA3ZmYxMmZjOThiIiwidGFnIjoiIn0=',
            'JiraUserName' => 'eyJpdiI6IitEVGFhZU5UTW90cGNNNUxnd2t3MUE9PSIsInZhbHVlIjoiOWlCTXY3bGI1RmRBUWZ3NVpFaGJ6UlUweFRJRWVlckZ6cXdGSUFwZXd2UT0iLCJtYWMiOiJhYTk4N2NjZjFiZjc3NWNiNTJlYjA4N2RhOWZlYzJkZGEyNjAwZjEwMGYzZDJkYmY5MjM4MjIzYmQwYzZiZDczIiwidGFnIjoiIn0=',
            'JiraApiKey' => 'eyJpdiI6IkFjREVyclRETFduSWlhV0psUUtoK2c9PSIsInZhbHVlIjoidE1GU1JFKzE5RVZqTDBMQXV5VjhLUmlwR0Irb1VNMUg3M09Tb1hSdldnOD0iLCJtYWMiOiI3NGI0MTgzM2NjMDE4ZTdiY2RhNjhkMGQ3YmRkYzhkYjQ0NGIzYzcwOThmMTQ1MTI1ZjM0NzYyNmU4MDBkZGI1IiwidGFnIjoiIn0=',
            'JiraIssueType' => '10004',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $clientUserProfile = [
            [
                'UserProfileId' => 1,
                'ClientId' => 1,
                'UserEmail' => 'uzsultanov@gmail.com',
                'UserAppId' => 'a3a6120c-e3aa-4185-a664-53a1567b99e4',
                'IsAdmin' => 1,
                'UserConfirmationId' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'UserProfileId' => 2,
                'ClientId' => 1,
                'UserEmail' => 'bahti005@gmail.com',
                'UserAppId' => 'a3a6120c-e3aa-4185-a664-53a1567b99e4',
                'IsAdmin' => 0,
                'UserConfirmationId' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],            
        ];

        Client::insert($client);
        ContactPerson::insert($contactPerson);
        ClientServer::insert($clientServer);
        ClientAuth::insert($clientAuth);
        ClientJiraController::insert($clientJiraController);
        ClientUserProfile::insert($clientUserProfile);
    }
}
