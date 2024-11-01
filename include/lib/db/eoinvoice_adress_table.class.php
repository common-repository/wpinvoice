<?php

class eoinvoice_adress_table
{
	// CONSTRUCTEUR
	
	function eoinvoice_adress_table()
	{	
	}
	
	// METHODES
	
	function new_adress($street_adress, $suburb, $city, $postcode, $state, $country, $phone, $telecopy)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "INSERT INTO " . EOI_TABLE_ADRESS . "(street_adress, suburb, city, postcode, state, country, phone, telecopy) VALUES ('" .
		htmlentities($street_adress, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($suburb, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($city, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($postcode, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($state, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($country, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($phone, ENT_QUOTES, "utf-8") . "', '" .
		htmlentities($telecopy, ENT_QUOTES, "utf-8") . "')";
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'insertion de l\'adresse en base de donn&eacute;es', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	function get_adress_element($adress_id, $element)
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT " . $element . " FROM " . EOI_TABLE_ADRESS . " WHERE adress_id=" . $adress_id;

		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));

		return $resultat;
	}
	
	function get_last_adress_id()
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT MAX(adress_id) FROM " . EOI_TABLE_ADRESS;

		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		return $resultat;
	}
}

?>
