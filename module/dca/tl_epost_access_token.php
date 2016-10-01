<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2016 Richard Henkenjohann
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
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
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'  => [
            'relation' => [
                'type'  => 'belongsTo',
                'table' => \EPost\Model\User::getTable(),
            ],
            'sql'      => "int(10) unsigned NOT NULL default '0'",
        ],
        'data' => [
            'sql' => "text NULL",
        ],
    ],
];
