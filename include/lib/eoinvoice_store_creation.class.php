<?php
// DEFINITION CLASSE eoinvoice_store_creation
// Interface de création de magasin
// Accessible par les utilisateurs n'étant pas encore manager ainsi qu'à l'administrateur

class eoinvoice_store_creation
{
	// Variables de classe
	var $adress_table_object;
	var $store_table_object;
	var $store_list_table_object;
	var $user_store_table_object;
	var $tools_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_store_creation()
	{
		// On charge les classes nécessaires pour la gestion et la lecture des tables en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_user_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');

		// On instancie un objet de chacune de ces classes
		$this->adress_table_object = new eoinvoice_adress_table();
		$this->store_table_object = new eoinvoice_store_table();
		$this->user_store_table_object = new eoinvoice_user_store_table();
		$this->store_list_table_object = new eoinvoice_store_list_table();
		$this->tools_object = new eoinvoice_tools();
	}
	
	// METHODES
	
	function eoinvoice_store_creation_page()
	{	
		// On regarde si le formulaire a été envoyé
		if($_POST['eoinvoice_hidden'] == 'Y')
		{
			// Si c'est le cas
			// On crée le nouveau magasin avec comme manager la personne choisi dans la liste
			if(isset($_POST['eoinvoice_selected_customer']))
			{$selected_customer = $_POST['eoinvoice_selected_customer'];}
			$this->create_store($selected_customer);
			
			// Entête de confirmation de création du magasin
			?><div class="updated"><p><strong><?php echo __('Magasin n°' . $this->store_list_table_object->how_many_stores() . ' cr&eacute;&eacute;', 'eoinvoice_trdom' ); ?></strong></p></div><?php 
		}
		
		// Affichage classique de la page
		?>
			<div class="wrap">
				<?php echo "<h2>" . __( 'Cr&eacute;ation d\'un nouveau magasin', 'eoinvoice_trdom' ) . "</h2>"; ?>
				<form method="POST" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					<input type="hidden" name="eoinvoice_hidden" value="Y">
					<div>
					<?php
						// Si c'est l'admin, on affiche la liste de tous les utilisateurs
						if(current_user_can(ADMIN_CAPABILITY))
						{?>
							<table class="widefat" style="width:200px;">
								<thead><tr><th><?php echo __( 'Gestionnaire principal du magasin', 'eoinvoice_trdom' ) ?></th></tr></thead>
								<tr><th><?php $this->tools_object->display_user_list();?></th></tr>
							</table>
						<?php } ?>
					</div>
				<p class="submit"><input type="submit" class="button-primary" name="Submit" value="<?php echo __('Cr&eacute;er le magasin', 'eoinvoice_trdom' ); ?>" /></p>
				</form>
			</div>
		<?php
	}
	
	// Crée un nouveau magasin
	function create_store($user_id)
	{
		// On cherche le numéro du nouveau store à créer
		$store_number = $this->store_list_table_object->how_many_stores()+1;
		// On inclut le code utile pour la création du groupement de tables nécessaire à la création du store
		require_once(EOINVOICE_HOME_DIR . 'include/module/install/creationTables.php');
		// Crée les tables du store avec l'identifiant store_number
		eoinvoice_db_creation_store($store_number);
		// Ajoute le store fraichement crée à la liste de tous les stores
		$control = $this->store_list_table_object->add_store_to_list();
		if($control != 1){die($control);}
		// Définit l'utilisateur référencé par user_id comme étant manager du nouveau store créé
		// Si c'est l'utilisateur qui crée le magasin, il est le manager
		if(!current_user_can(ADMIN_CAPABILITY))
		{$user_id = $this->tools_object->eoinvoice_get_current_user_id();}
		$control = $this->user_store_table_object->link_user_as_manager($user_id, $store_number);
		if($control != 1){die($control);}
	}
}
?>
