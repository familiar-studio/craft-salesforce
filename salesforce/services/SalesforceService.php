<?php
namespace Craft;


use Guzzle\Http\Client;


class SalesforceService extends BaseApplicationComponent
{
    private $token;
    private $client;
    private $baseUrl;


    /**
     * setupConnection
     */
    public function setup()
    {

        // get plugin
        $plugin = craft()->plugins->getPlugin('salesforce');

        // get settings
        $settings = $plugin->getSettings();
		
		
		if (!$settings->isLive) {
			$loginUrl = 'https://test.salesforce.com';
			$clientId = $settings->clientIdSandbox;
			$clientSecret = $settings->clientSecretSandbox;
			$redirectUri = $settings->redirectUriSandbox;
			$username = $settings->usernameSandbox;
			$password = $settings->passwordSandbox;
			$instanceUrl = $settings->instanceUrlSandbox;
	
		} else {
			$loginUrl = 'https://login.salesforce.com';
			$clientId = $settings->clientIdLive;
			$clientSecret = $settings->clientSecretLive;
			$redirectUri = $settings->redirectUriLive;
			$username = $settings->usernameLive;
			$password = $settings->passwordLive;
			$instanceUrl = $settings->instanceUrlLive;
			
		}
		
        $curl	 	= curl_init($loginUrl.'/services/oauth2/token');

        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'username' => $username, // The code from the previous request
            'password' => $password,
            'grant_type' => 'password'
        ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);


        $authentication =curl_exec($curl);

        $authentication = json_decode($authentication);

        $this->token = $authentication->access_token;
		$this->client = new \Guzzle\Http\Client($instanceUrl);
		$this->baseUrl = "/services/data/v29.0/";

    

    }




    public function sobjects() {

      $this->setup();
      $request = $this->client->get('/services/data/v29.0/sobjects')->setHeader("Authorization", "OAuth ".$this->token);

      $response = $request->send();

      if (!$response->isSuccessful()) {
        return;
      }
      $body = $response->getBody();

      $bodyObject =  json_decode($body);

      return $bodyObject->sobjects;
    }


    public function query($query) {
	  $this->setup();
	  
	  $queryUrl = $this->baseUrl."query?q=".urlencode($query);
	  
	  //var_dump($query);
	  //exit;
	  
      $response = $this->client->get($queryUrl)->setHeader("Authorization", "OAuth ".$this->token)->send();

      if (!$response->isSuccessful()) {
        return;
      }
      $body = $response->getBody();

      $bodyObject =  json_decode($body);

      return $bodyObject->records;

    }

    public function delete($object, $id) {
      $this->setup();

	  $deleteURL = $this->baseUrl."sobjects/".$object."/".$id;

      $response = $this->client->delete($deleteURL)->setHeader("Authorization", "OAuth ".$this->token)->send();

      if (!$response->isSuccessful()) {
        return;
      }
      $body = $response->getBody();

      $bodyObject =  json_decode($body);

      return $bodyObject;

    }





    public function activateUser($user)
    {
	  $this->setup();
	   	
      if (!is_null($user->email)) {
     
        $query = "SELECT Id FROM Contact WHERE Email = '".$user->email."'";

        $queryUrl = $this->baseUrl."query?q=".urlencode($query);

        $response = $this->client->get($queryUrl)->setHeader("Authorization", "OAuth ".$this->token)->send();

        if (!$response->isSuccessful()) {
          return false;
        }

        $body = $response->getBody();
        $bodyObject = json_decode($body);

        try {
          SalesforcePlugin::log($body);

          if (sizeOf($bodyObject->records) == 0) {
            SalesforcePlugin::log('CREATE : CONTACT DOES NOT EXIST');
            // required fields: LastName

            $insertAccountUrl = $this->baseUrl."sobjects/Account/";
            $insertContactUrl = $this->baseUrl."sobjects/Contact/";

            $request = $this->client->post($insertAccountUrl, array(
                'Content-Type' => 'application/json'
              ), array())->setHeader("Authorization", "OAuth ".$this->token);

            $userOrg = $user->getContent()->organizationName;

            if ($userOrg == '') {
              $userOrg = 'No Organization';
            }

            $response = $request->setBody('{ "Name" : "'.$userOrg.'" }')->send();

            $body = $response->getBody();
            $bodyObject = json_decode($body);
            $accountId = $bodyObject->id;

            $contactRequest = $this->client->post($insertContactUrl, array(
               'Content-Type' => 'application/json'
              ), array())->setHeader("Authorization", "OAuth ".$this->token);

            $userName = $user->firstName;
            $userLast = $user->lastName;

            $response = $contactRequest->setBody('{"FirstName": "'.$userName.'", "LastName":"'.$userLast.'", "AccountId": "'.$accountId.'", "Email" : "'.$user->email.'"}')->send();

            $contactBody = $response->getBody();
            $contactBodyObject = json_decode($contactBody);
            $contactId = $contactBodyObject->id;
            $user->getContent()->contactId = $contactId;

            craft()->users->saveUser($user);

            return $user->getContent();

          } else {
            SalesforcePlugin::log('CONTACT EXISTS IN SF SETTING ID -', $bodyObject->records[0]->Id);

            $user->getContent()->contactId = $bodyObject->records[0]->Id;
            craft()->users->saveUser($user);

            return $user->getContent();
          }
        } catch (Exception $e) {
          SalesforcePlugin::log('ERROR', $e);
        }
      }

      return false;

    }



}
