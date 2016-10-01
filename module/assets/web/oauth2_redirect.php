<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 *
 * Copyright (c) 2015-2016 Richard Henkenjohann
 *
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */


use EPost\Model\AccessToken;
use EPost\Model\User;
use EPost\OAuth2\Client\Provider\EPost as OAuthProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;


define('TL_MODE', 'FE');
require __DIR__.'/../../../../initialize.php';

class oauth2_redirect
{

    public function run()
    {

        $scopes = ['create_letter', 'send_hybrid'];
        $user = User::findBy('oauth_state', \Input::get('state'));

        if (null === $user) {
            exit ('Invalid state');
        }

        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', EPOST_DEV_ID, EPOST_APP_ID),
                'redirectUri'           => \Environment::get('base').\Environment::get('scriptName'),
                'scopes'                => $scopes,
                'lif'                   => file_get_contents(EPOST_LIF_PATH),
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        if (!empty(\Input::get('error'))) {

            if ('access_denied' === \Input::get('error')) {
                exit('access denied');
            }

            // Got an error, probably user denied access
            exit('Got error: '.$_GET['error']);

        } elseif (empty(\Input::get('code'))) {

            exit('no code');

        } else {

            try {
                // Try to get an access token (using the authorization code grant)
                $token = $provider->getAccessToken(
                    'authorization_code',
                    [
                        'code' => \Input::get('code'),
                    ]
                );

                /** @var AccessToken $accessToken */
                $accessToken = $user->getRelated('access_token');
                $accessToken->saveAccessToken($token);

                $redirectUri = ('' !== $user->redirectBackUrl) ? $user->redirectBackUrl : 'contao/main.php?do=epost_user&key=authorization&id='.$user->id.'&rt='.REQUEST_TOKEN;

                $user->oauth_state = '';
                $user->redirectBackUrl = '';
                $user->save();

                Controller::redirect($redirectUri);

            } catch (IdentityProviderException $e) {
                dump($e->getResponseBody());
            }
        }
    }
}

// Run the controller
$oath2redirect = new oauth2_redirect;
$oath2redirect->run();
