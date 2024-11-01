<?php
// DEFINITION Classe eoinvoice_user_store_table

class eoinvoice_user_store_table
{
	var $verif;
	var $store_list_table_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_user_store_table()
	{
		
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		$this->store_list_table_object = new eoinvoice_store_list_table();
		
		// Variable db wordpress globale
		global $wpdb;
		
		// On vérifie si la table  existe
		if ($wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_USER_STORE . "'") == EOI_TABLE_USER_STORE)
		{
			// Si elle existe, on passe le controle a vrai
			// Sinon faux
			$this->verif = true;}else{$this->verif = false;
		}
	}
	
	// METHODES
	
	// Permet à un utilisateur désigné par son user_id de devenir un manager
	// càd un gestionnaire de magasin
	function link_user_as_manager($user_id, $store_number)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		$sql = "INSERT INTO " . EOI_TABLE_USER_STORE . " (user_id, store_number, state, add_date, update_date) VALUES ('" .
		$user_id . "', '" .
		$store_number . "', '" .
		"Active" . "', " .
		"NOW()" . ", " . 
		"NOW()" . ")";
		
		// Filtrage injection puis execution
		$wpdb->query($wpdb->prepare($sql));
		
		// Si l'user est déja dans cette table, la requête précédente ne passe pas, on update donc simplement :
		// Préparation de la requête
		$sql = "UPDATE " . EOI_TABLE_USER_STORE . " SET state='Active', update_date=NOW() WHERE user_id=". $user_id . " AND store_number=" . $store_number;
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de la mise en place du gestionnaire', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	// Permet de supprimer un manager désigné par son user_id
	function unlink_manager($user_id, $store_number)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		$sql = "UPDATE " . EOI_TABLE_USER_STORE . " SET state='Deleted', update_date=NOW() WHERE user_id=". $user_id . " AND store_number=" . $store_number;
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de la suppression du gestionnaire', 'eoinvoice_trdom' );}
		else
		{return TRUE;}
	}
	
	// Renvoie un tableau des magasins dont l'utilisateur user_id est manager
	// Tableau
	function user_store_list($user_id)
	{
		// Variable db wordpress globale
		global $wpdb;	
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT store_number FROM " . EOI_TABLE_USER_STORE . " WHERE user_id=" . $user_id . " AND state='Active'";
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// On déclare le tableau
		$store_list = array();
		$i = 0;
		
		// On met tout dans le tableau
		foreach ($resultats as $resultat)
		{
			if($this->store_list_table_object->is_store_active($resultat->store_number))
			{
				$store_list[$i] = $resultat->store_number; $i++;
			}
		}
		
		// Renvoie le premier et unique résultat
		return $store_list;
	}
	
	// Renvoie un tableau contenant la liste des gestionnaires pour un magasin $store_number donné
	function store_manager_list($store_number)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT user_id FROM " . EOI_TABLE_USER_STORE . " WHERE store_number=" . $store_number. " AND state='Active'";
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		// On déclare le tableau
		$user_list = array();
		$i = 0;
		
		// On met tout dans le tableau
		foreach ($resultats as $resultat)
		{$user_list[$i] = $resultat->user_id; $i++;}
		
		// Renvoie le premier et unique résultat
		return $user_list;
	}
	
}
?>
