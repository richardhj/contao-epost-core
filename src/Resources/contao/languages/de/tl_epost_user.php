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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_epost_user']['settings_legend'] = 'Eintellungen';


/**
 * Operations
 */
$GLOBALS['TL_LANG']['tl_epost_user']['new'][0]    = 'Neuer Benutzer';
$GLOBALS['TL_LANG']['tl_epost_user']['new'][1]    = 'Einen neuen Benutzer erstellen';
$GLOBALS['TL_LANG']['tl_epost_user']['edit'][0]   = 'Benutzer bearbeiten';
$GLOBALS['TL_LANG']['tl_epost_user']['edit'][1]   = 'Benutzer ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_epost_user']['copy'][0]   = 'Benutzer duplizieren';
$GLOBALS['TL_LANG']['tl_epost_user']['copy'][1]   = 'Benutzer ID %s duplizieren';
$GLOBALS['TL_LANG']['tl_epost_user']['delete'][0] = 'Benutzer löschen';
$GLOBALS['TL_LANG']['tl_epost_user']['delete'][1] = 'Benutzer ID %s löschen';
$GLOBALS['TL_LANG']['tl_epost_user']['show'][0]   = 'Details';
$GLOBALS['TL_LANG']['tl_epost_user']['show'][1]   = 'Die Details des Benutzers ID %s anzeigen';


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_epost_user']['title'][0]                = 'Titel';
$GLOBALS['TL_LANG']['tl_epost_user']['title'][1]                = 'Geben Sie einen intern verwendeten Namen ein.';
$GLOBALS['TL_LANG']['tl_epost_user']['authorization'][0]        = 'Authorisierungs-Typ';
$GLOBALS['TL_LANG']['tl_epost_user']['authorization'][1]        = 'Wählen Sie das Verfahren zur Authentifizierung aus.';
$GLOBALS['TL_LANG']['tl_epost_user']['username'][0]             = 'Benutzername';
$GLOBALS['TL_LANG']['tl_epost_user']['username'][1]             = 'Geben Sie den Benutzernamen für die Anmeldung am E-POST Portal ein.';
$GLOBALS['TL_LANG']['tl_epost_user']['password'][0]             = 'Passwort';
$GLOBALS['TL_LANG']['tl_epost_user']['password'][1]             = 'Geben Sie das Passwort für die Anmeldung am E-POST Portal ein.';
$GLOBALS['TL_LANG']['tl_epost_user']['scopes'][0]               = 'Scopes';
$GLOBALS['TL_LANG']['tl_epost_user']['scopes'][1]               = 'Der Scope legt fest, inwieweit der Client auf die Ressource(n) des Nutzers zugreift.';
$GLOBALS['TL_LANG']['tl_epost_user']['invalidate_immediate'][0] = 'Sofort abmelden';
$GLOBALS['TL_LANG']['tl_epost_user']['invalidate_immediate'][1] = 'Die Gültigkeit der Authorisierung unmittelbar nach der Benutzung widerrufen.';
$GLOBALS['TL_LANG']['tl_epost_user']['test_environment'][0]     = 'Test-Umgebung';
$GLOBALS['TL_LANG']['tl_epost_user']['test_environment'][1]     = 'Aktivieren Sie die Test-Umgebung (für Entwickler).';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['send_letter']     = 'Senden von elektronischen Briefen';
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['send_hybrid']     = 'Senden von postalischen Briefen';
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['read_letter']     = 'Anzeigen von Entwürfen';
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['create_letter']   = 'Anlegen von Entwürfen';
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['delete_letter']   = 'Löschen von Entwürfen';
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['safe']            = 'Zugriff auf die E-POST CLOUD';
$GLOBALS['TL_LANG']['tl_epost_user']['scopeOptions']['register_device'] = 'Registrieren von Geräten';
