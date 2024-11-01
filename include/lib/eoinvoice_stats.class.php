<?php

class eoinvoice_stats
{
	var $invoice_table_object;
	var $tools_object;

	function eoinvoice_stats()
	{
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		$this->invoice_table_object = new eoinvoice_invoice_table();
		$this->tools_object = new eoinvoice_tools();
	}
	
	function eoinvoice_stats_page()
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
			
			// Si l'utilisateur est bien manager ou admin
			if($this->tools_object->current_user_is_manager_of($store_selected) || $this->tools_object->current_user_is() == 'admin')
			{
				// Et on affiche la page
				echo '<div class="wrap">';
				echo "<h2>" . __( 'Tableau de bord', 'eoinvoice_trdom' ) . "</h2>";
				
				// Panneau de changement de magasin
				$this->tools_object->display_logout_form($store_selected);
				
				// Statistiques de facturation
				$this->last_five_invoices($store_selected);
				$this->year_totals($store_selected, date(Y));
				$this->month_totals($store_selected, date(Y), date(m));
				$this->day_totals($store_selected, date(Y), date(m), date(d));
				$this->all_time_totals($store_selected);
				echo '</div>';
			}
			else
			{
				// Sinon on affiche la liste de choix d'un magasin
				$this->tools_object->eoinvoice_store_list_page(__( 'Tableau de bord', 'eoinvoice_trdom' ), 'dashboard');
			}
		}
		else
		{
			// Sinon on affiche la liste de choix d'un magasin
			$this->tools_object->eoinvoice_store_list_page(__( 'Tableau de bord', 'eoinvoice_trdom' ), 'dashboard');
		}
	}
	
	// Affiche un tableau avec le total pour un jour donné
	function day_totals($store_number, $year, $month, $day)
	{
		$tab = $this->invoice_table_object->get_day_totals($store_number, $year, $month, $day);
		
		if(!isset($tab[0])){$tab[0] = 0; $tab[1] = 0;}
		
		?>
			<table class="widefat" style="width:150px;">
				<thead>
					<tr><th><?php echo __( 'Aujourd\'hui', 'eoinvoice_trdom' ); ?></th></tr>
				</thead>
				<tr><th style="font-size:18px;"><?php echo $tab[0] . ' € TTC'; ?></th></tr>
				<tr><th style="font-size:16px;"><?php echo $tab[1] . ' € TVA'; ?></th></tr>
			</table>
		<?php
	}
	
	// Affiche un tableau avec le total pour un mois donné
	function month_totals($store_number, $year, $month)
	{
		$tab = $this->invoice_table_object->get_month_totals($store_number, $year, $month);
	
		if(!isset($tab[0])){$tab[0] = 0; $tab[1] = 0;}
	
		?>
			<table class="widefat" style="width:150px;">
				<thead>
					<tr><th><?php echo __( 'Ce mois', 'eoinvoice_trdom' ); ?></th></tr>
				</thead>
				<tr><th style="font-size:18px;"><?php echo $tab[0] . ' € TTC'; ?></th></tr>
				<tr><th style="font-size:16px;"><?php echo $tab[1] . ' € TVA'; ?></th></tr>
			</table>
		<?php
	}
	
	// Affiche un tableau avec le total pour une année donnée
	function year_totals($store_number, $year)
	{
		$tab = $this->invoice_table_object->get_year_totals($store_number, $year);
		
		if(!isset($tab[0])){$tab[0] = 0; $tab[1] = 0;}
		
		?>
			<table class="widefat" style="width:150px;">
				<thead>
					<tr><th><?php echo __( 'Cette ann&eacute;e', 'eoinvoice_trdom' ); ?></th></tr>
				</thead>
				<tr><th style="font-size:18px;"><?php echo $tab[0] . ' € TTC'; ?></th></tr>
				<tr><th style="font-size:16px;"><?php echo $tab[1] . ' € TVA'; ?></th></tr>
			</table>
		<?php
	}
	
	// Affiche un tableau avec le total depuis la création du magasin
	function all_time_totals($store_number)
	{
		$tab = $this->invoice_table_object->get_all_time_totals($store_number);
		
		if(!isset($tab[0])){$tab[0] = 0; $tab[1] = 0;}
		
		?>
			<table class="widefat" style="width:150px;">
				<thead>
					<tr><th><?php echo __( 'Depuis la cr&eacute;ation du magasin', 'eoinvoice_trdom' ); ?></th></tr>
				</thead>
				<tr><th style="font-size:18px;"><?php echo $tab[0] . ' € TTC'; ?></th></tr>
				<tr><th style="font-size:16px;"><?php echo $tab[1] . ' € TVA'; ?></th></tr>
			</table>
		<?php
	}
	
	// Affiche un tableau avec le récapitulatif des 5 dernières factures
	// Nom prénom du client - nbr objets commandés - total ttc
	function last_five_invoices($store_number)
	{
		
		// On récupère les 5 dernières factures
		$tab = $this->invoice_table_object->last_five_invoices($store_number);
		
		?>
			<table class="widefat" style="width:500px;">
				<thead>
					<tr><th><?php echo __( 'Les 5 dernières factures', 'eoinvoice_trdom' ); ?></th><th></th><th></th><th></th></tr>
					<tr><th><?php echo __( 'Ref', 'eoinvoice_trdom' ); ?></th><th><?php echo __( 'Client', 'eoinvoice_trdom' ); ?></th><th><?php echo __( 'Qté', 'eoinvoice_trdom' ); ?></th><th><?php echo __( 'Total', 'eoinvoice_trdom' ); ?></th></tr>
				</thead>
		<?php
		
		if(!isset($tab))
		{echo '<tr><th>Aucune facture</th><th></th><th></th><th></th></tr>';}
		
		// Pour chacune des 5 factures
		for($i = 0; $i < count($tab); $i++)
		{
			// On récupère nom et prénom du client
			$customer_info = $this->tools_object->eoinvoice_get_customer_info($tab[$i][1]);
	
			?>
				<tr>
					<th><?php echo $tab[$i][0]; ?></th>
					<th><?php echo $customer_info[0] . ' ' . $customer_info[1]; ?></th>
					<th><?php echo $tab[$i][2]; ?></th>
					<th><?php echo $tab[$i][3]; ?></th>
				</tr>
			<?php
		}
		
		echo '</table>';
	}
	
}
?>
