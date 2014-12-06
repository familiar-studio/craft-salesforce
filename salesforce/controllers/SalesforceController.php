<?php
namespace Craft;

class SalesforceController extends BaseController
{
  private $scopes = array();
  private $params = array();



  public function actionDeleteCPPAT()
  {


	  $id = craft()->request->getParam('cppatId');
	  craft()->salesforce->delete('CPPAT__c',$id );
	  $this->redirect('/account/cppats');
  }

  public function actionActivateUser() {
    $user = craft()->userSession->getUser();

    craft()->salesforce->activateUser($user);

    $this->redirect('/account/cppats');
  }


  // /**
  // * Connect
  // */
  // public function actionConnect(array $variables = array())
  // {
  //   if($response = craft()->oauth->connect(array(
  //       'plugin' => 'salesforce',
  //       'provider' => 'salesforce'
  //   )))
  //   {
  //       if($response['success'])
  //       {
  //           // token
  //           $token = $response['token'];
  //
  //           // save token
  //           craft()->salesforce->saveToken($token);
  //
  //           // session notice
  //           craft()->userSession->setNotice(Craft::t("Connected to Salesforce."));
  //       }
  //       else
  //       {
  //           craft()->userSession->setError(Craft::t($response['errorMsg']));
  //       }
  //
  //       $this->redirect($response['redirect']);
  //   }
  // }
  //
  // /**
  //  * Disconnect
  //  */
  // public function actionDisconnect()
  // {
  //     // reset token
  //     craft()->salesforce->saveToken(null);
  //
  //     // set notice
  //     craft()->userSession->setNotice(Craft::t("Disconnected from Salesforce."));
  //
  //     // redirect
  //     $redirect = craft()->request->getUrlReferrer();
  //     $this->redirect($redirect);
  // }
}
