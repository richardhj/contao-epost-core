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

/**
 * Table tl_epost_access_token
 */
$GLOBALS['TL_DCA']['tl_epost_access_token'] = [

    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id'   => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid'  => [
            'relation' => [
                'type'  => 'belongsTo',
                'table' => 'tl_epost_user',
            ],
            'sql'      => "int(10) unsigned NOT NULL default '0'",
        ],
        'data' => [
            'sql' => 'text NULL',
        ],
    ],
];
