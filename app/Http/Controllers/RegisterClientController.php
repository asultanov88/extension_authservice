<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Models\ClientServer;
use App\Models\ClientAuth;
use App\Models\ClientJiraController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Models\ClientUserProfile;

class RegisterClientController extends Controller
{
    /**
     * Adds new user.
     */
    public function addUserProfile(){

    }

    /**
     * Regosters new client.
     */
    public function registerClient(Request $request){

        $request->validate([
            'EntityName' => 'required|max:50',
            'Email' => 'required|email:rfc,dns|unique:client|max:100',
            'PhoneNumber' => 'required|unique:client|max:50',
            'Address1' => 'required|max:255',
            'Address2' => 'max:255',
            'County' => 'required|max:50',
            'State' => 'required|max:50',
            'Zip' => 'required|max:50',
            'Country' => 'required|max:50',
            'ExpirationInYears' => 'required|numeric|max:3',
            // Defines if user uses the common respository or private.
            'CommonUser' => 'required|boolean',
            // Defines if user has Jira integration.
            'JiraUser' => 'required|boolean',

            // Entity contact person valdation. Nested object name: contact_person
            'contact_person.FirstName' => 'required|max:50',
            'contact_person.LastName' => 'required|max:50',
            'contact_person.Email' => 'required|email:rfc,dns|max:100',
            'contact_person.PhoneNumber' => 'required|max:50',

            // At least 1 superuser must be added.
            'superusers' => 'required|array|min:1',
            'superusers.*' => 'required|email:rfc,dns|max:100',
        ]);

        if($request->has('JiraUser') && $request['JiraUser'] == true){
            $request->validate([
                'ClientJiraControllerId' => 'required|unique:client_jira_controllers',
                'JiraDomain' => 'required|unique:client_jira_controllers',
                'JiraUserName' => 'required',
                'JiraApiKey' => 'required',
                'JiraIssueType' => 'required',
            ]);
        }

        try {
            if($request['CommonUser'] == true && strlen($request['RepositoryServer']) > 0){

                return response()->
                json(['error' => 'The RepositoryServer cannot contain value when CommonUser is true.'], 500);

            }else if($request['CommonUser'] == false && strlen($request['RepositoryServer']) < 1){

                return response()->
                json(['error' => 'Either CommonUser should be true or RepositoryServer should contain value.'], 500);

            }

            // Saving into client table.
            $client = new Client();
            $client['uuid'] = Str::uuid()->toString(); 
            $client['EntityName'] = $request['EntityName'];
            $client['Email'] = $request['Email'];
            $client['PhoneNumber'] = $request['PhoneNumber'];
            $client['Address1'] = $request['Address1'];
            $client['Address2'] = $request['Address2'] ? $request['Address2'] : null;
            $client['County'] = $request['County'];
            $client['State'] = $request['State'];
            $client['Zip'] = $request['Zip'];
            $client['Country'] = $request['Country'];
            $client['JiraUser'] = $request['JiraUser'] == true ? 1 : 0;
            // This is used to link other table records with the client.
            $client->save();
            $clientId = $client['id'];

            // Saving into contact_person table.
            $contactPerson = new ContactPerson();
            $contactPerson['ClientId'] = $clientId;
            $contactPerson['FirstName'] = $request['contact_person']['FirstName'];
            $contactPerson['LastName'] = $request['contact_person']['LastName'];
            $contactPerson['Email'] = $request['contact_person']['Email'];
            $contactPerson['PhoneNumber'] = $request['contact_person']['PhoneNumber'];
            $contactPerson->save();

            // Saving into client_server table.
            $clientServer = new ClientServer();
            $clientServer['ClientId'] = $clientId;
            $clientServer['RepositoryServer'] = $request['RepositoryServer']
                                                ? $request['RepositoryServer']
                                                : env('DEFAULT_BUG_REPO', 'N/A');
            $clientServer->save();

            // Saving into client_authkey table.
            $clientAuthSup = new ClientAuth();
            $clientAuthSup['ClientId'] = $clientId;
            $clientAuthSup['AuthKey'] = $this->generateRegKey('sup_');
            $clientAuthSup['isAdmin'] = 1;
            $clientAuthSup['ExpirationDate'] = Carbon::now()->addYears($request['ExpirationInYears']);
            $clientAuthSup->save();

            $clientAuthReg = new ClientAuth();
            $clientAuthReg['ClientId'] = $clientId;
            $clientAuthReg['AuthKey'] = $this->generateRegKey('reg_');
            $clientAuthReg['isAdmin'] = 0;
            $clientAuthReg['ExpirationDate'] = Carbon::now()->addYears($request['ExpirationInYears']);
            $clientAuthReg->save();

            // Saving Jira settings if JiraUser.
            if($request['JiraUser']){
                $clientJiraController = new ClientJiraController();
                $clientJiraController['ClientId'] = $clientId;
                $clientJiraController['ClientJiraControllerId'] = $request['ClientJiraControllerId'];
                $clientJiraController['JiraDomain'] = Crypt::encryptString($request['JiraDomain']);                
                $clientJiraController['JiraUserName'] = Crypt::encryptString($request['JiraUserName']);                
                $clientJiraController['JiraApiKey'] = Crypt::encryptString($request['JiraApiKey']);                
                $clientJiraController['JiraIssueType'] = $request['JiraIssueType'];   
                $clientJiraController->save();             
            }

            // Saving superusers.
            foreach ($request['superusers'] as $superuser) {
                $clientUserProfile = new ClientUserProfile();
                $clientUserProfile['ClientId'] = $clientId;
                $clientUserProfile['UserEmail'] = $superuser;
                $clientUserProfile['UserAppId'] = null;
                $clientUserProfile['IsAdmin'] = 1;
                $clientUserProfile['UserConfirmationId'] = null;
                $clientUserProfile->save();
            }

            return $this->getLastClientInfo($clientId);

        } catch (Exception $e) {
            return response()->
            json($e, 500);
        }

    }

    /**
     * Generates a unique registration key for each client.
     */
    private function generateRegKey($prefix){

        $registrationKey = null;

        do {

            $registrationKey = uniqid($prefix, true);

        } while (ClientAuth::where('AuthKey', '=', $registrationKey)->first());

        return $registrationKey;

    }

    /**
     * Gets the last registered client information.
     */
    private function getLastClientInfo($clientId){

        $clientInfo = Client::where('id', '=', $clientId)->first();
        $registrationInfo_reg = ClientAuth::where('ClientId', '=', $clientId)->where('isAdmin', '=', 0)->first();
        $registrationInfo_sup = ClientAuth::where('ClientId', '=', $clientId)->where('isAdmin', '=', 1)->first();

        $lastClientInfo = [

            'EntityName' => $clientInfo['EntityName'],
            'Email' => $clientInfo['Email'],
            'ClientId' => $clientInfo['id'],
            'JiraUser' => $clientInfo['JiraUser'] == 1 ? true : false,
            'RegistrationKey_sup' => $registrationInfo_sup['AuthKey'],
            'RegistrationKey_reg' => $registrationInfo_reg['AuthKey'],
            'ExpirationDate' => $registrationInfo_reg['ExpirationDate']

        ];

        return $lastClientInfo;
    }
}
