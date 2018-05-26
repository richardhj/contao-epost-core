<?php

/**
 * This file is part of richardhj/contao-epost-core.
 *
 * Copyright (c) 2015-2018 Richard Henkenjohann
 *
 * @package   richardhj/contao-epost-core
 * @author    Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright 2015-2018 Richard Henkenjohann
 * @license   https://github.com/richardhj/contao-epost-core/blob/master/LICENSE
 */

namespace Richardhj\ContaoEPostCoreBundle\Model;


use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Model;
use Contao\System;
use League\OAuth2\Client\Token\AccessToken as OAuthAccessToken;
use ParagonIE\Halite\KeyFactory;
use Richardhj\EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;


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
    public const OAUTH2_AUTHORIZATION_CODE_GRANT = 'authorization_code_grant';

    /**
     * OAuth-2.0 authorization specifications
     */
    public const OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT = 'resource_owner_password_credentials_grant';

    /**
     * @var string
     */
    protected static $strTable = 'tl_epost_user';

    /**
     * @var OAuthAccessToken
     */
    private $token;

    /**
     * Url to the authorization back end module
     *
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        return 'contao?do=epost_user&key=authorization&id='.$this->id.'&rt='.REQUEST_TOKEN;
    }

    /**
     * Redirect to the authorization back end module preserving a prescribed redirect back url
     *
     * @param string $redirectBackUrl
     *
     * @throws RedirectResponseException
     */
    public function redirectForAuthorization(string $redirectBackUrl = ''): void
    {
        if ('' !== $redirectBackUrl) {
            $this->redirectBackUrl = $redirectBackUrl;
            $this->save();
        }

        throw new RedirectResponseException($this->getAuthorizationUrl());
    }


    /**
     * Authenticate the user and return the AccessToken or false otherwise
     *
     * @return OAuthAccessToken|false
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function authenticate()
    {
        switch ($this->authorization) {
            case self::OAUTH2_AUTHORIZATION_CODE_GRANT:

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

            case self::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT:
                $provider = new OAuthProvider(
                    [
                        'scopes'                => ['create_letter', 'send_hybrid'],
                        'clientId'              => sprintf('%s,%s', System::getContainer()->getParameter('contao_epost.dev_id'), System::getContainer()->getParameter('contao_epost.app_id')),
                        'lif'                   => System::getContainer()->getParameter('contao_epost.lif'),
                        'enableTestEnvironment' => $this->test_environment,
                    ]
                );


                $keyPath = System::getContainer()->getParameter('kernel.project_dir').'/var/epost/secret.key';
                $encryptionKey = KeyFactory::loadEncryptionKey($keyPath);

                $this->token = $provider->getAccessToken(
                    'password',
                    [
                        'username' => $this->username,
                        'password' => SymmetricCrypto::decrypt($this->password, $encryptionKey),
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
