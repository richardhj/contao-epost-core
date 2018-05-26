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

namespace Richardhj\ContaoEPostCoreBundle\Helper;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Haste\Util\Url;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use Richardhj\ContaoEPostCoreBundle\Model\AccessToken;
use Richardhj\ContaoEPostCoreBundle\Model\User;
use Richardhj\EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;


/**
 * Class Dca
 *
 * @package Richardhj\EPost\Contao\Helper
 */
class Dca
{

    private $epostDevId;

    private $epostAppId;

    private $epostLif;

    private $rootDir;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Dca constructor.
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->epostDevId = System::getContainer()->getParameter('contao_epost.dev_id');
        $this->epostAppId = System::getContainer()->getParameter('contao_epost.app_id');
        $this->epostLif   = System::getContainer()->getParameter('contao_epost.lif');
        $this->rootDir    = System::getContainer()->getParameter('kernel.project_dir');
        $this->router     = System::getContainer()->get('router');
    }

    /**
     * @param DataContainer $dataContainer
     *
     * @throws CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function checkCredentials(DataContainer $dataContainer): void
    {
        if ($dataContainer->activeRecord->authentication === User::OAUTH2_AUTHORIZATION_CODE_GRANT) {
            return;
        }

        // Prepare authorization redirect
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', $this->epostDevId, $this->epostAppId),
                'scopes'                => $dataContainer->activeRecord->scopes,
                'lif'                   => $this->epostLif,
                'enableTestEnvironment' => $dataContainer->activeRecord->test_environment,
            ]
        );

        try {
            // Try to get an access token using the resource owner password credentials grant.
            $provider->getAccessToken(
                'password',
                [
                    'username' => $dataContainer->activeRecord->username,
                    'password' => SymmetricCrypto::decrypt(
                        $dataContainer->activeRecord->password,
                        $this->getEncryptionKey()
                    )->getString(),
                ]
            );

        } catch (IdentityProviderException $e) {
            Message::addError($e->getResponseBody()['error_description']);

            return;
        }

        Message::addConfirmation('Ein Login mit den angegebenen Zugangsdaten war erfolgreich.');
    }


    /**
     * Back end module checking the given AccessToken and redirects to the OAuth provider if necessary
     *
     * @return string empty
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Contao\CoreBundle\Exception\RedirectResponseException
     * @throws CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Exception
     */
    public function handleAuthorization(): string
    {
        if ('authorization' !== Input::get('key')) {
            return '';
        }

        /** @var User $user */
        $user = User::findByPk(Input::get('id'));

        if (null === $user) {
            System::log(
                sprintf('E-POST user ID %u does not exist', Input::get('id')),
                __METHOD__,
                TL_ERROR
            );

            throw new RedirectResponseException('contao?act=error');
        }

        // This module is for Authorization Code Grant necessary exclusively
        if ($user::OAUTH2_AUTHORIZATION_CODE_GRANT !== $user->authorization) {
            System::log(
                sprintf('E-POST authorization type "%s" must not be authorized manually', $user->authorization),
                __METHOD__,
                TL_ERROR
            );

            throw new RedirectResponseException('contao?act=error');
        }

        /** @var AccessToken $accessToken */
        $accessToken = $user->getRelated('access_token');

        // Make sure to relate at least an empty AccessToken instance
        if (null === $accessToken) {
            $accessToken      = new AccessToken();
            $accessToken->pid = $user->id;
            $accessToken->save();
            $user->access_token = $accessToken->id;
            $user->save();
        }

        $token = $accessToken->createAccessToken();

        // Token is valid
        if (null !== $token && !$token->hasExpired()) {
            Message::addConfirmation(
                sprintf(
                    'Der Benutzer <em>%s</em> ist authorisiert bis: %s',
                    $user->title,
                    Date::parse(Date::getNumericDatimFormat(), $token->getExpires())
                )
            );

            throw new RedirectResponseException(Url::removeQueryString(['key']));
        }

        // Prepare authorization redirect
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', $this->epostDevId, $this->epostAppId),
                'redirectUri'           => $this->router->generate(
                    'richardhj.contao_epost_core.oauth2_redirect',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'scopes'                => trimsplit(' ', $user->scopes),
                'lif'                   => $this->epostLif,
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        $authUrl = $provider->getAuthorizationUrl();

        // Save the state for later handling
        $user->oauth_state = $provider->getState();
        $user->save();

        throw new RedirectResponseException($authUrl);
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     */
    private function getEncryptionKey(): EncryptionKey
    {
        $keyPath = $this->rootDir.'/var/epost-secret.key';
        try {
            $key = KeyFactory::loadEncryptionKey($keyPath);
        } catch (CannotPerformOperation $e) {
            $key = KeyFactory::generateEncryptionKey();
            KeyFactory::save($key, $keyPath);
        }

        return $key;
    }
}
