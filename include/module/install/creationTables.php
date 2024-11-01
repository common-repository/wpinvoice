<?php
// Création des tables lors de l'installation

function eoinvoice_db_creation_generic()
{
	global $wpdb;

	// On vérifie si la table de liaison n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_USER_STORE . "'") != EOI_TABLE_USER_STORE)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_USER_STORE . " (
			`user_id` INT NOT NULL ,
			`store_number` INT NOT NULL ,
			`state` ENUM('Active','Deleted') NOT NULL ,
			`add_date` DATETIME NULL ,
			`update_date` DATETIME NULL ,
			PRIMARY KEY(user_id, store_number)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}

	// On vérifie si la table store_list n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_STORE_LIST . "'") != EOI_TABLE_STORE_LIST)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_STORE_LIST . " (
			`store_number` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`store_status` ENUM(  'Active',  'Moderated', 'Deleted' ) NOT NULL DEFAULT 'Active' ,
			`store_add_date` DATETIME NOT NULL ,
			`store_uniqid` VARCHAR(32) NULL ,
			`store_xml_import_key` BLOB NULL
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}

	// On vérifie si la table adress n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_ADRESS . "'") != EOI_TABLE_ADRESS)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_ADRESS . " (
			`adress_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`street_adress` VARCHAR(100) NOT NULL ,
			`suburb` VARCHAR(45) NULL ,
			`city` VARCHAR(45) NOT NULL ,
			`postcode` VARCHAR(45) NOT NULL ,
			`state` VARCHAR(45) NOT NULL ,
			`country` VARCHAR(45) NOT NULL ,
			`phone` VARCHAR(15) NOT NULL , 
			`telecopy` VARCHAR(20) NULL
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}
	
	// On vérifie si la table de liaison n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_USER_ADRESS . "'") != EOI_TABLE_USER_ADRESS)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_USER_ADRESS . " (
			`user_id` INT NOT NULL ,
			`adress_id` INT NOT NULL ,
			`adress_type` INT NOT NULL ,
			PRIMARY KEY(user_id, adress_id, adress_type)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}
	
	// On vérifie si la table des documents n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_GED_DOCUMENTS . "'") != EOI_TABLE_GED_DOCUMENTS)
	{
		// On construit la requete SQL de création de table
		$sql =
			"CREATE TABLE " . EOI_TABLE_GED_DOCUMENTS . " (
			`id` int(10) unsigned NOT NULL auto_increment,
			`status` enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
			`dateCreation` datetime NOT NULL,
			`idCreateur` int(10) unsigned NOT NULL,
			`dateSuppression` datetime NULL,
			`idSuppresseur` int(10) unsigned NULL,
			`categorie` varchar(255) collate utf8_unicode_ci NOT NULL,
			`nom` varchar(255) collate utf8_unicode_ci NOT NULL,
			`chemin` varchar(255) collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `status` (`status`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Document management';";
	}
	
	update_option('eoinvoice_db_version', 6);
}

function eoinvoice_db_creation_store($store_number)
{
	global $wpdb;
	
	// On vérifie si la table des lignes n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF . "'") != EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_INVOICE_ROW_PRE . $store_number . EOI_TABLE_INVOICE_ROW_SUF . " (
			`invoice_row_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`invoice_id` INT NOT NULL ,
			`product_id` INT NOT NULL ,
			`product_name` VARCHAR(45) NULL ,
			`product_description` VARCHAR(200) NULL ,
			`product_base_price` NUMERIC(10,5) NULL ,
			`product_price` NUMERIC(10,5) NULL ,
			`product_ean13` VARCHAR(13) NULL ,
			`product_reference` VARCHAR(45) NULL ,
			`product_supplier_reference` VARCHAR(45) NULL ,
			`product_weight` NUMERIC(10,5) NULL ,
			`qty_ordered` INT NULL ,
			`qty_invoiced` INT NULL ,
			`qty_shipped` INT NULL ,
			`qty_backordered` INT NULL ,
			`qty_canceled` INT NULL ,
			`qty_refunded` INT NULL ,
			`row_weight` NUMERIC(10,5) NULL ,
			`tax_percent` NUMERIC(10,5) NULL ,
			`tax_amount` NUMERIC(10,5) NULL ,
			`no_discount` TINYINT(1) NULL ,
			`discount_percent` NUMERIC(10,5) NULL ,
			`discount_amount` NUMERIC(10,5) NULL ,
			`base_discount_amount` NUMERIC(10,5) NULL ,
			`price` NUMERIC(10,5) NULL ,
			`base_price` NUMERIC(10,5) NULL
			) ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}
	
	
	// On vérifie si la table des lignes n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . "'") != EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_INVOICE_PRE . $store_number . EOI_TABLE_INVOICE_SUF . " (
			`invoice_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`invoice_ref` VARCHAR(45) NOT NULL ,
			`invoice_date_add` DATETIME NULL ,
			`invoice_date_update` DATETIME NULL ,
			`invoice_comment` VARCHAR(150) NULL ,
			`invoice_status` ENUM('Valid','Moderated','Deleted') NOT NULL DEFAULT 'Valid' ,
			`invoice_payment_status` ENUM('Paid','NotYet') NOT NULL DEFAULT 'NotYet' ,
			`store_id` INT NOT NULL ,
			`user_id` INT NOT NULL ,
			`billing_adress_id` INT NOT NULL ,
			`delivery_adress_id` INT NOT NULL ,
			`payment_method` VARCHAR(45) NULL ,
			`payment_method_module_code` VARCHAR(45) NULL ,
			`delivery_method` VARCHAR(45) NULL ,
			`delivery_method_module_code` VARCHAR(45) NULL ,
			`total_qty_ordered` INT NOT NULL ,
			`discount_code` VARCHAR(45) NULL ,
			`total_discount` NUMERIC(10,5) NULL ,
			`base_products` NUMERIC(10,5) NULL ,
			`base_shipping` NUMERIC(10,5) NULL ,
			`base_wrapping` NUMERIC(10,5) NULL ,
			`base_grand_total` NUMERIC(10,5) NULL ,
			`total_paid` NUMERIC(10,5) NULL ,
			`total_paid_real` NUMERIC(10,5) NULL ,
			`total_products` NUMERIC(10,5) NULL ,
			`total_shipping` NUMERIC(10,5) NULL ,
			`total_wrapping` NUMERIC(10,5) NULL ,
			`grand_total` NUMERIC(10,5) NULL ,
			`tax_total` NUMERIC(10,5) NULL ,
			`currency` ENUM('€','$') NULL ,
			`currency_in_usd` NUMERIC(10,5) NULL ,
			`ip_adress` VARCHAR(45) NULL ,
			`secure_key` VARCHAR(45) NULL
			) ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}
	
		// On vérifie si la table store n'existe pas
	if( $wpdb->get_var("SHOW TABLES LIKE '" . EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF . "'") != EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF)
	{
		// On construit la requete SQL de création de table
		$sql = 
			"CREATE TABLE " . EOI_TABLE_STORE_PRE . $store_number . EOI_TABLE_STORE_SUF . " (
			`store_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`store_name` VARCHAR(45) NOT NULL ,
			`store_tax_number` VARCHAR(45) NOT NULL ,
			`store_email` VARCHAR(60) NOT NULL ,
			`store_adress_id` INT NOT NULL ,
			`society_type` ENUM('SARL','SAS','SA') ,
			`society_capital` INT ,
			`store_accept_check` TINYINT(1),
			`rib_fr_bank` VARCHAR(5) NULL ,
			`rib_fr_register` VARCHAR(5) NULL ,
			`rib_fr_account` VARCHAR(5) NULL ,
			`rib_fr_key` VARCHAR(5) NULL ,
			`rib_IBAN` VARCHAR(27) NULL ,
			`rib_BIC` VARCHAR(15) NULL
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// Execution de la requete
		$wpdb->query($sql);
	}
}

// Permet la mise à jour de la base de données
function eoinvoice_db_update()
{
	global $wpdb;
	
	// V1
	if(!get_option('eoinvoice_db_version'))
	{add_option('eoinvoice_db_version', 1);}
		
	// V2
	if(get_option('eoinvoice_db_version') < 2)
	{
		// On construit la requête SQL de mise à jour
		// Ajout de 3 champs à la table des gestionnaires (état, date d'ajout, date de modification d'état)
		$sql =
			"ALTER TABLE " . EOI_TABLE_USER_STORE . " ADD
			`state` ENUM('Active','Deleted') NOT NULL, ADD
			`add_date` DATETIME NULL, ADD
			`update_date` DATETIME NULL;";
		
		// Execution de la requete
		$wpdb->query($sql);
		
		// Préparation de la requête
		$sql = "UPDATE " . EOI_TABLE_USER_STORE . " SET state=Active;";
		
		// Execution de la requete
		$wpdb->query($sql);
		
		update_option('eoinvoice_db_version', 2);
	}
	
	// V3
	if(get_option('eoinvoice_db_version') < 3)
	{
		// On veut lister le nombre de magasins afin d'appliquer des modifications
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		$store_list_object = new eoinvoice_store_list_table();
		// Combien de magasins ?
		$loop = $store_list_object->how_many_stores();
		
		// Pour chaque magasin
		for($i = 1; $i <= $loop; $i++)
		{
			// On construit la requête SQL de mise à jour
			// Ajout de 3 champs à la table de magasin (type de société, capital de la société, accepte les chèques ou pas)
			$sql =
				"ALTER TABLE " . EOI_TABLE_STORE_PRE . $i . EOI_TABLE_STORE_SUF . " ADD
				`society_type` ENUM('SARL','SAS','SA'), ADD
				`society_capital` INT, ADD
				`store_accept_check` TINYINT(1);";
			
			// Execution de la requete
			$wpdb->query($sql);
		}
		
		// On construit la requête SQL de mise à jour
		// Ajout d'un champ fax à la table d'adresse
		$sql =
			"ALTER TABLE " . EOI_TABLE_ADRESS . " ADD
			`telecopy` VARCHAR(20);";

		// Execution de la requete
		$wpdb->query($sql);
		
		update_option('eoinvoice_db_version', 3);
	}
	
	// V4
	if(get_option('eoinvoice_db_version') < 4)
	{
		// On veut lister le nombre de magasins afin d'appliquer des modifications
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		$store_list_object = new eoinvoice_store_list_table();
		// Combien de magasins ?
		$loop = $store_list_object->how_many_stores();
		
		// Pour chaque magasin
		for($i = 1; $i <= $loop; $i++)
		{
			// On construit la requête SQL de mise à jour
			// Ajout de 3 champs à la table de magasin (type de société, capital de la société, accepte les chèques ou pas)
			$sql =
				"ALTER TABLE " . EOI_TABLE_STORE_PRE . $i . EOI_TABLE_STORE_SUF . " ADD
				`rib_fr_bank` VARCHAR(5) NULL , ADD
				`rib_fr_register` VARCHAR(5) NULL , ADD
				`rib_fr_account` VARCHAR(5) NULL , ADD
				`rib_fr_key` VARCHAR(5) NULL , ADD
				`rib_IBAN` VARCHAR(27) NULL , ADD
				`rib_BIC` VARCHAR(15) NULL;";
			
			// Execution de la requete
			$wpdb->query($sql);
		}
		
		update_option('eoinvoice_db_version', 4);
	}
	
	// V5
	if(get_option('eoinvoice_db_version') < 5)
	{	
		// On construit la requête SQL de mise à jour
		// Ajout d'un champ fax à la table d'adresse
		$sql =
			"ALTER TABLE " . EOI_TABLE_STORE_LIST . " ADD
			`store_uniqid` VARCHAR(32) NULL , ADD
			`store_xml_import_key` BLOB NULL;";

		// Execution de la requete
		$wpdb->query($sql);
		
		update_option('eoinvoice_db_version', 5);
	}
	
	// V6
	if(get_option('eoinvoice_db_version') < 6)
	{
		// On construit la requete SQL de création de la table d'historisation des documents
		$sql =
			"CREATE TABLE " . EOI_TABLE_GED_DOCUMENTS . " (
			`id` int(10) unsigned NOT NULL auto_increment,
			`status` enum('Valid','Moderated','Deleted') collate utf8_unicode_ci NOT NULL default 'Valid',
			`dateCreation` datetime NOT NULL,
			`idCreateur` int(10) unsigned NULL,
			`dateSuppression` datetime NULL,
			`idSuppresseur` int(10) unsigned NOT NULL,
			`categorie` varchar(255) collate utf8_unicode_ci NOT NULL,
			`nom` varchar(255) collate utf8_unicode_ci NOT NULL,
			`chemin` varchar(255) collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `status` (`status`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Document management';";
		
		// Execution de la requete
		$wpdb->query($sql);
		
		// On veut lister le nombre de magasins afin d'appliquer des modifications
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		$store_list_object = new eoinvoice_store_list_table();
		// Combien de magasins ?
		$loop = $store_list_object->how_many_stores();
		
		// Pour chaque magasin
		for($i = 1; $i <= $loop; $i++)
		{
			// On construit la requête SQL de mise à jour
			// On change invoice_status en invoice_payment_status
			$sql =
				"ALTER TABLE " . EOI_TABLE_INVOICE_PRE . $i . EOI_TABLE_INVOICE_SUF . " CHANGE
				`invoice_status`  `invoice_payment_status` ENUM(  'Paid',  'NotYet' ) DEFAULT 'NotYet';";
			
			// Execution de la requete
			$wpdb->query($sql);
			
			// On construit la requête SQL de mise à jour
			// Ajout d'un invoice_status
			$sql =
				"ALTER TABLE " . EOI_TABLE_INVOICE_PRE . $i . EOI_TABLE_INVOICE_SUF . " ADD
				`invoice_status` ENUM(  'Valid',  'Moderated', 'Deleted' ) DEFAULT 'Valid';";
				
			// Execution de la requete
			$wpdb->query($sql);
		}
		
			// On construit la requête SQL de mise à jour
			// Ajout d'un invoice_status
			$sql =
				"ALTER TABLE " . EOI_TABLE_STORE_LIST . " ADD
				`store_status` ENUM(  'Active',  'Moderated', 'Deleted' ) DEFAULT 'Active';";
				
			// Execution de la requete
			$wpdb->query($sql);
		
		update_option('eoinvoice_db_version', 6);
	}
}

?>
