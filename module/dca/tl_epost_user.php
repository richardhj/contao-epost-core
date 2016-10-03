<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2016 Richard Henkenjohann
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */


$table = EPost\Model\User::getTable();


/**
 * DCA config
 */
$GLOBALS['TL_DCA'][$table] = [

    // Config
    'config'                => [
        'dataContainer' => 'Table',
        'sql'           => [
                'keys' => [
                    'id' => 'primary',
                ],
            ],
    ],

    // List
    'list'                  => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'filter,search;limit',
        ],
        'label'             => [
            'fields' => ['title'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG'][$table]['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG'][$table]['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG'][$table]['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG'][$table]['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // MetaPalettes
    'metapalettes'          => [
        'default' => [
            'settings' => [
                'title',
                'authorization',
                'scopes',
                'invalidate_immediate',
                'test_environment',
            ],
        ],
    ],

    // MetaSubSelectPalettes
    'metasubselectpalettes' => [
        'authorization' => [
            EPost\Model\User::OAUTH2_AUTHORIZATION_CODE_GRANT                  => [],
            EPost\Model\User::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT => [
                'username',
                'password',
            ],
        ],
    ],

    // Fields
    'fields'                => [
        'id'                   => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'               => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG'][$table]['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50',
            ],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'authorization'        => [
            'label'     => &$GLOBALS['TL_LANG'][$table]['authorization'],
            'inputType' => 'select',
            'options'   => [
                EPost\Model\User::OAUTH2_AUTHORIZATION_CODE_GRANT,
                EPost\Model\User::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT,
            ],
            'reference' => &$GLOBALS['TL_LANG']['MSC']['epost']['authorizationTypes'],
            'eval'      => [
                'submitOnChange' => true,
                'tl_class'       => 'w50',
                'mandatory'      => true,
            ],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'scopes'               => [
            'label'     => &$GLOBALS['TL_LANG'][$table]['scopes'],
            'inputType' => 'checkbox',
            'options'   => [
                'send_letter',
                'send_hybrid',
                'read_letter',
                'create_letter',
                'delete_letter',
                'safe',
                'register_device',
            ],
            'reference' => &$GLOBALS['TL_LANG'][$table]['scopeOptions'],
            'eval'      => [
                'tl_class'  => '',
                'csv'       => ' ',
                'mandatory' => true,
                'multiple'  => true,
            ],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'username'             => [
            'label'     => &$GLOBALS['TL_LANG'][$table]['username'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50 clr',
            ],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'password'             => [
            'label'         => &$GLOBALS['TL_LANG'][$table]['password'],
            'exclude'       => true,
            'inputType'     => 'text',
            'eval'          => [
                'mandatory'    => true,
                'encrypt'      => true,
                'hideInput'    => true,
                'preserveTags' => true,
                'tl_class'     => 'w50',
            ],
            'load_callback' => function ($value) {
                if (strlen($value)) {
                    return \Encryption::encrypt('*****');
                }

                return $value;
            },
            'save_callback' => function ($value, \DataContainer $dc) {
                if ('*****' === \Encryption::decrypt($value)) {
                    return $dc->activeRecord->password;
                }

                return $value;
            },
            'sql'           => "text NULL",
        ],
        'invalidate_immediate' => [
            'label'     => &$GLOBALS['TL_LANG'][$table]['invalidate_immediate'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12',
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'test_environment'     => [
            'label'     => &$GLOBALS['TL_LANG'][$table]['test_environment'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12',
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'access_token'         => [
            'relation' => [
                'type'  => 'hasOne',
                'table' => \EPost\Model\AccessToken::getTable(),
            ],
            'sql'      => "int(10) unsigned NOT NULL default '0'",
        ],
        'oauth_state'          => [
            'sql' => "text NULL",
        ],
        'redirectBackUrl'      => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
