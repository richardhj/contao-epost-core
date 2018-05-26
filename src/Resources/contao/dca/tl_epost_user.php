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

use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use Richardhj\ContaoEPostCoreBundle\Helper\Dca;
use Richardhj\ContaoEPostCoreBundle\Model\User;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;


/**
 * DCA config
 */
$GLOBALS['TL_DCA']['tl_epost_user'] = [

    // Config
    'config'                => [
        'dataContainer'     => 'Table',
        'onsubmit_callback' => [[Dca::class, 'checkCredentials']],
        'sql'               => [
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
                'label' => &$GLOBALS['TL_LANG']['tl_epost_user']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_epost_user']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_epost_user']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                .'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_epost_user']['show'],
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
            User::OAUTH2_AUTHORIZATION_CODE_GRANT                  => [],
            User::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT => [
                'username',
                'password',
            ],
        ],
    ],

    // Fields
    'fields'                => [
        'id'                   => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'               => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\'',
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_epost_user']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50 clr',
            ],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'authorization'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_epost_user']['authorization'],
            'inputType' => 'select',
            'options'   => [
                User::OAUTH2_AUTHORIZATION_CODE_GRANT,
                User::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT,
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
            'label'     => &$GLOBALS['TL_LANG']['tl_epost_user']['scopes'],
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
            'reference' => &$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions'],
            'eval'      => [
                'tl_class'  => 'clr',
                'csv'       => ' ',
                'mandatory' => true,
                'multiple'  => true,
            ],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'username'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_epost_user']['username'],
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
            'label'         => &$GLOBALS['TL_LANG']['tl_epost_user']['password'],
            'exclude'       => true,
            'inputType'     => 'text',
            'eval'          => [
                'mandatory'    => true,
                'hideInput'    => true,
                'preserveTags' => true,
                'tl_class'     => 'w50',
            ],
            'load_callback' => [
                function ($value) {
                    return empty($value) ? '' : '*****';
                },
            ],
            'save_callback' => [
                function (string $value, \DataContainer $dc) {
                    $keyPath = System::getContainer()->getParameter('kernel.project_dir').'/var/epost-secret.key';
                    try {
                        $key = KeyFactory::loadEncryptionKey($keyPath);
                    } catch (CannotPerformOperation $e) {
                        $key = KeyFactory::generateEncryptionKey();
                        KeyFactory::save($key, $keyPath);
                    }

                    return '*****' === $value ? $dc->activeRecord->password : SymmetricCrypto::encrypt(new HiddenString($value), $key);
                },
            ],
            'sql'           => 'text NULL',
        ],
        'invalidate_immediate' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_epost_user']['invalidate_immediate'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12',
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'test_environment'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_epost_user']['test_environment'],
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
                'table' => 'tl_epost_access_token',
            ],
            'sql'      => "int(10) unsigned NOT NULL default '0'",
        ],
        'oauth_state'          => [
            'sql' => 'text NULL',
        ],
        'redirectBackUrl'      => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
