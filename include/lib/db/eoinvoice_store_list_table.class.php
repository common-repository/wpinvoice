<?php
// DEFINITION Classe eoinvoice_store_list_table
// Permet de gérer la table store_list, contenant la liste de tous les magasins

class eoinvoice_store_list_table
{
	// CONSTRUCTEUR
	
	function eoinvoice_store_list_table()
	{
	}
	
	// METHODES
	
	// Ajoute un store à la liste
	function add_store_to_list()
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// On génére l'identifiant unique
		$unique = md5(uniqid());
	
		// Préparation de la requête
		$sql = "INSERT INTO " . EOI_TABLE_STORE_LIST . " (store_add_date, store_uniqid, store_xml_import_key) VALUES (NOW(), '" . $unique . "', '" . $this->make_password(10) . "')";
		
		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'insertion du magasin en base de donn&eacute;es', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	// Renvoi le nombre de magasin présent en base de données
	function how_many_stores()
	{
		// Variable db wordpress globale
		global $wpdb;	
	
		// Préparation de la requête
		// Récupère les informations les plus à jour
		$sql = "SELECT COUNT(store_number) FROM " . EOI_TABLE_STORE_LIST;
		
		// Filtrage injection puis execution
		$store_count = $wpdb->get_var($wpdb->prepare($sql));
		
		// Renvoie le résultat
		return $store_count;
	}
	
	// Renvoie un tableau contenant la liste des numéro de magasin
	function get_store_list()
	{
		// Variable db wordpress globale
		global $wpdb;
	
		$store_list_array = array();
	
		// Préparation de la requête
		// Récupère tous les store_number
		$sql = "SELECT store_number FROM " . EOI_TABLE_STORE_LIST;
		
		// Filtrage injection puis execution
		$resultats = $wpdb->get_results($wpdb->prepare($sql));
		
		$i = 0;
		
		// A chaque numéro on ajoute le nom et on met dans le tableau
		foreach ($resultats as $resultat)
		{
			if($this->is_store_active($resultat->store_number))
			{
				$store_list_array[$i] = $resultat->store_number;
				$i++;
			}
		}
		
		return $store_list_array;
	}
	
	function get_store_uniqid($store_number)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		// Récupère tous les store_number
		$sql = "SELECT store_uniqid FROM " . EOI_TABLE_STORE_LIST . " WHERE store_number=" . $store_number;
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		return $resultat;
	}
	
	function get_store_by_uniqid($store_uniqid)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		// Récupère tous les store_number
		$sql = "SELECT store_number FROM " . EOI_TABLE_STORE_LIST . " WHERE store_uniqid='" . $store_uniqid . "'";
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		return $resultat;
	}
	
	function get_store_xml_import_key($store_number)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		// Récupère tous les store_number
		$sql = "SELECT store_xml_import_key FROM " . EOI_TABLE_STORE_LIST . " WHERE store_number=" . $store_number;
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		return $this->decrypt_password($resultat);
	}
	
	// Génere un mot de passe chiffré
	function make_password($length)
	{
		// Les caractères suivants sont ignorés pour la génération : " ' , ` < >
        $mauvais_chars = array(34,39,44,60,62,96);
        mt_srand(time()); 
        while (strlen($var) < $length)
        {
			$tmp = mt_rand(33,126); 
			if (in_array($tmp,$mauvais_chars))
				continue; 
				$var .= chr($tmp);
        } 
        return $this->crypt_password($var);
	}
	
	// Permet de chiffrer une chaîne de caractère
	function crypt_password($password)
	{
		// Lecture du fichier
		$huge_string = $password;

		// Choix d'un algo, mode (couple)
		$algo = EOINVOICE_CRYPT_ALGO;    // ou la constante php MCRYPT_BLOWFISH
		$mode = EOINVOICE_CRYPT_MODE;        // ou la constante php MCRYPT_MODE_NOFB

		// Calcul des longueurs max de la clé et de l'IV
		$key_size = mcrypt_module_get_algo_key_size($algo); // 56
		$iv_size  = mcrypt_get_iv_size($algo, $mode); // 8

		// Création d'un IV aléatoire de la bonne longueur
		// N'importe quoi du moment qu'il est de la bonne longueur
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		// On encrypte
		$huge_string_crypt  = mcrypt_encrypt($algo, EOINVOICE_XML_SERVICE_KEY, $huge_string, $mode, $iv);

		// On retourne le mot de passe crypté préfixé de l'IV
		return $iv.$huge_string_crypt;
	}
	
	// Permet de déchiffrer une chaîne de caractère
	function decrypt_password($password)
	{
		// Lecture du fichier
		$huge_string_crypt = $password;
		
		// Choix d'un algo, mode
		$algo = EOINVOICE_CRYPT_ALGO;    // ou la constante php MCRYPT_BLOWFISH
		$mode = EOINVOICE_CRYPT_MODE;        // ou la constante php MCRYPT_MODE_NOF
		
		// Calcul des longueurs max de la clé et de l'IV
		$key_size = mcrypt_module_get_algo_key_size($algo);
		$iv_size  = mcrypt_get_iv_size($algo, $mode);
		
		// Décryptage
		$huge_string_decrypt = mcrypt_decrypt($algo, EOINVOICE_XML_SERVICE_KEY, substr($huge_string_crypt,$iv_size) , $mode, substr($huge_string_crypt,0,$iv_size));

		// Affichage de contrôle
		return $huge_string_decrypt;
	}
	
	function is_store_active($store_number)
	{
		// Variable db wordpress globale
		global $wpdb;
	
		// Préparation de la requête
		// Récupère tous les store_number
		$sql = "SELECT store_status FROM " . EOI_TABLE_STORE_LIST . " WHERE store_number=" . $store_number;
		
		// Filtrage injection puis execution
		$resultat = $wpdb->get_var($wpdb->prepare($sql));
		
		if($resultat=='Active')
		{$out = TRUE;}else{$out = FALSE;}
		
		return $out;
	}

}
?>
