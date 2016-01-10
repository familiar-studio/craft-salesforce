<?php

namespace Craft;

class SalesforceVariable
{
    public function test()
    {

        $contact = craft()->salesforce->query('Select Id, Email, FirstName from Contact limit 1')[0];
        return $contact->Email;
    }


}
