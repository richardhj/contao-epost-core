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

use Richardhj\ContaoEPostCoreBundle\Helper\Dca;
use Richardhj\ContaoEPostCoreBundle\Model\AccessToken;
use Richardhj\ContaoEPostCoreBundle\Model\User;

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
                'icon'          => 'bundles/richardhjcontaoepostcore/images/users.png',
                'nested'        => 'epost_components:config',
                'authorization' => [Dca::class, 'handleAuthorization'],
            ],
        ],
    ]
);

$GLOBALS['BE_MOD']['epost']['epost_components']['icon'] = 'bundles/richardhjcontaoepostcore/images/config.png';


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_epost_user']         = User::class;
$GLOBALS['TL_MODELS']['tl_epost_access_token'] = AccessToken::class;
