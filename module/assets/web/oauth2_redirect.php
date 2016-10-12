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


$dir = dirname(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : __FILE__);

while ($dir && $dir != '.' && $dir != '/' && !is_file($dir.'/system/initialize.php')) {
    $dir = dirname($dir);
}

define('TL_MODE', 'BE');
/** @noinspection PhpIncludeInspection */
require($dir.'/system/initialize.php');


/**
 * Class oauth2_redirect
 */
class oauth2_redirect
{

    /**
     * Handle a OAuth redirect
     */
    public function run()
    {
        // Find a user by given state
        $user = User::findBy('oauth_state', \Input::get('state'));

        if (null === $user) {
            \System::log(
                sprintf('A user was not found for the given state (%s)', \Input::get('state')),
                __METHOD__,
                TL_ERROR
            );
            \Controller::redirect('contao/main.php?act=error');
        }

        // Initiate OAuth provider. Scopes and redirectUri must be the same
        $provider = new OAuthProvider(
            [
                'clientId'              => sprintf('%s,%s', EPOST_DEV_ID, EPOST_APP_ID),
                'redirectUri'           => \Environment::get('base').\Environment::get('scriptName'),
                'scopes'                => trimsplit(' ', $user->scopes),
                'lif'                   => file_get_contents(EPOST_LIF_PATH),
                'enableTestEnvironment' => $user->test_environment,
            ]
        );

        if (!empty(\Input::get('error'))) {

            // The user did not granted the access
            if ('access_denied' === \Input::get('error')) {
                \Message::addError(sprintf('Der Benutzer <em>%s</em> hat den Zugriff verweigert', $user->title));
                \Controller::redirect('contao/main.php?do=epost_user');
            }

            // Miscellaneous error
            \System::log(
                sprintf('Error occurred for authorization: %s', \Input::get('error')),
                __METHOD__,
                TL_ERROR
            );
            \Controller::redirect('contao/main.php?act=error');

        } elseif (empty(\Input::get('code'))) {

            \System::log('Malicious authorization redirect', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');

        } else {

            try {
                // Try to get an access token (using the authorization code grant)
                $token = $provider->getAccessToken(
                    'authorization_code',
                    [
                        'code' => \Input::get('code'),
                    ]
                );

                // Update and persist the token
                /** @var AccessToken $accessToken */
                $accessToken = $user->getRelated('access_token');
                $accessToken->saveAccessToken($token);

                // Redirect back to the corresponding back end module OR use the url prescribed
                $redirectUri = ('' !== $user->redirectBackUrl) ? $user->redirectBackUrl : 'contao/main.php?do=epost_user&key=authorization&id='.$user->id.'&rt='.REQUEST_TOKEN;

                // Reset temp data
                $user->oauth_state = '';
                $user->redirectBackUrl = '';
                $user->save();

                \System::log(
                    sprintf('An AccessToken for user ID %u was fetched and saved successfully', $user->id),
                    __METHOD__,
                    TL_ERROR
                );
                \Controller::redirect($redirectUri);

            } catch (IdentityProviderException $e) {
                \System::log(
                    sprintf('Error occurred for authorization: %s', $e->getResponseBody()['error_description']),
                    __METHOD__,
                    TL_ERROR
                );
                \Controller::redirect('contao/main.php?act=error');
            }
        }
    }
}

// Run the controller
$oath2redirect = new oauth2_redirect;
$oath2redirect->run();
