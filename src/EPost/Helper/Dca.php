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

namespace Richardhj\EPost\Contao\Helper;

use Contao\DataContainer;
use Contao\Date;
use Contao\Encryption;
use Contao\Environment;
use Contao\Input;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Message\AddMessageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use Haste\Util\Url;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Richardhj\EPost\Contao\Model\AccessToken;
use Richardhj\EPost\Contao\Model\User;
use Richardhj\EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Class Dca
 *
 * @package Richardhj\EPost\Contao\Helper
 */
class Dca
{

    /**
     * @param DataContainer $dataContainer
     */
    public function checkCredentials(DataContainer $dataContainer)
    {
        global $container;

        // Prepare authorization redirect
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf(
                    '%s,%s',
                    $container['contao-epost.dev-id'],
                    $container['contao-epost.app-id']
                ),
                'scopes'                => $dataContainer->activeRecord->scopes,
                'lif'                   => $container['contao-epost.lif'],
                'enableTestEnvironment' => $dataContainer->activeRecord->test_environment,
            ]
        );

        try {
            // Try to get an access token using the resource owner password credentials grant.
            $provider->getAccessToken(
                'password',
                [
                    'username' => $dataContainer->activeRecord->username,
                    'password' => Encryption::decrypt($dataContainer->activeRecord->password),
                ]
            );

        } catch (IdentityProviderException $e) {
            $this->getEventDispatcher()->dispatch(
                AddMessageEvent::createError($e->getResponseBody()['error_description'])
            );
        }

        $this->getEventDispatcher()->dispatch(
            AddMessageEvent::createConfirm('Ein Login mit den angegebenen Zugangsdaten war erfolgreich.')
        );
    }


    /**
     * Back end module checking the given AccessToken and redirects to the OAuth provider if necessary
     *
     * @return string empty
     */
    public function handleAuthorization()
    {
        global $container;

        if ('authorization' !== Input::get('key')) {
            return '';
        }

        /** @var User $user */
        $user = User::findByPk(Input::get('id'));

        if (null === $user) {
            $this->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf('E-POST user ID %u does not exist', Input::get('id')),
                    __METHOD__,
                    TL_ERROR
                )
            );
            $this->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
        }

        // This module is for Authorization Code Grant necessary exclusively
        if ($user::OAUTH2_AUTHORIZATION_CODE_GRANT !== $user->authorization) {
            $this->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf('E-POST authorization type "%s" must not be authorized manually', $user->authorization),
                    __METHOD__,
                    TL_ERROR
                )
            );
            $this->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
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
            $this->getEventDispatcher()->dispatch(
                AddMessageEvent::createConfirm(
                    sprintf(
                        'Der Benutzer <em>%s</em> ist authorisiert bis: %s',
                        $user->title,
                        Date::parse(Date::getNumericDatimFormat(), $token->getExpires())
                    )
                )
            );
            $this->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent(Url::removeQueryString(['key']))
            );
        }

        // Prepare authorization redirect
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf(
                    '%s,%s',
                    $container['contao-epost.dev-id'],
                    $container['contao-epost.app-id']
                ),
                'redirectUri'           => Environment::get('base')
                                           . 'system/modules/epost/assets/web/oauth2_redirect.php',
                'scopes'                => trimsplit(' ', $user->scopes),
                'lif'                   => $container['contao-epost.lif'],
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        $authUrl = $provider->getAuthorizationUrl();

        // Save the state for later handling
        $user->oauth_state = $provider->getState();
        $user->save();

        $this->getEventDispatcher()->dispatch(
            ContaoEvents::CONTROLLER_REDIRECT,
            new RedirectEvent($authUrl)
        );

        return '';
    }

    /**
     * @return EventDispatcher
     */
    private function getEventDispatcher()
    {
        return $GLOBALS['container']['event-dispatcher'];
    }
}
