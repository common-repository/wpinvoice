<?php

class eoinvoice_user_adress_table
{
	// Booleen table existante
	var $verif;

	function eoinvoice_user_adress_table()
	{	
		// Variable db wordpress globale
		global $wpdb;
		
		// On vérifie si la table adress existe
		if ($wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_USER_ADRESS . "'") == EOI_TABLE_USER_ADRESS)
		{
			// Si elle existe, on passe le controle a vrai
			// Sinon faux
			$this->verif = true;}else{$this->verif = false;
		}
	}
	
	// Crée un nouveau lien entre adresse et utilisateur -> creation client
	function eoinvoice_new_user_adress_link($user_id, $adress_id, $adress_type)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		$sql = "INSERT INTO " . EOI_TABLE_USER_ADRESS . " (user_id, adress_id, adress_type) VALUES ('" .
		mysql_real_escape_string($user_id) . "', '" . 
		mysql_real_escape_string($adress_id) . "', '" . 
		mysql_real_escape_string($adress_type) . "')";
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'attribution d\'une adresse à un utilisateur', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	// Permet de récupérer l'id d'une des adresses du client
	// $type 1 facturation 2 livraison
	function eoinvoice_get_user_adress_id($user_id,$type)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT MAX(adress_id) FROM " . EOI_TABLE_USER_ADRESS . " WHERE (user_id=" . $user_id . " AND adress_type=" . $type . ")";
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		return $resultat;
	}
	
	// Permet de récupérer le nom et le prenom du client
	// $type == 0 => login | 1 => prenom | 2 => nom
	function eoinvoice_get_user_info($user_id, $type)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		switch ($type)
		{ 
			case 1 : 
			 $meta_type = "last_name";
			 break;
			case 2 : 
			 $meta_type = "first_name"; 
			 break;
			case 0 : 
			 $meta_type = "nickname";
			 break; 
		}
	
		// Préparation de la requête
		$sql = "SELECT meta_value FROM " . EOI_TABLE_WP_USER_META . " WHERE (user_id=" . $user_id . " AND meta_key='" . $meta_type . "')";
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));

		return $resultat;
	}
	
		// Liste tout les id utilisateurs/clients inscrits et renvoie un tableau
	function eoinvoice_get_user_id_list()
	{
		// Variable db globale
		global $wpdb;
		
		// Préparation de la requête
		$sql = "SELECT * FROM " . EOI_TABLE_WP_USERS ;

		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		$users_id_array = array();
		$current_table_ref = 0;
		// Renvoie les résultats dans un tableau
		foreach ($resultats as $resultat)
		{$users_id_array[$current_table_ref] = $resultat->ID; $current_table_ref++;}
		
		return $users_id_array;
	}

}

?>
