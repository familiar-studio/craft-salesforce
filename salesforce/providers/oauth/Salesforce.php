<?php
/**
 * @link      https://dukt.net/craft/slack/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/slack/docs/license
 */

namespace Dukt\OAuth\Providers;

use Craft\UrlHelper;
use Craft\Oauth_TokenModel;

class Salesforce extends BaseProvider
{
    // Public Methods
    // =========================================================================

    /* default scopes (minimum scope for getting user details) */

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return 'Salesforce';
    }

    /**
     * Get Icon URL
     *
     * @return string
     */
    public function getIconUrl()
    {
        return UrlHelper::getResourceUrl('salesforce/icon.svg');

    }

    /**
     * Get OAuth Version
     *
     * @return int
     */
    public function getOauthVersion()
    {
        return 2;
    }

    /**
     * Get API Manager URL
     *
     * @return string
     */
    public function getManagerUrl()
    {
        return 'https://na34.salesforce.com/02u?retURL=%2Fui%2Fsetup%2FSetup%3Fsetupid%3DDevTools&setupid=TabSet';
    }

    /**
     * Get Scope Docs URL
     *
     * @return string
     */
    public function getScopeDocsUrl()
    {
        return 'https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/';
    }

    /**
     * Create Provider
     */
    public function createProvider()
    {
        $config = [
            'clientId' => $this->providerInfos->clientId,
            'clientSecret' => $this->providerInfos->clientSecret,
            'redirectUri' => $this->getRedirectUri(),
        ];

        return new \Dukt\OAuth\OAuth2\Client\Provider\Salesforce($config);
    }

}
