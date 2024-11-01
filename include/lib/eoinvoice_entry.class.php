<?php
// DEFINITION CLASSE Entry

class eoinvoice_entry
{
	// Objet Settings nous permettant de récupérer les coordonnées magasin
	var $tools_object;
	// Objet View nous permettant entre autre de faire les calculs et de prévisualiser la facture après remplissage
	var $view_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_entry()
	{
		// On charge les classes nécessaires pour la lecture des tables de coordonnées en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		// On charge la classe view_save pour la prévisualisation et l'enregistrement de la facture
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_view_save.class.php');
		
		$this->tools_object = new eoinvoice_tools();
		$this->view_save_object = new eoinvoice_view_save();
	}
	
	// METHODES
	
	// Page de création de nouvelle facture
	// TODO : A transformer en template
	function eoinvoice_entry_page()
	{
		// Si l'utilisateur est gestionnaire d'un seul magasin, on le connecte directement
		$this->tools_object->single_store_autologin();
		
		// On contrôle si il y a eu deconnexion
		$this->tools_object->logout_check();
		
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		
		// Si on est passé par l'étape de sélection du magasin, alors affichage des options
		if(isset($_POST['eoinvoice_selected_store']) || isset($_SESSION[$session_store_word]))
		{
			// On retiens le magasin selectionné
			if(isset($_POST['eoinvoice_selected_store']))
			{$_SESSION[$session_store_word] = $_POST['eoinvoice_selected_store'];}
			$store_selected = $_SESSION[$session_store_word];
		
			// Contrôle des chiffres et données entrées
			$entry_check_array = $this->invoice_regex_check();
			
			// Si l'utilisateur clique sur "Prévisualiser" ou sur "Enregistrer", et que le formulaire est bon
			if ((isset($_POST['preview']) || isset($_POST['save'])) && $entry_check_array[0])
			{
				// On affiche la page de consultation/enregistrement de facture
				$this->view_save_object->eoinvoice_view_save_page();
			} 	// Sinon on affiche la page formulaire
			else
			{
				// INFOS MAGASIN
				// On récupère les infos du magasin dans un tableau
				$store_info_tab = $this->tools_object->eoinvoice_get_store_info($store_selected);
				// Puis ses coordonnées dans un autre tableau
				$store_adress_tab = $this->tools_object->eoinvoice_get_adress($store_info_tab[0]);
				
				// INFOS CLIENT
				// On regarde si un client est sélectionné
				if(isset($_POST['eoinvoice_selected_customer']))
				{$selected_customer = $_POST['eoinvoice_selected_customer'];}
				else{$selected_customer = 1;}
				
				// Affichage de la page
				
				// Sauvegarde le numéro de ligne de la facture en cours pour l'appel JQuery?>
				<script>var chariot_input = <?php echo ($this->view_save_object->entry_row_qty()+1)?>;</script>
				
				<div class="wrap">
					<?php echo "<h2>" . __( 'Nouvelle facture', 'eoinvoice_trdom' ) . "</h2>";
					
					// Panneau de changement de magasin
					$this->tools_object->display_logout_form($store_selected);
					
					// On affiche l(es) erreur(s) de saisie si erreur(s) il y'a
					// et si le formulaire a été posté
					if(!$entry_check_array[0] && isset($_POST['preview']))
					{$this->invoice_regex_display($entry_check_array);}
					
					?>
					<form method="post" action="">
					<input type="hidden" name="eoinvoice_selected_store" value="<?php echo $store_selected; ?>"/>
						<div>
								<table class="widefat" style="width:200px;">
									<thead><tr><th><?php echo __( 'Client', 'eoinvoice_trdom' ) ?></th></tr></thead>
									<tr><th><?php $this->display_customers_list();?></th></tr>
								</table>
						</div>
						<div>
							<table id="invoice_table" class="widefat" width="500px;">
								<thead>
									<tr>
										<th><?php echo __( 'D&eacute;tail', 'eoinvoice_trdom' ); ?></th><th></th><th></th><th></th>
										<th></th>
										<th></th><th></th><th></th>
									</tr>
									<tr>
										<th class="header"><?php echo __("R&eacute;f&eacute;rence *", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Nom", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Description", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Poids *", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Quantit&eacute; *", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Taxe (%) *", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Remise (%)", 'eoinvoice_trdom'); ?></th>
										<th class="header"><?php echo __("Prix unité HT *", 'eoinvoice_trdom'); ?></th>
									</tr>
								</thead>
								<?php $this->view_save_object->display_rows(TRUE); ?>
									<tr>
										<th>
											<input type="button" id="add_line" class="button" value="<?php echo __('Ajouter une ligne', 'eoinvoice_trdom'); ?>" />
										</th>
										<th></th>
										<th></th>
										<th></th>
										<th id="total_qty"></th>
										<th id="total_weight"></th>
										<th></th>
										<th id="total_discount"></th>
									</tr>
							</table>
							<p class="submit">
								<input type="submit" class="button-primary" name="preview" value="<?php echo __('Prévisualiser', 'eoinvoice_trdom' ); ?>" />
							</p>
						</div>
					</form>
				</div>
				<?php
			}
		}
		else
		{
			// Sinon on affiche la liste de choix d'un magasin
			$this->tools_object->eoinvoice_store_list_page(__( 'Nouvelle facture', 'eoinvoice_trdom' ), 'entry');
		}
	}
	
	// Affiche un menu déroulant de sélection du client
	function display_customers_list()
	{
		// On récupère le nom, le prénom et les coordonnées de tous nos clients dans un tableau
		$customers_array = $this->tools_object->eoinvoice_get_users_list();
		
		if(isset($_POST['eoinvoice_selected_customer']))
		{$selected_customer = $_POST['eoinvoice_selected_customer'];}
		
		// On ouvre la liste
		echo '<select name="eoinvoice_selected_customer">';
		for($i = 0; $i < (count($customers_array)); $i++)
		{
			if (!$this->tools_object->user_can_legacy($customers_array[$i][4], ADMIN_LEVEL))
			{
				echo '<option value=\'' . $customers_array[$i][4] . '\'';
				// On regarde si il est sélectionné
				if($selected_customer == $customers_array[$i][4])
				{echo ' selected';}	// Et on adapte en conséquence
				echo '>';
				echo $customers_array[$i][0] . " " . $customers_array[$i][1] . " | login: " . $customers_array[$i][5];
				echo '</option>';
			}
		}
		// On ferme notre liste
		echo '</select>';
	}
	
	// Methode permettant de vérifier les champs de la facture avant prévisualisation
	// Retourne un tableau à deux dimensions
	// [0] contrôle global d'erreur
	// [x][0] $bad_ref [x][1] $bad_weight 		[x][2] $bad_qty
	// [x][3] $bad_tax [x][4] $product_discount [x][5] $bad_price
	// avec x le numéro de la ligne
	function invoice_regex_check()
	{
		// Nombre de lignes remplies
		$row_qty = $this->view_save_object->entry_row_qty();
		
		// Tableau de résultats du contrôle de regex de la facture
		$invoice_regex_array = array();
		
		// On teste les lignes remplies
		for($i = 1; $i <= $row_qty; $i++)
		{
			// Test des expressions régulières
			if (!preg_match("#[0-9a-zA-Z]#", ($this->tools_object->varSanitizer($_POST['row_' . $i . '_product_ref']))))
			{$bad_ref = TRUE; $error = TRUE;}else{$bad_ref = FALSE;}
			if (!preg_match("#^[0-9]{1,}[.,]?[0-9]{0,}$#", ($this->tools_object->varSanitizer($_POST['row_' . $i . '_product_weight']))))
			{$bad_weight = TRUE; $error = TRUE;}else{$bad_weight = FALSE;}
			if (!preg_match("#^[0-9]{1,}$#", ($this->tools_object->varSanitizer($_POST['row_' . $i . '_product_qty']))))
			{$bad_qty = TRUE; $error = TRUE;}else{$bad_qty = FALSE;}
			if (!preg_match("#^[0-9]{1,}[.,]?[0-9]{0,}$#", ($this->tools_object->varSanitizer($_POST['row_' . $i . '_product_tax_percent']))))
			{$bad_tax = TRUE; $error = TRUE;}else{$bad_tax = FALSE;}
			if (!preg_match("#^[0-9]{1,}[.,]?[0-9]{0,}$#", ($this->tools_object->varSanitizer($_POST['row_' . $i . '_product_discount']))))
			{$bad_discount = TRUE; $error = TRUE;}else{$bad_discount = FALSE;}
			if (!preg_match("#^[0-9]{1,}[.,]?[0-9]{0,}$#", ($this->tools_object->varSanitizer($_POST['row_' . $i . '_product_base_price']))))
			{$bad_price = TRUE; $error = TRUE;}else{$bad_price = FALSE;}
			
			// Tableau de résultats du contrôle de regex de la ligne
			$row_regex_array = array($bad_ref, $bad_weight, $bad_qty, $bad_tax, $bad_discount, $bad_price);
			// Qu'on place dans le tableau de contrôle du regex de la facture
			$invoice_regex_array[$i] = $row_regex_array;
		}
		
		// Contrôle global d'erreur
		if (!($row_qty > 0) || $error)
		{$invoice_regex_array[0] = FALSE;}else{$invoice_regex_array[0] = TRUE;}
		
		// On retourne le tableau
		return $invoice_regex_array;
	}
	
	// Méthode permettant l'affichage et la mise en forme des erreurs de saisies
	// à partir du tableau retourné par la fonction invoice_regex_check()
	function invoice_regex_display($invoice_regex_array)
	{
		$row_qty = count($invoice_regex_array)-1;
		
		// Entête / Panneau erreur
		echo '<div class="error"><p style="color:red;"><strong>' . __('Attention', 'eoinvoice_trdom') . '</strong></p>';
		
		if (!($row_qty > 0))
		{echo '<p>' . $i . __("Aucune ligne n'est remplie", 'eoinvoice_trdom') . '</p>';}
		
		// On teste les lignes remplies
		for($i = 1; $i <= $row_qty; $i++)
		{
			echo '<p><strong>'. __("Ligne ", 'eoinvoice_trdom') . $i . '</strong><br />';
			
			$error = FALSE;
				
			for($j = 0; $j < count($invoice_regex_array[$i]); $j++)
			{
				if($invoice_regex_array[$i][$j])
				{$error = TRUE;}
			}
			if($error != TRUE){echo "OK" . '<br />';}
			
				
			if ($invoice_regex_array[$i][0])
			{echo __("Référence non conforme ou manquant", 'eoinvoice_trdom') . '<br />';}
			if ($invoice_regex_array[$i][1])
			{echo __("Poids non conforme ou manquant", 'eoinvoice_trdom') . '<br />';}
			if ($invoice_regex_array[$i][2])
			{echo __("Quantit&eacute; non conforme ou manquant", 'eoinvoice_trdom') . '<br />';}
			if ($invoice_regex_array[$i][3])
			{echo __("TVA non conforme ou manquant", 'eoinvoice_trdom') . '<br />';}
			if ($invoice_regex_array[$i][4])
			{echo __("Remise non conforme ou manquant", 'eoinvoice_trdom') . '<br />';}
			if ($invoice_regex_array[$i][5])
			{echo __("Prix unitaire HT non conforme ou manquant", 'eoinvoice_trdom') . '<br />';}
			
			echo '</p>';
		}
		
		?>
			</div>
		<?php
	}

// FIN CLASSE
}
?>
