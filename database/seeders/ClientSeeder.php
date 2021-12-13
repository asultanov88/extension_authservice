<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Models\ClientServer;
use App\Models\ClientAuth;


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

        ClientAuth::truncate();
        ClientServer::truncate();
        ContactPerson::truncate();
        Client::truncate();

        $client = [

            'id' => 1,
            'EntityName' => 'My Test Company',
            'Email' => 'test_email@email.com',
            'PhoneNumber' => '+15617777777',
            'Address1' => '123 Awesome St, Apt 111',
            'Address2' => null,
            'County' => 'Palm BEach County',
            'State' => 'FL',
            'Zip' => '33408',
            'Country' => 'United Stated of America',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];


        $contactPerson = [

            'ClientId' => 1,
            'FirstName' => 'John',
            'LastName' => 'Wayne',
            'Email' => 'john_wayne@email.com',
            'PhoneNumber' => '+15618888888',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        $clientServer = [

            'ClientId' => 1,
            'RepositoryServer' => 'http://127.0.0.1:8000/api/',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        $clientAuth_sup = [

            'ClientId' => 1,
            'AuthKey' => 'sup_61b589b5f03c42.30439098',
            'isAdmin' => 1,
            'ExpirationDate' => '2022-12-12',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        $clientAuth_reg = [

            'ClientId' => 1,
            'AuthKey' => 'reg_61b589b5f160b1.70048695',
            'isAdmin' => 0,
            'ExpirationDate' => '2022-12-12',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];

        Client::insert($client);
        ContactPerson::insert($contactPerson);
        ClientServer::insert($clientServer);
        ClientAuth::insert($clientAuth_sup);
        ClientAuth::insert($clientAuth_reg);

    }
}
