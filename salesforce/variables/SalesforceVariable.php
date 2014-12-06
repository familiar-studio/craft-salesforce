<?php
namespace Craft;

class SalesforceVariable
{
  public function activateUser($user) {
  	$response = craft()->salesforce->activateUser($user);

  	return $response;
  }
    
    public function getCPPATS($contactId){
	     
      $query = "SELECT Name,CreatedDate, LastModifiedDate, CPPAT_Progress__c, Id from CPPAT__c where Contact__c = '".$contactId."' ORDER BY CreatedDate DESC LIMIT 100"; 
      $response = craft()->salesforce->query($query);
      
      return $response;
    }

}
