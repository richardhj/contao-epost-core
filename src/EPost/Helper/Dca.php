<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 *
 * Copyright (c) 2015-2016 Richard Henkenjohann
 *
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */

namespace EPost\Helper;

use EPost\Model\AccessToken;
use EPost\Model\User;
use EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use Haste\Util\Url;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;


/**
 * Class Dca
 * @package EPost\Helper
 */
class Dca
{


    public function checkCredentials(\DataContainer $dataContainer)
    {
        global $container;

        // Prepare authorization redirect
        $provider = new OAuthProvider(
            [
                'clientId' => sprintf(
                    '%s,%s',
                    $container['contao-epost.dev-id'],
                    $container['contao-epost.app-id']
                ),
                'scopes' => $dataContainer->activeRecord->scopes,
                'lif' => file_get_contents(EPOST_LIF_PATH),
                'enableTestEnvironment' => $dataContainer->activeRecord->test_environment,
            ]
        );

        try {
            // Try to get an access token using the resource owner password credentials grant.
            $provider->getAccessToken(
                'password',
                [
                    'username' => $dataContainer->activeRecord->username,
                    'password' => \Encryption::decrypt($dataContainer->activeRecord->password),
                ]
            );

        } catch (IdentityProviderException $e) {
            \Message::addError($e->getResponseBody()['error_description']);
        }

        \Message::addConfirmation('Ein Login mit den angegebenen Zugangsdaten war erfolgreich.');
    }


    /**
     * Back end module checking the given AccessToken and redirects to the OAuth provider if necessary
     *
     * @return string empty
     */
    public function handleAuthorization()
    {
        global $container;

        if ('authorization' !== \Input::get('key')) {
            return '';
        }

        /** @var User $user */
        $user = User::findByPk(\Input::get('id'));

        if (null === $user) {
            \System::log(sprintf('E-POST user ID %u does not exist', \Input::get('id')), __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        // This module is for Authorization Code Grant necessary exclusively
        if ($user::OAUTH2_AUTHORIZATION_CODE_GRANT !== $user->authorization) {
            \System::log(
                sprintf('E-POST authorization type "%s" must not be authorized manually', $user->authorization),
                __METHOD__,
                TL_ERROR
            );
            \Controller::redirect('contao/main.php?act=error');
        }

        /** @var AccessToken $accessToken */
        $accessToken = $user->getRelated('access_token');

        // Make sure to relate at least an empty AccessToken instance
        if (null === $accessToken) {
            $accessToken = new AccessToken();
            $accessToken->pid = $user->id;
            $accessToken->save();
            $user->access_token = $accessToken->id;
            $user->save();
        }

        $token = $accessToken->createAccessToken();

        // Token is valid
        if (null !== $token && !$token->hasExpired()) {
            \Message::addConfirmation(
                sprintf(
                    'Der Benutzer <em>%s</em> ist authorisiert bis: %s',
                    $user->title,
                    \Date::parse(\Date::getNumericDatimFormat(), $token->getExpires())
                )
            );
            \Controller::redirect(Url::removeQueryString(['key']));
        }

        // Prepare authorization redirect
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf(
                    '%s,%s',
                    $container['contao-epost.dev-id'],
                    $container['contao-epost.app-id']
                ),
                'redirectUri'           => \Environment::get('base')
                                           .'system/modules/epost/assets/web/oauth2_redirect.php',
                'scopes'                => trimsplit(' ', $user->scopes),
                'lif'                   => file_get_contents(EPOST_LIF_PATH),
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        $authUrl = $provider->getAuthorizationUrl();

        // Save the state for later handling
        $user->oauth_state = $provider->getState();
        $user->save();

        \Controller::redirect($authUrl);

        return '';
    }
}
