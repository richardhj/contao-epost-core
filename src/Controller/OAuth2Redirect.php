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

namespace Richardhj\ContaoEPostCoreBundle\Controller;


use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Message;
use Contao\System;
use Richardhj\ContaoEPostCoreBundle\Model\AccessToken;
use Richardhj\EPost\OAuth2\Client\Provider as OAuthProvider;
use Richardhj\ContaoEPostCoreBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OAuth2Redirect extends Controller
{

    /**
     * @param Request $request
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function __invoke(Request $request)
    {
        // Find a user by given state
        $user = User::findBy('oauth_state', $request->query->get('state'));
        if (null === $user) {
            System::log(
                sprintf('A user was not found for the given state (%s)', $request->query->get('state')),
                __METHOD__,
                TL_ERROR
            );
            throw new RedirectResponseException('/contao?act=error');
        }

        // Initiate OAuth provider. Scopes and redirectUri must be the same
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', $this->getParameter('contao_epost.dev_id'), $this->getParameter('contao_epost.app_id')),
                'redirectUri'           => \Environment::get('base').\Environment::get('scriptName'),
                'scopes'                => trimsplit(' ', $user->scopes),
                'lif'                   => $this->getParameter('contao_epost.lif'),
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        if (!empty($request->query->get('error'))) {

            // The user did not granted the access
            if ('access_denied' === $request->query->get('error')) {
                Message::addError(sprintf('Der Benutzer <em>%s</em> hat den Zugriff verweigert', $user->title));
                throw new RedirectResponseException('contao?do=epost_user');
            }

            // Miscellaneous error
            System::log(
                sprintf('Error occurred for authorization: %s', $request->query->get('error')),
                __METHOD__,
                TL_ERROR
            );

            throw new RedirectResponseException('/contao?act=error');

        }

        if (empty($request->query->get('code'))) {

            System::log('Malicious authorization redirect', __METHOD__, TL_ERROR);

            throw new RedirectResponseException('/contao?act=error');
        }

        try {
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $request->query->get('code'),
                ]
            );

            // Update and persist the token
            /** @var AccessToken $accessToken */
            $accessToken = $user->getRelated('access_token');
            $accessToken->saveAccessToken($token);

            // Redirect back to the corresponding back end module OR use the url prescribed
            $redirectUri = ('' !== $user->redirectBackUrl) ? $user->redirectBackUrl
                : '/contao?do=epost_user&key=authorization&id='.$user->id.'&rt='.REQUEST_TOKEN;

            // Reset temp data
            $user->oauth_state     = '';
            $user->redirectBackUrl = '';
            $user->save();

            System::log(
                sprintf('An AccessToken for user ID %u was fetched and saved successfully', $user->id),
                __METHOD__,
                TL_ERROR
            );
            throw new RedirectResponseException($redirectUri);

        } catch (IdentityProviderException $e) {
            System::log(
                sprintf('Error occurred for authorization: %s', $e->getResponseBody()['error_description']),
                __METHOD__,
                TL_ERROR
            );

            throw new RedirectResponseException('/contao?act=error');
        }
    }
}
