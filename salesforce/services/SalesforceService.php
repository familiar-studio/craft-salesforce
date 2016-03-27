<?php
namespace Craft;

use Guzzle\Http\Client;

class SalesforceService extends BaseApplicationComponent
{


    public function query($query, $cacheKey = null, $json = true) {

      SalesforcePlugin::log('Query called: '.$query);
	    $queryUrl = "/query?q=".urlencode($query);

      $response = $this->request($queryUrl,'get', $cacheKey);
      $data = $response->records;

      //if there is a second page of records get that too!


      if (!$response->done) {

        $secondResponse = $this->request($response->nextRecordsUrl, 'get');

        $data = array_merge($data,$secondResponse->records );
      }

      return $data;

    }

    public function retrieve($object, $id, $fields=null, $cacheKey = null) {

      $uri = "/sobjects/".$object."/".$id;

      // if you ask for specific fields just return those
      if ($fields) {
        $uri .=  "?fields=".$fields;
      }

      $response = $this->request($uri,'get', $cacheKey);

      return $response;

    }


    public function sobjects($cacheKey = null) {

      $response = $this->request('/sobjects', 'get', $cacheKey);
      return $response->sobjects;

    }

    public function describe($objectName, $cacheKey = null) {

      $uri = "/sobjects/".$objectName."/describe";
      $response = $this->request($uri,'get', $cacheKey);

      return $response;

    }

    public function getPicklist($objectName, $fieldname, $cacheKey = null) {

      $uri = "/sobjects/".$objectName."/describe";

      $response = $this->request($uri, 'get', $cacheKey);

      foreach ($response->fields as $field) {
        if ($field->name == $fieldname) {
          return $field->picklistValues;
        }
      }

      return null;

    }

    public function save($objectName, $data, $id = null) {

      $data = json_encode($data);

      SalesforcePlugin::log('About to save inside'.$data);

      if ($id != null) {
        $uri = "/sobjects/".$objectName."/".$id;
        $response = $this->request($uri, 'patch', null, $data);

      } else {
        $uri = "/sobjects/".$objectName.'/';
        $response = $this->request($uri, 'post', null, $data);
      }


      return $response;

    }

    public function delete($objectName, $id) {

      $uri = "/sobjects/".$objectName."/".$id;
      $response = $this->request($uri,'delete');

      return $response;

    }

    public function attachmentBody($id) {


      $uri = "/sobjects/Attachment/".$id."/body";
      $response = $this->request($uri,'getRaw');
      return $response;
    }



    public function request($uri, $method = 'get',  $cacheKey = null, $data = null) {

      SalesforcePlugin::log('Getting records');

      // if cached
      if ($cacheKey) {
        $cachedResponse = craft()->cache->get($cacheKey);
        if ($cachedResponse) {
          return $cachedResponse;
        }
      }

      $plugin = craft()->plugins->getPlugin('salesforce');
      $settings = $plugin->getSettings();

      if ($settings->env == 'sandbox') {
        $instanceUrl =   $settings->instanceUrlSandbox;
      } else {
        $instanceUrl =   $settings->instanceUrlLive;
      }


      $client = new \Guzzle\Http\Client($instanceUrl);
      $baseUrl = "/services/data/v".$settings->version.".0";

      //craft()->kint->dd(craft()->salesforce_oauth->getToken());

      $token = craft()->salesforce_oauth->getToken()->accessToken;

      $headers = array(
          'Content-Type' => 'application/json',
          'Authorization' => 'OAuth '.$token
      );

      // if already has base url attached
      if (substr($uri, 0, 15) == '/services/data/' ) {

        $url = $uri;

      } else {
        $url = $baseUrl.$uri;

      }


      try {
        if ($method == 'post') {
          $response = $client->post($url,$headers,$data)->send();
        } else if ($method == 'patch') {
          $response = $client->patch($url, $headers ,$data)->send();
        } else if ($method == 'delete') {
          $response = $client->delete($url, $headers)->send();
        } else {
          $response = $client->get($url, $headers)->send();
        }

        if (!$response->isSuccessful()) {
          SalesforcePlugin::log('Unsuccessful Request '.$e->getRequest());
          return;
        }
        $body = $response->getBody();

        SalesforcePlugin::log('The Response '.$body);

        if ($method == 'getRaw') {
          $data =  $body;
        } else {
          $data = json_decode($body);

        }

        if ($cacheKey) {
          craft()->cache->set($cacheKey, $data);
        }

        return $data;

      } catch (\Exception $e) {
        //SalesforcePlugin::log('The Token '.$this->token);
        SalesforcePlugin::log('****Error! '.$e->getResponse()->getBody(true));
        SalesforcePlugin::log('The Request '.$e->getRequest());

        //if expired token force refresh and try again

        return $e->getResponse()->getBody(true);
      }


    }










}
