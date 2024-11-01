<?php
// DEFINITION CLASSE Front

class eoinvoice_front
{
	var $invoice_object;
	var $tools_object;
	var $odt_export_object;
	var $html_export_object;
	var $pdf_export_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_front()
	{
		// On charge les classes nécessaires pour la gestion et la lecture des tables en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		// Pour l'export HTML / ODT / PDF
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_export_odt.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_export_pdf.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_export_html.class.php');

		// Instanciation
		$this->invoice_object = new eoinvoice_invoice_table();
		$this->tools_object = new eoinvoice_tools();
		$this->odt_export_object = new eoinvoice_export_odt();
		$this->pdf_export_object = new eoinvoice_export_pdf();
		$this->html_export_object = new eoinvoice_export_html();
	}
	
	// METHODES
	
	// Affichage de la page de listing des factures
	// Si c'est le client qui entre sur cette page, alors on n'affiche que ses factures
	function eoinvoice_front_page()
	{			
		// Si l'utilisateur est gestionnaire d'un seul magasin, on le connecte directement
		$this->tools_object->single_store_autologin();
		
		// On contrôle si il y a eu deconnexion
		$this->tools_object->logout_check();
		
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		
		// Si on est passé par l'étape de sélection du magasin / que l'utilisateur en cours est admin
		if((isset($_POST['eoinvoice_selected_store']) || isset($_SESSION[$session_store_word])))
		{
			// On retiens le magasin selectionné
			if(isset($_POST['eoinvoice_selected_store']))
			{$_SESSION[$session_store_word] = $_POST['eoinvoice_selected_store'];}
			$store_selected = $_SESSION[$session_store_word];

			// On contrôle si l'utilisateur à confirmé le paiement d'une facture
			if(isset($_POST['markaspaid']))
			{
				$control = $this->invoice_object->mark_as_paid($store_selected,$_POST['paid_invoice']);
				if($control != 1){die($control);}
			}

			// On contrôle si l'utilisateur à demandé une consultation
			// HTML
			if(isset($_GET['exporthtml']) && $_GET['exporthtml'] > 0)
			{
				// On affiche la facture en html
				$this->html_export_object->invoice_export($store_selected, $_GET['exporthtml']);
			}
			else
			{
				// Sinon on affiche la page normale 
				?>
				<div class="wrap">
					<?php echo "<h2>" . __( 'Consultation de factures', 'eoinvoice_trdom' ) . "</h2>";?>
					<?php
						// Affichage panneau magasin
						$this->tools_object->display_logout_form($store_selected);
						// Affichage listing des factures
						$this->eoinvoice_display_listing($store_selected, 10);
					?>
				</div>
				<?php
			}
		}
		else
		{
			// Sinon on affiche la liste de choix d'un magasin
			$this->tools_object->eoinvoice_store_list_page(__( 'Consultation de factures', 'eoinvoice_trdom' ),'consult');
		}
	}
	
	// Affiche le listing des factures avec $nb_rows le nombre de lignes affichées
	function eoinvoice_display_listing($store_number)
	{
		// On récupère le rôle de l'utilisateur
		$role = $this->tools_object->current_user_is();
		
		// Si l'user en cours est l'admin
		if($role == 'admin')
		{
			// On affiche tout
			$invoices_list = $this->invoice_object->get_invoices($store_number, 0);
		}
		else if($role == 'manager')// S'il est manager
		{
			// Si il n'est que client du magasin consulté
			if($this->tools_object->current_user_is_manager_of($store_number) == 0)
			{
				// On affiche ses factures en tant que client
				$invoices_list = $this->invoice_object->get_invoices($store_number, $this->tools_object->eoinvoice_get_current_user_id());
			}
			else // Mais s'il est manager du magasin
			{
				// On affiche les factures émises par le magasin
				$invoices_list = $this->invoice_object->get_invoices($store_number, 0);
			}
		}
		else // S'il est client
		{
			// On affiche en fonction de son id
			$invoices_list = $this->invoice_object->get_invoices($store_number, $this->tools_object->eoinvoice_get_current_user_id());
		}
		
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		// On récupère le magasin en cours
		$store_selected = $_SESSION[$session_store_word];
		
		// On récupère le nombre de chiffres après la virgule désiré
		$round_number_count = get_option('eoinvoice__s' . $store_selected . '_round_number_count');
		
		// Tableau
		?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php echo __( 'R&eacute;f&eacute;rence', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Client', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Date de cr&eacute;ation', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Etat', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Adresse de facturation', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Adresse de livraison', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Total HT', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Remise', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'TVA', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Total TTC', 'eoinvoice_trdom' ) ?></th>
					<th><?php echo __( 'Consultation', 'eoinvoice_trdom' ) ?></th>
				</tr>
			</thead>
		<?php
		
		// Si il y'a au moins une facture
		if(isset($invoices_list[0]))
		{
			for($i = 0; $i < count($invoices_list); $i++)
			{
				// On récupère les infos du client
				$user_array = $this->tools_object->eoinvoice_get_customer_info($this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'user_id'));
				
				// On forme l'identifiant de session
				$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
				// On récupère le magasin en cours
				$store_selected = $_SESSION[$session_store_word];
				
				?>
					<tr>
						<th><?php echo $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'invoice_ref'); ?></th>
						<th><?php echo $user_array[0] . ' ' . $user_array[1] . ' | ' . $user_array[5]; ?></th>
						<th><?php echo $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'invoice_date_add'); ?></th>
						<th style="text-align:center;"><?php if($this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'invoice_payment_status') == 'NotYet'){echo __( 'En attente de r&egrave;glement', 'eoinvoice_trdom') . ' '; ?><form method="post" action=""><div class="button-primary"><input type="submit" name="markaspaid" value="Marquer comme pay&eacute;e" style="border:none;" /><input type="hidden" name="paid_invoice" value="<?php echo $invoices_list[$i]; ?>"/></div></form><?php }else{echo __( 'R&eacute;gl&eacute;e', 'eoinvoice_trdom' );} ?></th>
						<th><?php echo $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'billing_adress_id'); ?></th>
						<th><?php echo $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'delivery_adress_id'); ?></th>
						<th><?php echo round($this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'base_grand_total'), get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'currency'); ?></th>
						<th><?php echo round($this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'total_discount'), get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'currency'); ?></th>
						<th><?php echo round($this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'tax_total'), get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'currency'); ?></th>
						<th><?php echo round($this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'grand_total'), get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $this->invoice_object->get_invoice_element($store_number, $invoices_list[$i], 'currency'); ?></th>
						<th><div class="export_pdf button-primary" id="store<?php echo $store_number; ?>_invoice<?php echo $invoices_list[$i]; ?>" style="float:left; margin-left:2px; color:red;">PDF</div><div class="export_html button-primary" style="float:left; margin-left:2px; color:blue;"><a href="<?php echo 'http://' . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'] . '&amp;exporthtml=' . $invoices_list[$i]; ?>" style="text-decoration:none; color:blue;">HTML</a></div></th>
					</tr>
				<?php
			}
		}
		else
		{
			?>
			<tr>
				<th>
					<div class="updated"><p><strong><?php echo __('Vous n\'avez aucune facture &agrave; consulter.' ); ?></strong></p></div>
				<th>
			<tr>
			<?php
		}
		
		echo '</table>'; // On ferme le tableau
		
		// DIV de sortie d'export PDF/ODT
		echo '<div id="export_output"></div>';
	}
}
?>
