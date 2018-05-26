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

use Richardhj\EPost\Api\Metadata\DeliveryOptions;
use Richardhj\ContaoEPostCoreBundle\Model\User;

$GLOBALS['TL_LANG']['MSC']['epost']['letterTypes'] = [
    DeliveryOptions::LETTER_TYPE_NORMAL => 'Elektronischer E‑POSTBRIEF',
    DeliveryOptions::LETTER_TYPE_HYBRID => 'Physischer E‑POSTBRIEF',
];

$GLOBALS['TL_LANG']['MSC']['epost']['recipientFields'] = [
    'displayName'   => 'Vor- und Nachname',
    'epostAddress'  => 'E-POSTBRIEF Adresse',
    'company'       => 'Firmenname',
    'salutation'    => 'Anrede',
    'title'         => 'Titel der Person',
    'firstName'     => 'Vorname',
    'lastName'      => 'Nachname',
    'streetName'    => 'Straßenname',
    'houseNumber'   => 'Hausnummer',
    'addressAddOn'  => 'Adresszusatz',
    'postOfficeBox' => 'Postfach',
    'zipCode'       => 'Postleitzahl',
    'city'          => 'Ort',
];

$GLOBALS['TL_LANG']['MSC']['epost']['registeredOptions'] = [
    DeliveryOptions::OPTION_REGISTERED_STANDARD                           => 'Einschreiben (ohne Optionen)',
    DeliveryOptions::OPTION_REGISTERED_SUBMISSION_ONLY                    => 'Einschreiben Einwurf',
    DeliveryOptions::OPTION_REGISTERED_ADDRESSEE_ONLY                     => 'Einschreiben nur mit Option Eigenhändig',
    DeliveryOptions::OPTION_REGISTERED_WITH_RETURN_RECEIPT                => 'Einschreiben nur mit Option Rückschein',
    DeliveryOptions::OPTION_REGISTERED_ADDRESSEE_ONLY_WITH_RETURN_RECEIPT => 'Einschreiben mit Option Eigenhändig und Rückschein',
    DeliveryOptions::OPTION_REGISTERED_NO                                 => 'Standardbrief',
];

$GLOBALS['TL_LANG']['MSC']['epost']['authorizationTypes'] = [
    User::OAUTH2_AUTHORIZATION_CODE_GRANT                  => 'Authorization Code Grant',
    User::OAUTH2_RESOURCE_OWNER_PASSWORD_CREDENTIALS_GRANT => 'Resource Owner Password Credentials Grant',
];
