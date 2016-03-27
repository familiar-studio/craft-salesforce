<?php

namespace Craft;

class SalesforceVariable
{
    public function test()
    {

        $contact = craft()->salesforce->query('Select Id, Email, FirstName from Contact limit 1')[0];
        return $contact->Email;
    }


    public function getContact($contactId)
    {

      $contact = craft()->salesforce->query('Select Id, Email,Phone, HasOptedOutOfEmail, FirstName, LastName, npo02__LastMembershipLevel__c, npo02__MembershipEndDate__c, MailingStreet, MailingCity, MailingState, MailingPostalCode, MailingCountry from Contact where Id =\''.$contactId.'\' limit 1')[0];

      return $contact;


    }


}
