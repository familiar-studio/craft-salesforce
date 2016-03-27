<?php
/**
 * @link      https://dukt.net/craft/twitter/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/twitter/docs/license
 */

namespace Craft;

class Salesforce_OauthService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    /**
     * @var Oauth_TokenModel|null
     */
    private $token;

    // Public Methods
    // =========================================================================

    /**
     * Save Token
     *
     * @param Oauth_TokenModel $token
     */
    public function saveToken(Oauth_TokenModel $token, String $env)
    {
        // get plugin
        $plugin = craft()->plugins->getPlugin('salesforce');

        // get settings
        $settings = $plugin->getSettings();


        if ($env == 'sandbox') {
          $tokenId = $settings->tokenIdSandbox;
        } else {
          $tokenId = $settings->tokenIdLive;
        }

        // TODO make this a setting
        $time = time() + 7200; // 2 hours ttl

        $token->endOfLife = $time;

        // do we have an existing token ?

        $existingToken = craft()->oauth->getTokenById($tokenId);

        if($existingToken)
        {
            $token->id = $existingToken->id;
        }

        // save token
        craft()->oauth->saveToken($token);

        // set token ID

        if ($env == 'sandbox') {
          $settings->tokenIdSandbox = $token->id;
        } else {
          $settings->tokenIdLive = $token->id;
        }

        // save plugin settings
        craft()->plugins->savePluginSettings($plugin, $settings);
    }

    /**
     * Get OAuth Token
     */
    public function getToken(String $env = null)
    {
        if($this->token)
        {
            return $this->token;
        }
        else
        {
            // get plugin
            $plugin = craft()->plugins->getPlugin('salesforce');

            // get settings
            $settings = $plugin->getSettings();

            //if not asking for a specific envoriment return the current one
            if ($env == null) {
              $env = $settings->env;
            }

            // get tokenId
            if ($env == 'sandbox') {
              $tokenId = $settings->tokenIdSandbox;
            } else {
              $tokenId = $settings->tokenIdLive;
            }

            // get token
            $token = craft()->oauth->getTokenById($tokenId);

            return $token;
        }
    }

    /**
     * Delete Token
     */
    public function deleteToken(String $env)
    {
        // get plugin
        $plugin = craft()->plugins->getPlugin('salesforce');

        // get settings
        $settings = $plugin->getSettings();

        //if not asking for a specific envoriment return the current one
        if ($env == null) {
          $env = $settings->env;
        }

        if ($env == 'sandbox') {
          $tokenId = $settings->tokenIdSandbox;
        } else {
          $tokenId = $settings->tokenIdLive;
        }


        if($tokenId)
        {
            $token = craft()->oauth->getTokenById($tokenId);

            if($token)
            {
                if(craft()->oauth->deleteToken($token))
                {
                  if ($env == 'sandbox') {
                    $settings->tokenIdSandbox = null;
                  } else {
                    $settings->tokenIdLive = null;
                  }


                    craft()->plugins->savePluginSettings($plugin, $settings);

                    return true;
                }
            }
        }

        return false;
    }
}
