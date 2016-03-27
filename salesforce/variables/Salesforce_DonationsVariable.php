<?php


namespace Craft;

class Salesforce_DonationsVariable
{

    public function getCampaign($campaignId)
    {
      $campaign = craft()->salesforce->query('Select Days_Remaining_in_Campaign__c, EndDate, AmountAllOpportunities, Campaign_Goal__c, Percent_of_Goal__c from Campaign WHERE Id = \''.$campaignId.'\' limit 1')[0];
      return $campaign;
    }

    public function getOpportunities($campaignId)
    {
      $opportunities = craft()->salesforce->query('Select Amount, Optional_Donor_Message__c, Donor_Display_Opt_In__c, Account.Name, (Select Contact.FirstName, Contact.LastName from OpportunityContactRoles where IsPrimary = true or Role = \'Primary Contact\' limit 1) from Opportunity WHERE Campaign.Id = \''.$campaignId.'\' and Donor_Display_Opt_In__c != \'Hide Donation\' and Donor_Display_Opt_In__c != \'\'');
      return $opportunities;
    }

    public function getOpportunityFromCampaignAndContact($campaignId, $stripeChargeId)
    {
      $opportunity = craft()->salesforce->query('Select Id, Name from Opportunity WHERE Campaign.Id = \''.$campaignId.'\' AND Stripe_Charge_Id__c = \''.$stripeChargeId.'\' limit 1')[0];
      return $opportunity;
    }
}
