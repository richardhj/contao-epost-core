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


class Dca
{

    public function handleAuthorization()
    {
        if ('authorization' !== \Input::get('key')) {
            return '';
        }

        $scopes = ['create_letter', 'send_hybrid'];

        $user = User::findByPk(\Input::get('id'));

        if (null === $user) {
            return 'User not found';
        }

        if ($user::OAUTH2_AUTHORIZATION_CODE_GRANT !== $user->authorization) {
            return 'Wrong authorization type';
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
            return 'Der User ist authorisiert bis: '.\Date::parse(\Date::getNumericDatimFormat(), $token->getExpires());
        }

        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', EPOST_DEV_ID, EPOST_APP_ID),
                'redirectUri'           => \Environment::get(
                        'base'
                    ).'system/modules/epost/assets/web/oauth2_redirect.php',
                'scopes'                => $scopes,
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
