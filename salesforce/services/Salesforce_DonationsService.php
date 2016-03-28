<?php
namespace Craft;
use Guzzle\Http\Client;


class Salesforce_DonationsService extends BaseApplicationComponent
{

    public function getAccountByMemberId($memberId, $memberLastName)
    {

      $accounts = craft()->salesforce->query('Select Id, Name, npo02__LastMembershipLevel__c, npo02__Informal_Greeting__c, npo02__MembershipEndDate__c, (Select Id, AccountId, FirstName, LastName, Email, HomePhone, MailingStreet, MailingCity, MailingState, MailingPostalCode, MailingCountry from Contacts)  from Account Where Member_Id__c = \''.$memberId.'\' AND Name LIKE \'%'.$memberLastName.'%\' limit 1');
      if (count($accounts) > 0) {
        $account = $accounts[0];
        return $account;
      }
    }


    public function syncCharge($charge) {

      $plugin = craft()->plugins->getPlugin('salesforce');
      $settings = $plugin->getSettings();

      SalesforcePlugin::log('**** Creating Donation **** ');

      $phone = array_key_exists('phone',$charge['meta']) ? $charge['meta']['phone']: null;

      // lookup or create contact
      $contact = $this->getContact($charge['meta']['firstName'], $charge['meta']['lastName'],$charge['customerEmail'], $charge['cardAddressLine1'], $charge['cardAddressCity'],$charge['cardAddressState'], $charge['cardAddressZip'], null, $charge['stripeCustomerId']);

      SalesforcePlugin::log('**** Got Contact **** ');
      SalesforcePlugin::log('The Contact: '.json_encode($contact));

      //check if opportunity already was made by either salesforceId or chargeId
      if (array_key_exists('salesforceId',$charge['meta'])) {
        $opportunities = craft()->salesforce->query('Select Id from Opportunity Where Id = \''.$charge['meta']['salesforceId'].'\' or Stripe_Charge_Id__c = \''.$charge['stripeChargeId'].'\' limit 1');
      } else {
        $opportunities = craft()->salesforce->query('Select Id from Opportunity Where Stripe_Charge_Id__c = \''.$charge['stripeChargeId'].'\' limit 1');
      }

      if (count($opportunities)== 0) {

        // get Record Type if set, otherwise use normal donation
        if ($charge[$settings->indicatorRecordType] != '' && $charge[$settings->indicatorRecordType] != null) {
          SalesforcePlugin::log('RecordType '.$charge['description']);

          $oppRecordType = craft()->salesforce->query('Select Id from RecordType WHERE SobjectType = \'Opportunity\' and Name = \''.$charge['description'].'\' limit 1' )[0];
          $oppData['RecordTypeId'] = $oppRecordType->Id;

          SalesforcePlugin::log('RecordTypeId '.$oppRecordType->Id);
        }

        // Set basic opportunity information
        $chargeTime = date('Y-m-d',strtotime($charge['timestamp']));

        // set standard opportunity fields
        $oppData['Name'] = $contact->Account->Name.' Donation on '.$chargeTime;
        $oppData['AccountId'] = $contact->AccountId;
        $oppData['StageName'] = $settings->stage;
        $oppData['CloseDate'] = $chargeTime;
        $oppData['Amount'] = $charge['planAmount']/100;
        $oppData['LeadSource'] = $settings->leadSource;

        // set stripe charge Id
        $oppData[$settings->fieldChargeId] = $charge['stripeChargeId'];

        // loop through all the custom opportunity fields
        foreach ($settings->opportunityFields as $field) {

          $sfField = $field[0];
          $fieldType = $field[1];
          $valueType = $field[2];
          $value = $field[3];

          if ($valueType == 'meta') {
            if (array_key_exists($value,$charge['meta'])) {

              if ($fieldType == 'checkbox') {
                $oppData[$sfField] =  ($charge['meta'][$value] == 'on' ? true: false);
              } else if ($fieldType == 'date') {
                $oppData[$sfField] = date('Y-m-d',strtotime($charge['meta'][$value]));
              } else if ($fieldType == 'dateTime') {
                //TODO: fix date time support
                $oppData[$sfField] = date('Y-m-d',strtotime($charge['meta'][$value]));
              } else {
                $oppData[$sfField] = $charge['meta'][$value];
              }

            }
          } else {
            $oppData[$sfField] = $value;
          }

        }

        // optional second contact
        // TODO: doesnt look for duplicates because doensnt want to change the account of existing contact
        if (array_key_exists('secondContactLastName',$charge['meta'])) {
          if ($charge['meta']['secondContactLastName'] != '') {
            $secondContact['AccountId'] = $contact->AccountId;
            $secondContact['FirstName'] = $charge['meta']['secondContactFirstName'];
            $secondContact['LastName'] = $charge['meta']['secondContactLastName'];

            if (array_key_exists('secondContactEmail',$charge['meta'])) {
              $secondContact['Email'] = $charge['meta']['secondContactEmail'];
            }
            DonationsPlugin::log('about to save second contact: '.json_encode($secondContact));
            $secondContact = craft()->salesforce->save('Contact', $secondContact);
          }
        }

        // optional gift membership
        if (array_key_exists('giftMembership',$charge['meta'])) {
          if ($charge['meta']['giftMembership'] == 'true') {

            SalesforcePlugin::log('Gift Membership Before Update: '.json_encode($oppData));
            // add the gift contact
            $giftRecipient  = $this->getContact($charge['meta']['giftFirstName'], $charge['meta']['giftLastName'],$charge['meta']['giftEmail'], $charge['meta']['giftAddress'], $charge['meta']['giftCity'],$charge['meta']['giftState'], $charge['meta']['giftZip']);

            //update the opportunity to be aassoicated with the recipients account
            $oppData['AccountId'] = $giftRecipient->AccountId;
            $oppData['Name'] = $giftRecipient->Account->Name.' Donation on '.$chargeTime;

            SalesforcePlugin::log('Gift Membership After Update: '.json_encode($oppData));

          }

        }

        SalesforcePlugin::log('About to save: '.json_encode($oppData));

        $opp = craft()->salesforce->save('Opportunity', $oppData);
        $oppId = $opp->id;

        // if is a gift membershop create contact role for purchaser
        if (array_key_exists('giftMembership',$charge['meta'])) {
          if ($charge['meta']['giftMembership'] == 'true') {

            $conRole = array();
            $conRole['ContactId'] = $contact->Id;
            $conRole['Role'] = 'Gift Purchaser';
            $conRole['OpportunityId'] = $oppId;

            craft()->salesforce->save('OpportunityContactRole', $conRole);
          }
        }

        SalesforcePlugin::log('New opportunity '.$oppId);
      } else {
        // if opportunity already existed in database use that one
        $oppId = $opportunities[0]->Id;
      }

      $newMeta = $charge['meta'];
      $newMeta['salesforceId'] = $oppId;
      $details = array('meta' => $newMeta);

      craft()->charge->updateChargeDetails($charge['id'], $details);

      return true;
    }



    public function getContact($firstName, $lastName, $email, $street = null, $city = null,$state = null, $zip = null, $phone = null, $stripeCustomerId = '0',$country = 'US', $accountId=null) {

      $plugin = craft()->plugins->getPlugin('salesforce');
      $settings = $plugin->getSettings();

      SalesforcePlugin::log('**** Getting Contact **** ');

      // Find the customer by matching on email address or stripe customer id
      $contacts = craft()->salesforce->query('Select Id, Email, Account.Name, AccountId from Contact Where Email = \''.$email.'\' OR npe01__AlternateEmail__c = \''.$email.'\' OR npe01__HomeEmail__c = \''.$email.'\' OR npe01__WorkEmail__c = \''.$email.'\' OR Stripe_Customer_Id__c = \''.$stripeCustomerId.'\'  limit 1');
      SalesforcePlugin::log('after query '.$email);

      if (count($contacts)>0 ) {
        // if it returns something just use that contact
        $contact = $contacts[0];
        SalesforcePlugin::log('Contact Exits '.$contact->Id);

      } else {

        SalesforcePlugin::log('New Contact: '.$email);

        $data = array();

        $data['FirstName'] = $firstName;
        $data['LastName'] = $lastName;
        $data['Email'] = $email;
        $data[$settings->fieldCustomerId] = $stripeCustomerId;
        $data['Phone'] = $phone;
        $data['MailingStreet'] = $street;
        $data['MailingCity'] = $city;
        $data['MailingState'] = $state;
        $data['MailingPostalCode'] = $zip;
        $data['MailingCountry'] = $country;

        SalesforcePlugin::log('About to save: '.json_encode($data));

        $contact = craft()->salesforce->save('Contact', $data);

        $contact->Id = $contact->id;
        // fill in account information for new account
        $contact = craft()->salesforce->query('Select Id, AccountId, Account.Name, Email from Contact Where Id = \''.$contact->Id.'\' limit 1')[0];

      }

      return $contact;

    }
}
