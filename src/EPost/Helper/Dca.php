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


class Dca
{

    public function handleAuthorization()
    {
        if ('authorization' !== \Input::get('key')) {
            return '';
        }

        $user = User::findByPk(\Input::get('id'));

        if (null === $user) {
            \System::log(sprintf('E-POST user ID %u does not exist', \Input::get('id')), __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

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

        if (null === $accessToken) {
            $accessToken = new AccessToken();
            $accessToken->pid = $user->id;
            $accessToken->save();
            $user->access_token = $accessToken->id;
            $user->save();
        }

        $token = $accessToken->createAccessToken();


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

        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', EPOST_DEV_ID, EPOST_APP_ID),
                'redirectUri'           => \Environment::get('base')
                    .'system/modules/epost/assets/web/oauth2_redirect.php',
                'scopes'                => trimsplit(' ', $user->scopes),
                'lif'                   => file_get_contents(EPOST_LIF_PATH),
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        $authUrl = $provider->getAuthorizationUrl();

        $user->oauth_state = $provider->getState();
        $user->save();

        \Controller::redirect($authUrl);

        return '';
    }
}
