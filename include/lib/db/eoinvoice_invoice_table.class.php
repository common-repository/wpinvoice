<?php // DEFINITION CLASSE invoice

class eoinvoice_invoice_table
{
	// Variable de classe numéro du store à traiter
	var $store_number;
	// Objet de liaison base de données store
	var $store_table_object;

	// CONSTRUCTEUR

	function eoinvoice_invoice_table()
	{	
		// On charge les classes nécessaires pour la gestion et la lecture des tables en base de données
		// Table magasin/store
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_table.class.php');
		// Instanciation
		$this->store_table_object = new eoinvoice_store_table();
	}
	
	// METHODES
	
	// Ajout d'une entrée dans la table
	function eoinvoice_new_invoice($store_number, $invoice_array)
	{
		// On récupère les totaux
		// [0] base_total_price			[1] total_price	
		// [2] total_qty				[3] total_weight
		// [4] customer_billing_adress 	[5] customer_delivery_adress
		// [6] user_id					[7] total_discount
		
		// Variable db wordpress globale
		global $wpdb;
		
		global $current_user;
		get_currentuserinfo();
		$current_user_id = $current_user->id;
		
		// Type de prefixe de facture
		if (get_option('eoinvoice__s' . $store_number . '_ref_type') == 'advanced')
		{$invoice_prefix = get_option('eoinvoice__s' . $store_number . '_ref_prefix');}else{$invoice_prefix = '';}
		// Règle pour les références
		$sprintf_rule = "%0". get_option('eoinvoice__s' . $store_number . '_ref_number_count') ."d";
		
		// Remplissage des champs
		$invoice_ref = $invoice_prefix . sprintf($sprintf_rule, (count($this->get_invoices($store_number, 0))+1)); // Référence de la facture
		$invoice_date_add = "NOW()"; // Date de creation de la facture
		$invoice_date_update = "NOW()"; // Date de mise à jour
		$invoice_comment = 0; // Commentaire
		$invoice_status = 'NotYet'; // Status de la facture
		$store_id = $this->store_table_object->get_last_store_id($store_number); // ID du magasin
		$user_id = $invoice_array[6];// ID du client
		$billing_adress_id = $invoice_array[4]; // ID de l'adresse de facturation
		$delivery_adress_id = $invoice_array[5]; // ID de l'adresse de livraison
		$payment_method = 0; // Méthode de paiement
		$payment_method_module_code = 0; // Code du module de paiement
		$delivery_method = 0; // Méthode livraison
		$delivery_method_module_code = 0; // Code du module de livraison
		$total_qty_ordered = $invoice_array[2]; // Quantité totale
		$discount_code = 0; // Code de réduction
		$total_discount = $invoice_array[7]; // Total des remises
		$base_products = $invoice_array[0]; // Total HT des produits
		$base_shipping = 0; // Total HT des livraisons
		$base_wrapping = 0; // Total HT des emballages
		$base_grand_total = $base_products+$base_shipping+$base_wrapping; // Total HT
		$total_paid = 0; // Total payé
		$total_paid_real = 0; // Total encaissé
		$total_products = $invoice_array[1]; // Total TTC des produits
		$total_shipping = 0; // Total TTC des livraisons
		$total_wrapping = 0; // Total TTC des emballages
		$grand_total = $total_products+$total_shipping+$total_wrapping; // Total TTC
		$tax_total = $grand_total-$base_grand_total; // Total des taxes
		$currency = '€'; // Devise utilisée
		$currency_in_usd = 0; // Valeur de la devise utilisée, en dollar US
		$ip_adress = 0; // IP de l'emmetteur de la facture
		$secure_key = 0; // Mot de passe de sécurité pour consultation client
		
		// Préparation de la requête
		$sql =
			"INSERT INTO " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " (
			invoice_ref,
			invoice_date_add,
			invoice_date_update,
			invoice_comment,
			invoice_status,
			store_id,
			user_id,
			billing_adress_id,
			delivery_adress_id,
			payment_method,
			payment_method_module_code,
			delivery_method,
			delivery_method_module_code,
			total_qty_ordered,
			discount_code,
			total_discount,
			base_products,
			base_shipping,
			base_wrapping,
			base_grand_total,
			total_paid,
			total_paid_real,
			total_products,
			total_shipping,
			total_wrapping,
			grand_total,
			tax_total,
			currency,
			currency_in_usd,
			ip_adress,
			secure_key ) VALUES ('" .
			$invoice_ref . "', " .
			$invoice_date_add . ", " .
			$invoice_date_update . ", '" .
			$invoice_comment . "', '" .
			$invoice_status . "', '" .
			$store_id . "', '" .
			$user_id . "', '" .
			$billing_adress_id . "', '" .
			$delivery_adress_id . "', '" .
			$payment_method . "', '" .
			$payment_method_module_code . "', '" .
			$delivery_method . "', '" .
			$delivery_method_module_code . "', '" .
			$total_qty_ordered . "', '" .
			$discount_code . "', '" .
			$total_discount . "', '" .
			$base_products . "', '" .
			$base_shipping . "', '" .
			$base_wrapping . "', '" .
			$base_grand_total . "', '" .
			$total_paid . "', '" .
			$total_paid_real . "', '" .
			$total_products . "', '" .
			$total_shipping . "', '" .
			$total_wrapping . "', '" .
			$grand_total . "', '" .
			$tax_total . "', '" .
			$currency . "', '" .
			$currency_in_usd . "', '" .
			$ip_adress . "', '" .
			$secure_key . "');";

		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'insertion de l\'adresse en base de donn&eacute;es', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	// Retourne un élément d'une facture référencée par son invoice_id 
	function get_invoice_element($store_number, $invoice_id, $element)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT " . $element . " FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " WHERE invoice_id=" . $invoice_id;

		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		return $resultat;
	}
	
	// Retourne un tableau contenant les invoice_id des factures de l'user_id concerné sur le magasin $store_number
	// Si user_id = 0, toutes les factures sont affichées
	function get_invoices($store_number, $user_id)
	{
		// Variable db globale
		global $wpdb;
		
		$invoices_array = array();
		
		// Préparation de la requête
		if ($user_id == 0)
		{$sql = "SELECT invoice_id FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF;}
		else
		{$sql = "SELECT invoice_id FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " WHERE user_id=" . $user_id;}

		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// Chariot
		$i = 0;
		
		// On met tout dans le tableau
		foreach ($resultats as $resultat)
		{$invoices_array[$i] = $resultat->invoice_id; $i++;}
		
		return $invoices_array;
	}
	
	// Retourne l'id de la dernière facture crée
	function get_last_invoice_id($store_number)
	{
		// Variable db wordpress globale
		global $wpdb;	
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT MAX(invoice_id) FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF;
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		// Renvoie le premier et unique résultat
		return $resultat;
	}
	
	// Retourne les totaux tva et normaux des factures sur une année donnée
	// TABLEAU:  [0] grand_total		[1] tax_total
	function get_year_totals($store_number, $year)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT grand_total, tax_total FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " WHERE YEAR(invoice_date_add) = " . $year;
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// Chariot
		$i = 0;
		
		// On met les totaux TVA et normaux dans le tableau
		foreach ($resultats as $resultat)
		{
			$tva = $tva + $resultat->tax_total;
			$grand_total = $grand_total + $resultat->grand_total;
			$totals_array[0] = $totals_array[0] + $grand_total;
			$totals_array[1] = $totals_array[1] + $tva;
		}
		
		return $totals_array;
	}
	
	// Retourne les totaux tva et normaux des factures sur un mois donné
	// TABLEAU:  [0] grand_total		[1] tax_total
	function get_month_totals($store_number, $year, $month)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT grand_total, tax_total FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " WHERE YEAR(invoice_date_add) = " . $year . " AND MONTH(invoice_date_add) = " . $month;
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// On met les totaux TVA et normaux dans le tableau
		foreach ($resultats as $resultat)
		{
			$tva = $tva + $resultat->tax_total;
			$grand_total = $grand_total + $resultat->grand_total;
			$totals_array[0] = $totals_array[0] + $grand_total;
			$totals_array[1] = $totals_array[1] + $tva;
		}
		
		return $totals_array;
	}
	
	// Retourne les totaux tva et normaux des factures sur un jour donné
	// TABLEAU:  [0] grand_total		[1] tax_total
	function get_day_totals($store_number, $year, $month, $day)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT grand_total, tax_total FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " WHERE YEAR(invoice_date_add) = " . $year . " AND ( MONTH(invoice_date_add) = " . $month . " AND DAY(invoice_date_add) = " . $day ." )";
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// On met les totaux TVA et normaux dans le tableau
		foreach ($resultats as $resultat)
		{
			$tva = $tva + $resultat->tax_total;
			$grand_total = $grand_total + $resultat->grand_total;
			$totals_array[0] = $totals_array[0] + $grand_total;
			$totals_array[1] = $totals_array[1] + $tva;
		}
		
		return $totals_array;
	}
	
	// Retourne les totaux tva et normaux des factures depuis la création
	// TABLEAU:  [0] grand_total		[1] tax_total
	function get_all_time_totals($store_number)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT grand_total, tax_total FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF;
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// On met les totaux TVA et normaux dans le tableau
		foreach ($resultats as $resultat)
		{
			$tva = $tva + $resultat->tax_total;
			$grand_total = $grand_total + $resultat->grand_total;
			$totals_array[0] = $totals_array[0] + $grand_total;
			$totals_array[1] = $totals_array[1] + $tva;
		}
		
		return $totals_array;
	}
	
	// Retourne les 5 dernières factures
	// TABLEAU:
	// [x][0] invoice_ref		[x][1] user_id
	// [x][2] total_qty_ordered	[x][3] grand_total
	function last_five_invoices($store_number)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT invoice_ref, user_id, total_qty_ordered, grand_total FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " WHERE invoice_id > ( SELECT MAX(invoice_id) FROM " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . ")-5";
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// Chariot
		$i = 0;
		
		// On met les totaux TVA et normaux dans le tableau
		foreach ($resultats as $resultat)
		{
			$last5_array[$i][0] = $resultat->invoice_ref;
			$last5_array[$i][1] = $resultat->user_id;
			$last5_array[$i][2] = $resultat->total_qty_ordered;
			$last5_array[$i][3] = $resultat->grand_total;
			$i++;
		}
		
		return $last5_array;
	}
	
	function mark_as_paid($store_number, $invoice_id)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		$sql = "UPDATE " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " SET invoice_payment_status='Paid', invoice_date_update=NOW() WHERE invoice_id=". $invoice_id ;
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de la suppression du gestionnaire', 'eoinvoice_trdom' );}
		else
		{return TRUE;}
	}
}
