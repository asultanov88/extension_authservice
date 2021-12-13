<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientAuth;
use App\Models\ClientServer;

class GetConfigController extends Controller
{
    public function getConfig(Request $request){

        try {
            $clientAuth = ClientAuth::where('AuthKey', '=', $request['RegistrationKey'])->first();

        if($clientAuth){

            $client = Client::where('id', '=', $clientAuth['ClientId'])->first();

            $clientServer = ClientServer::where('ClientId', '=', $client['id'])->first();

            $config = [

                'client' => $client['EntityName'],
                'isAdmin' => $clientAuth['isAdmin'],
                'repositoryServer' => $clientServer['RepositoryServer'],
                'registratonKey' => $clientAuth['AuthKey'],
                'token' => $this->generateRegToken($clientAuth['AuthKey']),

            ];

            return response()->
            json($config, 200);

        }else{
            return response()->
            json(['error' => 'Requested user not found'], 500);
        }

    } catch (Exception $e) {
            return response()->
            json($e, 500);
        }

    }

    /**
     * The salt is in .env file as REG_KEY_CRYPT_SALT.
     */
    private function generateRegToken($regKey){

        return crypt($regKey, '$5$rounds=5000'.env('REG_KEY_CRYPT_SALT'));

    }
}
