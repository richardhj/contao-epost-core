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
                'tables'        => [EPost\Model\User::getTable()],
                'icon'          => 'assets/epost/core/images/users.png',
                'nested'        => 'epost_components:config',
                'authorization' => ['EPost\Helper\Dca', 'handleAuthorization'],
            ],
        ],
    ]
);

$GLOBALS['BE_MOD']['epost']['epost_components']['icon'] = 'assets/epost/core/images/config.png';


/**
 * E-POSTBUSINESS API configuration
 */
define('EPOST_DEV_ID', 'richardhj');
define('EPOST_APP_ID', 'contao_epost_core');
define('EPOST_LIF_PATH', dirname(__DIR__).'/assets/'.EPOST_DEV_ID.'_'.EPOST_APP_ID.'.lif');


/**
 * Models
 */
$GLOBALS['TL_MODELS'][EPost\Model\User::getTable()] = 'EPost\Model\User';
$GLOBALS['TL_MODELS'][EPost\Model\AccessToken::getTable()] = 'EPost\Model\AccessToken';
