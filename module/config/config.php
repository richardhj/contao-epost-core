<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2016 Richard Henkenjohann
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */

/**
 * Back end modules
 */
array_insert(
    $GLOBALS['BE_MOD'],
    2,
    [
        'epost' => [
            'epost_user' => [
                'tables'        => ['tl_epost_user'],
                'icon'          => 'assets/epost/images/users.png',
                'nested'        => 'epost_config',
                'authorization' => ['EPost\Helper\Dca', 'handleAuthorization'],
            ],
        ],
    ]
);


$GLOBALS['BE_MOD']['epost']['epost_config']['icon'] = 'assets/epost/images/config.png';


/**
 * E-POSTBUSINESS API Configuration
 */
define('EPOST_DEV_ID', 'anonymous225');
define('EPOST_APP_ID', 'Testapp225');
define('EPOST_LIF_PATH', dirname(__DIR__).'/assets/'.EPOST_DEV_ID.'_'.EPOST_APP_ID.'.lif');


/**
 * Models
 */
$GLOBALS['TL_MODELS'][EPost\Model\User::getTable()] = 'EPost\Model\User';
$GLOBALS['TL_MODELS'][EPost\Model\AccessToken::getTable()] = 'EPost\Model\AccessToken';
