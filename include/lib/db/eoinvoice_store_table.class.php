<?php

class eoinvoice_store_table
{
	// CONSTRUCTEUR
	
	function eoinvoice_store_table()
	{	
	}
	
	// METHODES
	
	// Crée un nouveau store
	function update_store($store_number, $nom, $email, $tax_number, $adress_id, $check_accept, $society_type, $society_capital, $rib_fr_bank_code, $rib_fr_register_code, $rib_fr_account_number, $rib_fr_key, $rib_iban, $rib_bic)
	{	
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		$sql = "INSERT INTO " . EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF . " (store_name, store_tax_number, store_email, store_adress_id, society_type, society_capital, store_accept_check, rib_fr_bank, rib_fr_register, rib_fr_account, rib_fr_key, rib_IBAN, rib_BIC) VALUES ('" .
		htmlentities($nom, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($tax_number, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($email, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($adress_id, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($society_type, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($society_capital, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($check_accept, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($rib_fr_bank_code, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($rib_fr_register_code, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($rib_fr_account_number, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($rib_fr_key, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($rib_iban, ENT_QUOTES, "utf-8") . "', '" . 
		htmlentities($rib_bic, ENT_QUOTES, "utf-8") . "')";
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'insertion des coordonn&eacute;es du magasin en base de donn&eacute;es', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	// Permet de récupérer la valeur d'un des champs de la table store
	function get_store_element($store_number, $element)
	{
		// Variable db wordpress globale
		global $wpdb;	
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT ". $element . " FROM " . EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF . " WHERE store_id=(SELECT MAX(store_id) FROM " . EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF . ")";
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		// Renvoie le premier et unique résultat
		return $resultat;
	}
	
	function get_last_store_id($store_number)
	{
		// Variable db wordpress globale
		global $wpdb;	
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT MAX(store_id) FROM " . EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF;
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		// Renvoie le premier et unique résultat
		return $resultat;
	}
}

?>
