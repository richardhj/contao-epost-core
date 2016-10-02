<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 *
 * Copyright (c) 2015-2016 Richard Henkenjohann
 *
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */


$table = EPost\Model\User::getTable();


/**
 * Legends
 */
$GLOBALS['TL_LANG'][$table]['settings_legend'] = 'Eintellungen';


/**
 * Operations
 */
$GLOBALS['TL_LANG'][$table]['new'][0] = 'Neuer Benutzer';
$GLOBALS['TL_LANG'][$table]['new'][1] = 'Einen neuen Benutzer erstellen';
$GLOBALS['TL_LANG'][$table]['edit'][0] = 'Benutzer bearbeiten';
$GLOBALS['TL_LANG'][$table]['edit'][1] = 'Benutzer ID %s bearbeiten';
$GLOBALS['TL_LANG'][$table]['copy'][0] = 'Benutzer duplizieren';
$GLOBALS['TL_LANG'][$table]['copy'][1] = 'Benutzer ID %s duplizieren';
$GLOBALS['TL_LANG'][$table]['delete'][0] = 'Benutzer löschen';
$GLOBALS['TL_LANG'][$table]['delete'][1] = 'Benutzer ID %s löschen';
$GLOBALS['TL_LANG'][$table]['show'][0] = 'Details';
$GLOBALS['TL_LANG'][$table]['show'][1] = 'Die Details des Benutzers ID %s anzeigen';


/**
 * Fields
 */
$GLOBALS['TL_LANG'][$table]['title'][0] = 'Titel';
$GLOBALS['TL_LANG'][$table]['title'][1] = 'Geben Sie einen intern verwendeten Namen ein.';
$GLOBALS['TL_LANG'][$table]['authorization'][0] = 'Authorisierungs-Typ';
$GLOBALS['TL_LANG'][$table]['authorization'][1] = 'Wählen Sie das Verfahren zur Authentifizierung aus.';
$GLOBALS['TL_LANG'][$table]['username'][0] = 'Benutzername';
$GLOBALS['TL_LANG'][$table]['username'][1] = 'Geben Sie den Benutzernamen für die Anmeldung am E-POST Portal ein.';
$GLOBALS['TL_LANG'][$table]['password'][0] = 'Passwort';
$GLOBALS['TL_LANG'][$table]['password'][1] = 'Geben Sie das Passwort für die Anmeldung am E-POST Portal ein.';
$GLOBALS['TL_LANG'][$table]['scopes'][0] = 'Scopes';
$GLOBALS['TL_LANG'][$table]['scopes'][1] = 'Der Scope legt fest, inwieweit der Client auf die Ressource(n) des Nutzers zugreift.';
$GLOBALS['TL_LANG'][$table]['invalidate_immediate'][0] = 'Sofort abmelden';
$GLOBALS['TL_LANG'][$table]['invalidate_immediate'][1] = 'Die Gültigkeit der Authorisierung unmittelbar nach der Benutzung widerrufen.';
$GLOBALS['TL_LANG'][$table]['test_environment'][0] = 'Test-Umgebung';
$GLOBALS['TL_LANG'][$table]['test_environment'][1] = 'Aktivieren Sie die Test-Umgebung (für Entwickler).';


/**
 * References
 */
$GLOBALS['TL_LANG'][$table]['scopeOptions']['send_letter'] = 'Senden von elektronischen Briefen';
$GLOBALS['TL_LANG'][$table]['scopeOptions']['send_hybrid'] = 'Senden von postalischen Briefen';
$GLOBALS['TL_LANG'][$table]['scopeOptions']['read_letter'] = 'Anzeigen von Entwürfen';
$GLOBALS['TL_LANG'][$table]['scopeOptions']['create_letter'] = 'Anlegen von Entwürfen';
$GLOBALS['TL_LANG'][$table]['scopeOptions']['delete_letter'] = 'Löschen von Entwürfen';
$GLOBALS['TL_LANG'][$table]['scopeOptions']['safe'] = 'Zugriff auf die E-POST CLOUD';
$GLOBALS['TL_LANG'][$table]['scopeOptions']['register_device'] = 'Registrieren von Geräten';
