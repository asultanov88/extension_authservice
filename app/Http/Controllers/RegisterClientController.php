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
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationConfirmation;

class RegisterClientController extends Controller
{
    /**
     * Gets a list of user profiles by search string.
     */
    public function getUserProfiles(Request $request){
        $request->validate([
            'query' => 'required|min:2|max:50'
        ]);

        try {

            $client = Client::where('id','=',$request['ClientId'])->first();
            $users = $client->users->where('UserEmail','like','%'.$request['query'].'%')->get();

            return response()->
            json(['result' => $users], 200);

        } catch (Exception $e) {
            return response()->
            json($e, 500);
        }
    }

    /**
     * Adds new user.
     */
    public function addUserProfile(Request $request){
        $request->validate([
            'NewUserEmail' => 'required|email:rfc,dns|max:100',
            'isAdmin' => 'required|integer|min:0|max:1',
            'AdminUser' => 'required|boolean'
        ]);

        try {
            
            if($request['AdminUser']){
                $client = Client::where('id','=',$request['ClientId'])->first();
                $existingUser = $client->users->where('UserEmail','=',$request['NewUserEmail'])->first();

                if($existingUser && isset($existingUser['UserProfileId'])){
                    return response()->
                    json(['result' => ['message' => 'User already exists.']], 500);
                }else{
                    $clientUserProfile = new ClientUserProfile();
                    $clientUserProfile['ClientId'] = $request['ClientId'];
                    $clientUserProfile['UserEmail'] = $request['NewUserEmail'];
                    $clientUserProfile['UserAppId'] = null;
                    $clientUserProfile['isAdmin'] = $request['isAdmin'];
                    $clientUserProfile['UserConfirmationId'] = null;
                    $clientUserProfile->save();            
                    
                    // Send email to user for notification;
                    Mail::to($request['NewUserEmail'])->send(new RegistrationConfirmation(null, true));


                    return response()->json(['result' => 'success'], 200);
                }
            }else{
                return response()->
                json(['result'=>'Not admin user.'], 500);
            }

        } catch (Exception $e) {
            return response()->
            json($e, 500);
        }
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
            'admins' => 'required|array|min:1',
            'admins.*' => 'required|email:rfc,dns|max:100',
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
            $clientAuthReg = new ClientAuth();
            $clientAuthReg['ClientId'] = $clientId;
            $clientAuthReg['AuthKey'] = $this->generateRegKey();
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

            // Saving client admins.
            foreach ($request['admins'] as $admin) {
                $clientUserProfile = new ClientUserProfile();
                $clientUserProfile['ClientId'] = $clientId;
                $clientUserProfile['UserEmail'] = $admin;
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
    private function generateRegKey(){

        $registrationKey = null;

        do {

            $registrationKey = uniqid(true);

        } while (ClientAuth::where('AuthKey', '=', $registrationKey)->first());

        return $registrationKey;
    }

    /**
     * Gets the last registered client information.
     */
    private function getLastClientInfo($clientId){

        $clientInfo = Client::where('id', '=', $clientId)->first();
        $registrationInfo = ClientAuth::where('ClientId', '=', $clientId)->first();

        $lastClientInfo = [

            'EntityName' => $clientInfo['EntityName'],
            'Email' => $clientInfo['Email'],
            'ClientId' => $clientInfo['id'],
            'JiraUser' => $clientInfo['JiraUser'] == 1 ? true : false,
            'RegistrationKey' => $registrationInfo['AuthKey'],
            'ExpirationDate' => $registrationInfo['ExpirationDate']

        ];

        return $lastClientInfo;
    }
}
