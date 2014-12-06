<?php

/**
 * Craft OAuth by Dukt
 *
 * @package   Craft OAuth
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 * @link      https://dukt.net/craft/oauth/
 */

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

    public function getCPPATS($id)
    {

     return craft()->salesforce->getCPPATS($id);
    }

    public function getSettingsHtml()
    {
      return craft()->templates->render('salesforce/settings', array(
        'settings' => $this->getSettings()
      ));
    }

    public function onBeforeUninstall()
    {
      if(isset(craft()->oauth))
      {
          craft()->oauth->deleteTokensByPlugin('salesforce');
      }
    }

    public function init()
    {
      // $user = craft()->userSession->getUser();
      // craft()->salesforce->activateUser($user);

      // anything in here runs on every page request
     /*
 craft()->users->onActivateUser = function(Event $event) {
        $user = $event->params['user'];
        SalesforcePlugin::log('In Event');

        craft()->salesforce->activateUser($user);
        // check salesforce for contact with that email address
        // if exists, get contact ID and save it on user
        // if doesnt exist create contact and account (using organization name) and save contactID on user

      };
*/
    }
    protected function defineSettings() 
    {
      return array (
        'isLive' => array(AttributeType::Bool, 'required' => true, 'default' => false),
        'clientIdSandbox' => array(AttributeType::String, 'required' => true),
        'clientSecretSandbox' => array(AttributeType::String, 'required' => true),
        'redirectUriSandbox' => array(AttributeType::String, 'required' => true),
        'usernameSandbox' => array(AttributeType::String, 'required' => true),
        'passwordSandbox' => array(AttributeType::String, 'required' => true),
        'instanceUrlSandbox' => array(AttributeType::String, 'required' => true),
        'tokenSandbox' => array(AttributeType::String),

        'clientIdLive' => array(AttributeType::String, 'required' => true),
        'clientSecretLive' => array(AttributeType::String, 'required' => true),
        'redirectUriLive' => array(AttributeType::String, 'required' => true),
        'usernameLive' => array(AttributeType::String, 'required' => true),
        'passwordLive' => array(AttributeType::String, 'required' => true),
        'instanceUrlLive' => array(AttributeType::String, 'required' => true),
        'tokenLive' => array(AttributeType::String)
      );
    }
}
