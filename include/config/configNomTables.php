<?php
	
/**
 * Define table names constants
 */

global $wpdb;

	
// STORE LINKED TABLES
// Store depending tables prefix
DEFINE('EOI_TABLE_INVOICE_PRE', $wpdb->prefix . "eoinvoice_s" . $store_number);
DEFINE('EOI_TABLE_INVOICE_ROW_PRE', $wpdb->prefix . "eoinvoice_s" . $store_number);
// Store table
DEFINE('EOI_TABLE_STORE_PRE', $wpdb->prefix . "eoinvoice_s" . $store_number);
// Store depending tables suffix
DEFINE('EOI_TABLE_INVOICE_SUF', "__invoice");
DEFINE('EOI_TABLE_INVOICE_ROW_SUF', "__invoice_row");
// Store table
DEFINE('EOI_TABLE_STORE_SUF', "__store");

// COMMON TABLES
// Store list table
DEFINE('EOI_TABLE_STORE_LIST', $wpdb->prefix . "eoinvoice__store_list");
// Store list table
DEFINE('EOI_TABLE_GED_DOCUMENTS', $wpdb->prefix . "eoinvoice__ged_documents");
// Adress table
DEFINE('EOI_TABLE_ADRESS', $wpdb->prefix . "eoinvoice__adress");
// Link table
DEFINE('EOI_TABLE_USER_ADRESS', $wpdb->prefix . "eoinvoice__user_adress");
// Link table
DEFINE('EOI_TABLE_USER_STORE', $wpdb->prefix . "eoinvoice__user_store");
// Standard wordpress user table
DEFINE('EOI_TABLE_WP_USERS', $wpdb->prefix . "users");
// Standard wordpress user meta table
DEFINE('EOI_TABLE_WP_USER_META', $wpdb->prefix . "usermeta");

?>
