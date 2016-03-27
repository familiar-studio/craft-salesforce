<?php
namespace Craft;

class Salesforce_DonationsController extends BaseController
{

  protected $allowAnonymous = true;



  public function actionSyncCharge()
  {
    $chargeId = craft()->request->getParam('chargeId');

    $charge = craft()->charge->getChargeById($chargeId);
    $synced = craft()->salesforce_donations->syncCharge($charge);

    $this->redirect('/admin/charge/detail/'.$chargeId);


  }

  public function actionGetCampaign()
  {
    $campaignId = craft()->request->getParam('campaignId');
    $campaign = craft()->salesforce_donations->getCampaignByCampaignId($campaignId);
    // $this->returnJson($account);

    $this->renderTemplate('donate/index', array (
      'campaign' => $campaign
    ));
  }


  public function actionLookupEmail()
  {
    $email = craft()->request->getParam('email');


    $contacts = craft()->salesforce->query('Select Id, Email_Stay_in_Touch_Form__c from Contact Where Email = \''.$email.'\' OR npe01__AlternateEmail__c = \''.$email.'\' OR npe01__HomeEmail__c = \''.$email.'\' OR npe01__WorkEmail__c = \''.$email.'\' limit 1');


    if (count($contacts)>0 ) {

      $data = array();
      $data['Email_Stay_in_Touch_Form__c'] = true;
      $contact = craft()->salesforce->save('Contact', $data, $contacts[0]->Id);

      $this->redirect('/support/contact/login?thanks=true');
    } else {
      $this->redirect('/support/contact/login?error=true');
    }



  }

  // this isn't needed if the donor opt-in functionality is on the payment form
  // public function actionAddOptInInfo()
  // {
  //
  //   $opportunityId = craft()->request->getParam('opportunityId');
  //   $optIn = craft()->request->getParam('optIn');
  //
  //   $oppData['Donor_Display_Opt_In__c'] = $optIn;
  //
  //   $opp = craft()->salesforce->save('Opportunity', $oppData, $opportunityId);
  //
  // }

}
