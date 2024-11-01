<?php
// DEFINITION CLASSE export_odt

// Classe permettant l'export d'une facture au format odt
class eoinvoice_export_odt
{
	var $odt_export_object;
	var $tools_object;
	var $invoice_row_object;
	var $invoice_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_export_odt()
	{
		// On charge la librairie odtPHP
		require_once(EOINVOICE_HOME_DIR . 'include/lib/odt/odf.php');
		// On instancie en précisant le template utilisé
		$this->odt_export_object = new odf(EOINVOICE_HOME_DIR . 'include/module/templates/odt/basic.odt');
		
		// On charge les classes nécessaires pour la lecture des tables de coordonnées en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_row_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		
		// On instancie
		// Outils divers
		$this->tools_object = new eoinvoice_tools();
		// Factures
		$this->invoice_row_object = new eoinvoice_invoice_row_table();
		$this->invoice_object = new eoinvoice_invoice_table();
	}
	
	// METHODES
	
	// Permet l'export d'une facture référencée par son invoice_id
	function invoice_export($invoice_id)
	{
		$store_number = $_SESSION['eoinvoice_selected_store'];
		
		// On récupère l'id du magasin
		$store_id = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'store_id');
		// On récupère les infos du magasin
		$store_info_array = $this->tools_object->eoinvoice_get_store_info($store_number);
		// On trie tout ça
		$store_adress_id = $store_info_array[0];
		$store_name = $store_info_array[1];
		$store_email = $store_info_array[2];
		$store_tax_number = $store_info_array[3];
		
		$this->odt_export_object->setVars('store_name', $store_name);
		$this->odt_export_object->setVars('store_street_adress', $store_street_adress);
		$this->odt_export_object->setVars('store_city', $store_city);
		$this->odt_export_object->setVars('store_postcode', $store_postcode);
		$this->odt_export_object->setVars('store_phone', $store_phone);
		$this->odt_export_object->setVars('store_email', $store_email);
		$this->odt_export_object->setVars('store_tax_number', $store_tax_number);
		
		$this->odt_export_object->saveToDisk('bla.odt');
		
		// Affiche le document dans une iframe.
		echo '<iframe src="../wp-content/plugins/eoinvoice/include/bla.odt" width="100%" height="100%">
			[Your browser does <em>not</em> support <code>iframe</code>,
			or has been configured not to display inline frames.
			You can access <a href="../wp-content/plugins/eoinvoice/include/bla.odt">the document</a>
			via a link though.]</iframe>';
	}
}

?>
