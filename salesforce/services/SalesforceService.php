<?php
namespace Craft;


use Guzzle\Http\Client;


class SalesforceService extends BaseApplicationComponent
{
    private $token;
    private $client;
    private $baseUrl;
    private $headers;


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
    			$loginUrl = 'https://cs1.salesforce.com';
    			$clientId = $settings->clientIdSandbox;
    			$clientSecret = $settings->clientSecretSandbox;
    			$redirectUri = $settings->redirectUriSandbox;
    			$username = $settings->usernameSandbox;
    			$password = $settings->passwordSandbox;
    			$instanceUrl = $settings->instanceUrlSandbox;
          $this->token = $settings->tokenSandbox;
    		} else {
    			$loginUrl = 'https://na1.salesforce.com';
    			$clientId = $settings->clientIdLive;
    			$clientSecret = $settings->clientSecretLive;
    			$redirectUri = $settings->redirectUriLive;
    			$username = $settings->usernameLive;
    			$password = $settings->passwordLive;
    			$instanceUrl = $settings->instanceUrlLive;
          $this->token = $settings->tokenLive;
    		}

        $this->headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'OAuth '.$this->token
        );

        $this->client = new \Guzzle\Http\Client($instanceUrl);
        $this->baseUrl = "/services/data/v35.0/";

        if (!$this->token) {

          $curl	 	= curl_init($loginUrl.'/services/oauth2/token');
          SalesforcePlugin::log('!!!!!USERNAME '.$username);

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
          SalesforcePlugin::log('authentication '.  $authentication );
          $authentication = json_decode($authentication);

          $this->token = $authentication->access_token;

          if (!$settings->isLive) {
            craft()->plugins->savePluginSettings( $plugin, array('tokenSandbox' => $this->token));
          } else {
            craft()->plugins->savePluginSettings( $plugin, array('tokenLive' => $this->token));
          }
        }


    }

    public function connect() {


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

    public function getPicklist($object, $fieldname) {

      $this->setup();


      $url = $this->baseUrl."sobjects/".$object."/describe";


      $response = $this->client->get($url)->setHeader("Authorization", "OAuth ".$this->token)->send();

      if (!$response->isSuccessful()) {
        return;
      }
      $body = $response->getBody();



      SalesforcePlugin::log('response'.$body);

      $bodyObject =  json_decode($body);
      foreach ($bodyObject->fields as $field) {

        if ($field->name == $fieldname) {
          return $field->picklistValues;
        }

      }
      return;

    }



    public function query($query, $cacheKey = null, $json = true) {
	    $this->setup();

      $query = urlencode($query);

      if ($cacheKey) {

        $cachedResponse = craft()->cache->get($cacheKey);

        if ($cachedResponse) {
          return $cachedResponse;
        }

      }


	    $queryUrl = $this->baseUrl."query?q=".$query;
      $data = $this->getRecords($queryUrl);



      if ($cacheKey) {
        craft()->cache->set($cacheKey, $data);
      }

      return $data;


    }

    public function getRecords($url) {


      try {
        $response = $this->client->get($url)->setHeader("Authorization", "OAuth ".$this->token)->send();
      } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
        echo $e->getRequest();
        echo 'uh oh'.$e->getMessage();
        echo '<h3>'.$e->getResponse()->getBody(true).'</h3>';
        exit;
      }

      if (!$response->isSuccessful()) {
        return;
      }
      $body = $response->getBody();

      $bodyObject =  json_decode($body);



      $data= $bodyObject->records;

      if (!$bodyObject->done) {
        $data =   array_merge($data, $this->getRecords($bodyObject->nextRecordsUrl));
      }


      return $data;


    }

    public function describe() {
      return this.ajax('/' + this.apiVersion + '/sobjects/' + objtype
            + '/describe/', callback, error);
    }

    public function retrieve($object, $id, $fields=null) {
      $this->setup();

      $queryUrl = $this->baseUrl."sobjects/".$object."/".$id;

      if ($fields) {
        $queryUrl .=  "?fields=".$fields;
      }


      $response = $this->client->get($queryUrl)->setHeader("Authorization", "OAuth ".$this->token)->send();

      if (!$response->isSuccessful()) {
        return;
      }
      $body = $response->getBody();


      $bodyObject =  json_decode($body);



      return $bodyObject;

    }


    public function save($object, $data, $id = null) {
      $this->setup();

      $data = json_encode($data);

      SalesforcePlugin::log('About to save inside'.$data);


      if ($id != null) {
        $postUrl = $this->baseUrl."sobjects/".$object."/".$id;
        $response = $this->client->patch($postUrl,$this->headers,$data)->send();
      } else {

        SalesforcePlugin::log('post'.$data);

        $postUrl = $this->baseUrl."sobjects/".$object.'/';

        SalesforcePlugin::log('post url'.$postUrl);


        $response = $this->client->post($postUrl,$this->headers,$data)->send();

      }


      SalesforcePlugin::log('posted'.$data);


      if (!$response->isSuccessful()) {
        //return $response;
        SalesforcePlugin::log('Error');
        SalesforcePlugin::log('Error text'.$response);
        exit;
      }

      $body = $response->getBody();

      SalesforcePlugin::log('Success!'.$body);


      $bodyObject =  json_decode($body);



      return $bodyObject;

    }





}
