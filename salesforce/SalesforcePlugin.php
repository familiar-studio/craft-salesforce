<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'salesforce/vendor/autoload.php');


class SalesforcePlugin extends BasePlugin
{
    /**
     * Get Name
     */
    function getName()
    {
        return Craft::t('Salesforce');
    }

    /**
     * Get Icon URL
     *
     * @return string
     */
    public function getIconUrl()
    {
        return UrlHelper::getResourceUrl('salesforce/salesforce.png');
    }


    /**
     * Get Version
     */
    function getVersion()
    {
        return '0.2';
    }


    /**
     * Returns required plugins
     *
     * @return array Required plugins
     */
    public function getRequiredPlugins()
    {
        return array(
            array(
                'name' => "OAuth",
                'handle' => 'oauth',
                'url' => 'https://dukt.net/craft/oauth',
                'version' => '1.0.0'
            )
        );
    }

    /**
     * Get Developer
     */
    function getDeveloper()
    {
        return 'Familiar';
    }

    /**
     * Get Developer URL
     */
    function getDeveloperUrl()
    {
        return 'http://familiar-studio.com/';
    }

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        return false;
    }

    public function getSettingsHtml()
    {

      $live = craft()->oauth->getProvider('salesforce');
      $sandbox = craft()->oauth->getProvider('salesforceSandbox');


      return craft()->templates->render('salesforce/settings', array(
        'settings' => $this->getSettings(),
        'oauth'=> array ('live'=> $live, 'sandbox' => $sandbox)
      ));
    }

    public function onBeforeUninstall()
    {
    }

    public function init()
    {
      // uncomment to test connection

      //$contact = craft()->salesforce->query("Select Id, Email, LastName from Contact limit 1");

      //$contact = craft()->salesforce->retrieve('Contact', '0033B000003Arqd');

      //$contact = craft()->salesforce->delete('Contact','0033B000003AtfL');

      // $data = array('FirstName'=>'Freddie', 'LastName'=>'Mancuso');
      // $contact = craft()->salesforce->save('Contact', $data);
      //

      //craft()->kint->dd($contact);
      //var_dump($contact);
      //die();

      $settings = $this->getSettings();

      if ($settings->syncCharges) {
        craft()->on('charge.onCharge', function(Event $event) {
          $charge = $event->params['charge'];
          //craft()->donations->createDonation($charge);
          craft()->tasks->createTask('Salesforce_Donations', 'Syncing Donation from '.$charge['customerEmail'] , array('charge' => $charge));
        });
      }



    }
    protected function defineSettings()
    {
      return array (
        'isLive' => array(AttributeType::Bool, 'required' => true, 'default' => false),
        'version' => array(AttributeType::Number, 'required' => true, 'default' => 34),
        'tokenIdLive' => array(AttributeType::String),
        'tokenIdSandbox' => array(AttributeType::String),
        'env'=> array(AttributeType::String),
        'instanceUrlSandbox' => array(AttributeType::String, 'required' => false),
        'instanceUrlLive' => array(AttributeType::String, 'required' => false),
        'syncCharges' =>  array(AttributeType::Bool, 'required' => true, 'default' => false),
        'fieldCustomerId' => array(AttributeType::String, 'required' => true, 'default' => 'Stripe_Customer_Id__c'),
        'fieldChargeId' => array(AttributeType::String, 'required' => true, 'default' => 'Stripe_Charge_Id__c'),
        'indicatorRecordType' => array(AttributeType::String, 'required' => true, 'default' => 'description'),
        'stage' => array(AttributeType::String, 'required' => true, 'default' => 'Closed Won'),
        'leadSource' => array(AttributeType::String, 'required' => true, 'default' => 'Web'),
        'opportunityFields' => array(AttributeType::Mixed, 'required' => false)


      );
    }

    public function getOauthProviders()
    {
        return [
            'Dukt\OAuth\Providers\Salesforce',
            'Dukt\OAuth\Providers\SalesforceSandbox'
        ];
    }


}
