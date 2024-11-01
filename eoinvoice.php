<?php
/*
Plugin Name: wpinvoice
Description: Permet la facturation depuis un site r&eacute;alis&eacute; sous wordpress.
Version: 0.1
Author: Eoxia
Author URI: http://www.eoxia.com/
License: GPL3


Copyright 2011  EOXIA  (email : contact@eoxia.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// CONSTANTES

// Chemin fichier maitre du plugin
DEFINE('EOINVOICE_MAIN_PHPFILE_PATH', __FILE__);
// Nom du repertoire du plugin
DEFINE('EOINVOICE_PLUGIN_DIR', basename(dirname(__FILE__)));
// Chemin du repertoire
DEFINE('EOINVOICE_HOME_DIR', WP_PLUGIN_DIR . '/' . EOINVOICE_PLUGIN_DIR . '/');
DEFINE('EOINVOICE_HOME_URL', WP_PLUGIN_URL . '/' . EOINVOICE_PLUGIN_DIR . '/');
// Admin capability
DEFINE('ADMIN_CAPABILITY', 'manage_options');
DEFINE('ADMIN_LEVEL', 10);
// Arrondis pour l'affichage
DEFINE('ROUND_DEC_AMOUNT', 2);
// Fichiers de polices PDF
DEFINE('FPDF_FONTPATH', EOINVOICE_HOME_DIR . 'include/lib/pdf/font/');
// Code de cryptage clé XML en base de données
DEFINE('EOINVOICE_XML_SERVICE_KEY', 'D2E5Rt!hy487');
// Algorythme et mode de cryptage utilisés pour les transferts XML
DEFINE('EOINVOICE_CRYPT_ALGO', 'blowfish');
DEFINE('EOINVOICE_CRYPT_MODE', 'nofb');

// INITIALISATION SESSION

session_start();

// INCLUDES
		
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_settings.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_front.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_entry.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_customer.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_store_creation.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_service.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_init.class.php');
require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_stats.class.php');

// INSTANCIATION

$eoinvoice_front = new eoinvoice_front();
$eoinvoice_settings = new eoinvoice_settings();
$eoinvoice_entry = new eoinvoice_entry();
$eoinvoice_customer = new eoinvoice_customer();
$eoinvoice_store_creation = new eoinvoice_store_creation();
$eoinvoice_service = new eoinvoice_service();
$eoinvoice_stats = new eoinvoice_stats();
$eoinvoice_init = new eoinvoice_init($eoinvoice_settings, $eoinvoice_front, $eoinvoice_entry, $eoinvoice_customer, $eoinvoice_store_creation, $eoinvoice_service, $eoinvoice_stats);

// MISE A JOUR BASE DE DONNEES
// Nom des tables
include_once(EOINVOICE_HOME_DIR . 'include/config/configNomTables.php');
// Chargement lib de création et de mise à jour des tables
require_once(EOINVOICE_HOME_DIR . 'include/module/install/creationTables.php');
eoinvoice_db_update();

?>