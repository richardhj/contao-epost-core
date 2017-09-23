<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2016 Richard Henkenjohann
 *
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
                'tables'        => [Richardhj\EPost\Contao\Model\User::getTable()],
                'icon'          => 'assets/epost/core/images/users.png',
                'nested'        => 'epost_components:config',
                'authorization' => ['EPost\Helper\Dca', 'handleAuthorization'],
            ],
        ],
    ]
);

$GLOBALS['BE_MOD']['epost']['epost_components']['icon'] = 'assets/epost/core/images/config.png';


/**
 * Models
 */
$GLOBALS['TL_MODELS'][Richardhj\EPost\Contao\Model\User::getTable()]        = 'EPost\Model\User';
$GLOBALS['TL_MODELS'][Richardhj\EPost\Contao\Model\AccessToken::getTable()] = 'EPost\Model\AccessToken';
