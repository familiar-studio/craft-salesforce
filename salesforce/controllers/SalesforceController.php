<?php
namespace Craft;

class SalesforceController extends BaseController
{
  private $scopes = array();
  private $params = array();

  protected $allowAnonymous = true;

  // sets up an enpoint for saleforce triggers to hit to clear the cache
  public function actionClearCache()
  {
    //map all the fields to the data object
    $data = craft()->request->getPost('data');

    SalesforcePlugin::log('this is saving');
    craft()->salesforce->save('Contact', $data, $id);

  }



  public function actionUpdateContact()
  {

    $this->requirePostRequest();


    $id  = craft()->request->getPost('Id');


    $data = array();
    $data['FirstName']  = craft()->request->getPost('FirstName');


    $data['LastName']  = craft()->request->getPost('LastName');
    $data['Email']  = craft()->request->getPost('Email');

    if (craft()->request->getPost('HasOptedOutOfEmail')) {
      $data['HasOptedOutOfEmail'] = true;
    }

    $data['Phone']  = craft()->request->getPost('Phone');

    $data['MailingStreet']  = craft()->request->getPost('MailingStreet');
    $data['MailingCity']  = craft()->request->getPost('MailingCity');
    $data['MailingState']  = craft()->request->getPost('MailingState');
    $data['MailingPostalCode']  = craft()->request->getPost('MailingPostalCode');
    $data['MailingCountry']  = craft()->request->getPost('MailingCountry');


    craft()->salesforce->save('Contact', $data, $id);


    $this->redirect('/support/contact?contactId='.$id.'&updated=true');

  }

  public function actionCheckContact()
  {
<<<<<<< HEAD

    $json = file_get_contents('php://input');
    $post = json_decode($json);

    $email = $post->email;

    if ($email != null) {

      $contacts = craft()->salesforce->query('Select Id, Email, FirstName, LastName from Contact Where Email = \''.$email.'\' OR npe01__AlternateEmail__c = \''.$email.'\' OR npe01__HomeEmail__c = \''.$email.'\' OR npe01__WorkEmail__c = \''.$email.'\'  limit 1');

      if (count($contacts)>0 ) {

        $this->returnJson($contacts[0]);

      }
    }

    $error = array();
    $error['message'] = 'not found';
    $this->returnJson($error);

=======
    header("Access-Control-Allow-Origin: *");
    $json = file_get_contents('php://input');
    $post = json_decode($json);
    $email = $post->Email;

    if ($email != null) {
      $contacts = craft()->salesforce->query('Select Id, Email, FirstName, LastName, MailingPostalCode, Number_of_Visits__c, npo02__LastMembershipLevel__c, npo02__MembershipEndDate__c, (SELECT CreatedDate from Visits__r order by CreatedDate desc limit 1) from Contact Where Email = \''.$email.'\' OR npe01__AlternateEmail__c = \''.$email.'\' OR npe01__HomeEmail__c = \''.$email.'\' OR npe01__WorkEmail__c = \''.$email.'\'  limit 1');

      if (count($contacts) > 0) {
        $contact = $contacts[0];
        $this->returnJson($contact);
      } else {
        $error = array();
        $error['error'] = true;
        $error['message'] = 'not found';
        $this->returnJson($error);
      }
    } else {
      $error = array();
      $error['error'] = true;
      $error['message'] = 'not found';
      $this->returnJson($error);
    }
  }

  public function actionCreateContact()
  {
    $json = file_get_contents('php://input');
    $post = json_decode($json);
    $response = craft()->salesforce->save('Contact', $post);
    $this->returnJson($response);
>>>>>>> master
  }

  public function actionCreateVisit()
  {
    $json = file_get_contents('php://input');
    $post = json_decode($json);
    $visitData = array();
    $visitData['Zip__c'] = $post->MailingPostalCode;
    $response = craft()->salesforce->save('Visit__c', $visitData);
    $this->returnJson($response);
  }

<<<<<<< HEAD

=======
  public function actionUpdateVisit() {
    $json = file_get_contents('php://input');
    $post = json_decode($json);
    $visitId = $post->Id;
    unset($post->Id);
    $visitData = array();
    $visitData['Contact__c'] = $post->Contact__c;
    $response = craft()->salesforce->save('Visit__c', $visitData, $visitId);
    $this->returnJson($response);
  }
>>>>>>> master
}
