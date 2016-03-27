<?php
/**
 * @link      https://dukt.net/craft/slack/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/slack/docs/license
 */

namespace Dukt\OAuth\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Entity\User;

class Salesforce extends AbstractProvider
{
    // Public Methods
    // =========================================================================

    public function urlAuthorize()
    {
        return 'https://login.salesforce.com/services/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://login.salesforce.com/services/oauth2/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://slack.com/api/api.test?access_token='.$token;
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;
        $user->uid = substr($response->uri, strrpos($response->uri, "/") + 1);
        $user->name = $response->name;
        return $user;
    }


}
