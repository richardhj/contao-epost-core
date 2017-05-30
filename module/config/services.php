<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2017 Richard Henkenjohann
 *
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */

/** @var Pimple $container */

$container['contao-epost.dev-id'] = 'richardhj';
$container['contao-epost.app-id'] = 'contao_epost_core';
$container['contao-epost.lif']    = $container->share(
    function () {
        return file_get_contents(dirname(__DIR__).'/PROD_richardhj_contao_epost_core.lif');
    }
);
