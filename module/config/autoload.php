<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register namespaces
 */
NamespaceClassLoader::add('EPost', 'system/modules/epost/library');
NamespaceClassLoader::add('NotificationCenter', 'system/modules/epost/library');


/**
 * Register the templates
 */
TemplateLoader::addFiles(
    array
    (
        'epost_coverletter_default' => 'system/modules/epost/templates/document',
    )
);
