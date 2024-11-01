<?php // DEFINITION CLASSE invoice_row

class eoinvoice_invoice_row_table
{
	// Numéro du store à traiter
	var $store_number;


	// CONSTRUCTEUR

	function eoinvoice_invoice_row_table()
	{	
	}
	
	// METHODES
	
	// Ajout d'une entrée dans la table
	function new_invoice_row($store_number, $invoice_id, $row_array)
	{
		// Variable db wordpress globale
		global $wpdb;
		
		// Structure $row_array
		// [0] $product_name 		[1] $product_desc 		[2] $product_base_price
		// [3] $product_price 		[4] $product_ref		[5] $product_weight
		// [6] $product_tax_percent [7] $row_weight			[8] $product_qty
		// [9] $product_discount 	[10] $row_base_price	[11] $row_price
		
		// Remplissage des champs
		$id_of_invoice = $invoice_id;
		$product_id = 0;
		$product_name = $row_array[0];
		$product_description = $row_array[1];
		$product_base_price = $row_array[2];
		$product_price = $row_array[3];
		$product_ean13 = 0;
		$product_reference = $row_array[4];
		$product_supplier_reference = 0;
		$product_weight = $row_array[5];
		$qty_ordered = 0;
		$qty_invoiced = $row_array[8];
		$qty_shipped = 0;
		$qty_backordered = 0;
		$qty_canceled = 0;
		$qty_refunded = 0;
		$row_weight = $row_array[7];
		$tax_percent = $row_array[6];
		$product_no_discount = 0;
		$row_discount_percent = $row_array[9];
		$row_discount_amount = 0;
		$row_base_discount_amount = 0;
		$row_price = $row_array[11];
		$row_base_price = $row_array[10];
		$tax_amount = $row_price-$row_base_price;
		
		// Préparation de la requête
		$sql =
			"INSERT INTO " . EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF . " (
			invoice_id,
			product_id,
			product_name,
			product_description,
			product_base_price,
			product_price,
			product_ean13,
			product_reference,
			product_supplier_reference,
			product_weight,
			qty_ordered,
			qty_invoiced,
			qty_shipped,
			qty_backordered,
			qty_canceled,
			qty_refunded,
			row_weight,
			tax_percent,
			tax_amount,
			no_discount,
			discount_percent,
			discount_amount,
			base_discount_amount,
			price,
			base_price) VALUES ('" .
			$id_of_invoice . "', '" .
			$product_id . "', '" .
			htmlentities($product_name, ENT_QUOTES, "utf-8") . "', '" .
			htmlentities($product_description, ENT_QUOTES, "utf-8") . "', '" .
			$product_base_price. "', '" .
			$product_price . "', '" .
			$product_ean13 . "', '" .
			$product_reference . "', '" .
			$product_supplier_reference . "', '" .
			$product_weight . "', '" .
			$qty_ordered . "', '" .
 			$qty_invoiced . "', '" .
			$qty_shipped . "', '" .
			$qty_backordered . "', '" .
			$qty_canceled . "', '" .
			$qty_refunded . "', '" .
			$row_weight . "', '" .
			$tax_percent . "', '" .
			$tax_amount . "', '" .
			$product_no_discount . "', '" .
			$row_discount_percent . "', '" .
			$row_discount_amount . "', '" .
			$row_base_discount_amount . "', '" .
			$row_price . "', '" .
			$row_base_price . "');";

		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'insertion d\'une ligne de facture en base de donn&eacute;es', 'eoinvoice_trdom' );}
		else
		{return TRUE;}
	}
	
	// Retourne UN élément d'une ligne de facture
	// référencée par son invoice_row_id
	function get_invoice_row_element($store_number, $invoice_row_id, $element)
	{
		// Variable db globale
		global $wpdb;
		// Préparation de la requête
		$sql = "SELECT " . $element . " FROM " . EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF . " WHERE invoice_row_id=" . $invoice_row_id;
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));

		// On retourne l'élément
		return $resultat;
	}
	
	// Retourne un ligne de la facture sous forme de tableau
	function get_invoice_row_array($store_number, $invoice_row_id)
	{
		// Variable db globale
		global $wpdb;
		// Préparation de la requête
		$sql = "SELECT * FROM " . EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF . " WHERE invoice_row_id=" . $invoice_row_id;
		// Filtrage injection puis execution
		$row_array = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
		// On retourne le tableau des résultats
		return $row_array;
	}
	
	// Retourne un tableau contenant les id des lignes de factures concernant la facture d'id $invoice_id
	function get_rows_about($store_number, $invoice_id)
	{
		// On déclare notre tableau
		$rows_array = array();
		// Variable db globale
		global $wpdb;
		// Préparation de la requête
		$sql = "SELECT invoice_row_id FROM " . EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF . " WHERE invoice_id=" . $invoice_id;
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		// Compteur
		$i = 0;
		// On met tout dans le tableau
		foreach ($resultats as $resultat)
		{$rows_array[$i] = $resultat->invoice_row_id; $i++;}
		
		// On retourne notre tableau d'id de ligne
		return $rows_array;
	}
}

?>
