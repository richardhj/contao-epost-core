<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2016 Richard Henkenjohann
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */

namespace EPost\Model;


use Contao\Model;
use EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use League\OAuth2\Client\Token\AccessToken as OAuthAccessToken;


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

        \Controller::redirect($this->getAuthorizationUrl());
    }


    /**
     * Authenticate the user and return the AccessToken or false otherwise
     *
     * @return OAuthAccessToken|false
     */
    public function authenticate()
    {
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
                        'clientId'              => sprintf('%s,%s', EPOST_DEV_ID, EPOST_APP_ID),
                        'lif'                   => file_get_contents(EPOST_LIF_PATH),
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
}
