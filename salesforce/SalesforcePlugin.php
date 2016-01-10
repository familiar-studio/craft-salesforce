<?php

namespace Craft;

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
     * Get Version
     */
    function getVersion()
    {
        return '0.1';
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
      return craft()->templates->render('salesforce/settings', array(
        'settings' => $this->getSettings()
      ));
    }

    public function onBeforeUninstall()
    {
    }

    public function init()
    {
      // uncomment to test connection
      // craft()->salesforce->setup();
      // $contact = craft()->salesforce->query("Select Id, Email, LastName from Contact");
      // var_dump($contact);
      // exit;

    }
    protected function defineSettings()
    {
      return array (
        'isLive' => array(AttributeType::Bool, 'required' => true, 'default' => false),
        'clientIdSandbox' => array(AttributeType::String, 'required' => false),
        'clientSecretSandbox' => array(AttributeType::String, 'required' => false),
        'redirectUriSandbox' => array(AttributeType::String, 'required' => false),
        'usernameSandbox' => array(AttributeType::String, 'required' => false),
        'passwordSandbox' => array(AttributeType::String, 'required' => false),
        'instanceUrlSandbox' => array(AttributeType::String, 'required' => false),
        'tokenSandbox' => array(AttributeType::String),

        'clientIdLive' => array(AttributeType::String, 'required' => false),
        'clientSecretLive' => array(AttributeType::String, 'required' => false),
        'redirectUriLive' => array(AttributeType::String, 'required' => false),
        'usernameLive' => array(AttributeType::String, 'required' => false),
        'passwordLive' => array(AttributeType::String, 'required' => false),
        'instanceUrlLive' => array(AttributeType::String, 'required' => false),
        'tokenLive' => array(AttributeType::String)
      );
    }


}
