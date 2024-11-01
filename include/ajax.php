<?php

DEFINE('DOING_AJAX', true);
DEFINE('WP_ADMIN', true);
require_once('../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

// On récupère l'action demandée
$action = $_REQUEST['action'];

if($action == 'export')
{
	// On charge le fichier principal permettant la redéfinition des constantes
	require('../eoinvoice.php');
	
	// On récupère le format d'export
	$export_type = $_REQUEST['export_type'];
	
	//On récupère le invoice_id aisni que le numéro du magasin
	$store_number = $_REQUEST['store_number'];
	$invoice_id = $_REQUEST['invoice_id'];
	
	// Si PDF
	if($export_type == 'pdf')
	{
		$eoinvoice_front->pdf_export_object->invoice_export($store_number, $invoice_id);
	}
	
	// Si ODT
	if($export_type == 'odt')
	{
		$eoinvoice_front->odt_export_object->invoice_export($store_number, $invoice_id);
	}
}

if($action == 'graph')
{
	// On charge le fichier principal permettant la redéfinition des constantes
	require('../eoinvoice.php');
	
	$eoinvoice_front->stats_object->make_graph();
}

?>
