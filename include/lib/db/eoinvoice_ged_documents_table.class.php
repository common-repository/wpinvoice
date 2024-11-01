<?php // DEFINITION CLASSE ged_documents
// Historisation des exports document

class eoinvoice_ged_documents_table
{
	// CONSTRUCTEUR

	function eoinvoice_ged_documents_table()
	{
	}
	
	function add_a_mark($creator_id, $category, $name, $path)
	{
		// Variable db wordpress globale
		global $wpdb;
		
		// Préparation de la requête
		$sql =
			"INSERT INTO " . EOI_TABLE_GED_DOCUMENTS  . " (
			status,
			dateCreation,
			idCreateur,
			categorie,
			nom,
			chemin ) VALUES (
			'Valid', 
			NOW(), " .
			$creator_id . ", '" .
			$category . "', '" .
			$name . "', '" .
			$path . "');";

		// Filtrage injection puis execution
		if($wpdb->query($wpdb->prepare($sql)) === FALSE)
		{return __( 'Erreur lors de l\'historisation du document en base de donn&eacute;es', 'eoinvoice_trdom' );}
		else
		{return 1;}
	}
	
	
}
