<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 *
 * Copyright (c) 2015-2016 Richard Henkenjohann
 *
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */


use EPost\Api\Metadata\DeliveryOptions;


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
