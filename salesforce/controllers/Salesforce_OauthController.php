<?php
/**
 * @link      https://dukt.net/craft/twitter/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/twitter/docs/license
 */

namespace Craft;

/**
 * Twitter OAuth controller
 */
class Salesforce_OauthController extends BaseController
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $handle = 'salesforce';

    // Public Methods
    // =========================================================================

    /**
     * Connect
     *
     * @return null
     */
    public function actionConnect()
    {

      $referer = craft()->httpSession->get('salesforce.referer');

      if (!$referer)
      {
          $referer = craft()->request->getUrlReferrer();

          craft()->httpSession->add('salesforce.referer', $referer);

      }

        $env  = craft()->request->getParam('env');

        if ($env == 'sandbox') {
          $this->handle = 'salesforceSandbox';
        }

        if ($response = craft()->oauth->connect(array(
            'plugin'   => 'salesforce',
            'provider' => $this->handle
        )))
        {

            if ($response['success'])
            {
                // token
                $token = $response['token'];


                //$token['endOfLife'] = $token['issued_at']+ 3600;


                // save token
                craft()->salesforce_oauth->saveToken($token, $env);

                // session notice
                craft()->userSession->setNotice(Craft::t("Connected to Salesforce."));
            }
            else
            {
                // session error
                craft()->userSession->setError(Craft::t($response['errorMsg']));
            }
        }
        else
        {
            // session error
            craft()->userSession->setError(Craft::t("Couldn’t connect"));
        }

        // OAuth Step 5

        // redirect

        craft()->httpSession->remove('twitter.referer');

        $this->redirect($referer);
    }

    /**
     * Disconnect
     *
     * @return null
     */
    public function actionDisconnect()
    {
      $env  = craft()->request->getParam('env');

      if (craft()->salesforce_oauth->deleteToken($env))
      {
          craft()->userSession->setNotice(Craft::t("Disconnected from Salesforce."));
      }
      else
      {
          craft()->userSession->setError(Craft::t("Couldn’t disconnect from Salesforce"));
      }

      // redirect
      $redirect = craft()->request->getUrlReferrer();
      $this->redirect($redirect);
    }
}
