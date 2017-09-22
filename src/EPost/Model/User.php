<?php

/**
 * This file is part of richardhj/contao-epost-core.
 *
 * Copyright (c) 2015-2017 Richard Henkenjohann
 *
 * @package   richardhj/contao-epost-core
 * @author    Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright 2015-2017 Richard Henkenjohann
 * @license   https://github.com/richardhj/contao-epost-core/blob/master/LICENSE
 */

namespace Richardhj\EPost\Contao\Model;


use Contao\Model;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use League\OAuth2\Client\Token\AccessToken as OAuthAccessToken;
use Richardhj\EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Class User
 * @property string $title
 * @property string $username
 * @property string $password Encrypted
 * @property string $authorization
 * @property mixed  $scopes
 * @property bool   $invalidate_immediate
 * @property bool   $test_environment
 * @property int    $access_token
 * @property string $redirectBackUrl
 * @package EPost\Model
 */
class User extends Model
{

    /**
     * OAuth-2.0 authorization specifications
     */
    const OAUTH2_AUTHORIZATION_CODE_GRANT = 'authorization_code_grant';


    /**
     * OAuth-2.0 authorization specifications
     */
    const OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT = 'resource_owner_password_credentials_grant';


    /**
     * @var string
     */
    protected static $strTable = 'tl_epost_user';


    /**
     * @var OAuthAccessToken
     */
    protected $token;


    /**
     * Url to the authorization back end module
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return 'contao/main.php?do=epost_user&key=authorization&id='.$this->id.'&rt='.REQUEST_TOKEN;
    }


    /**
     * Redirect to the authorization back end module preserving a prescribed redirect back url
     *
     * @param string $redirectBackUrl
     */
    public function redirectForAuthorization($redirectBackUrl = '')
    {
        if ('' !== $redirectBackUrl) {
            $this->redirectBackUrl = $redirectBackUrl;
            $this->save();
        }

        $this->getEventDispatcher()->dispatch(
            ContaoEvents::CONTROLLER_REDIRECT,
            new RedirectEvent($this->getAuthorizationUrl())
        );
    }


    /**
     * Authenticate the user and return the AccessToken or false otherwise
     *
     * @return OAuthAccessToken|false
     */
    public function authenticate()
    {
        global $container;

        switch ($this->authorization) {
            case User::OAUTH2_AUTHORIZATION_CODE_GRANT:

                /** @var AccessToken $accessToken */
                $accessToken = $this->getRelated('access_token');

                if (null === $accessToken) {
                    return false;
                }

                $this->token = $accessToken->createAccessToken();

                if (null === $this->token || $this->token->hasExpired()) {
                    //todo
                    return false;
                }

                break;

            case User::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT:
                $provider = new OAuthProvider(
                    [
                        'scopes'                => ['create_letter', 'send_hybrid'],
                        'clientId'              => sprintf(
                            '%s,%s',
                            $container['contao-epost.dev-id'],
                            $container['contao-epost.app-id']
                        ),
                        'lif'                   => $container['contao-epost.lif'],
                        'enableTestEnvironment' => $this->test_environment,
                    ]
                );

                $this->token = $provider->getAccessToken(
                    'password',
                    [
                        'username' => $this->username,
                        'password' => \Encryption::decrypt($this->password),
                    ]
                );

                break;

            default:
                throw new \InvalidArgumentException(sprintf('Unknown authorization "%s"', $this->authorization));
                break;
        }

        return $this->token;
    }


    /**
     * Invalidate the access token
     */
    public function __destruct()
    {
        if ('' !== $this->access_token && $this->invalidate_immediate) {
//            $this->logout();
        }
    }

    /**
     * @return EventDispatcher
     */
    private function getEventDispatcher()
    {
        return $GLOBALS['container']['event-dispatcher'];
    }
}
