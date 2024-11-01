<?php
class eoinvoice_export_html
{
	// Objet Tools permettant de récupérer les coordonnées magasin / client
	var $tools_object;
	// Objets de gestion de table
	var $invoice_row_object;
	var $invoice_object;
	
	function eoinvoice_export_html()
	{
		// On charge les classes nécessaires pour la lecture des tables de coordonnées en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_row_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		
		// INSTANCIATION
		// Outils divers
		$this->tools_object = new eoinvoice_tools();
		// Gestion de la base de données
		$this->invoice_row_object = new eoinvoice_invoice_row_table();
		$this->invoice_object = new eoinvoice_invoice_table();
	}
	
	// Affichage de la facture en (X)HTML
	function invoice_export($store_selected, $invoice_id)
	{	
		?>
			<div id="eoinvoice_html_export_page">
				<h1 id="eoinvoice_html_title">FACTURE</h1>
				<div id="eoinvoice_html_logo"><?php $this->store_logo($store_selected, $invoice_id); ?></div>
				<div id="eoinvoice_html_store"><?php $this->store_head($store_selected, $invoice_id); ?></div>
				<div id="eoinvoice_html_customer"><?php $this->client_head($store_selected, $invoice_id); ?></div>
				<div id="eoinvoice_html_refdate"><?php $this->invoice_refdate($store_selected, $invoice_id); ?></div>
				<div id="eoinvoice_html_rows"><?php $this->rows($store_selected, $invoice_id); ?></div>
				<div id="eoinvoice_html_total"><?php $this->total($store_selected, $invoice_id); ?></div>
			</div>
		<?php
	}
	
	// En-tête magasin
	function store_head($store_number, $invoice_id)
	{
		// On récupère les infos du magasin
		$store_info_array = $this->tools_object->eoinvoice_get_store_info($store_number);
		// On trie
		$store_adress_id = $store_info_array[0];
		$store_name = $store_info_array[1];
		$store_email = $store_info_array[2];
		$store_tax_number = $store_info_array[3];
		
		// On récupère l'adresse du magasin
		$store_adress_tab = $this->tools_object->eoinvoice_get_adress($store_adress_id);
		// On trie
		$store_street_adress = $store_adress_tab[0];
		$store_suburb = $store_adress_tab[1];
		$store_city = $store_adress_tab[2];
		$store_postcode = $store_adress_tab[3];
		$store_state = $store_adress_tab[4];
		$store_country = $store_adress_tab[5];
		$store_phone = $store_adress_tab[6];
				
		// Et on affiche
		echo '<strong>' . $store_name . '</strong><br />';
		echo $store_street_adress . '<br />';
		if ($store_suburb != ''){echo $store_suburb . '<br />';}
		echo $store_postcode . ' ' . $store_city . '<br />';
		if ($store_state != ''){echo $store_state . '<br />';}
		echo $store_country . '<br />';
		echo $store_phone . '<br /><br />';
		echo $store_email . '<br />';
		echo $store_tax_number;
	}
	
	// Logo du magasin
	function store_logo($store_number, $invoice_id)
	{
		echo 'Logo';
	}
	
	// Référence et date de facturation
	function invoice_refdate($store_number, $invoice_id)
	{	
		// On récupère la référence
		$store_ref = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'invoice_ref');
		// On récupère l'id du magasin
		$invoice_add_date = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'invoice_date_add');
		
		?>
		<table>
			<thead>
				<tr>
					<td>R&eacute;f&eacute;rence</td>
					<td>Date</td>
				</tr>
			</thead>
			<tr>
				<td><?php echo $store_ref; ?></td>
				<td><?php echo $invoice_add_date; ?></td>
			</tr>
		</table>
		<?php
		
	}

	
	// En-tête client
	function client_head($store_number, $invoice_id)
	{
		// On récupère l'id du client
		$customer_id = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'user_id');
		// On récupère les adresses du client dans un tableau
		$customer_info_tab = $this->tools_object->eoinvoice_get_customer_info($customer_id);
		// Adresse facturation
		$customer_billing_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[3]);
		
		// On trie
		$customer_firstname = $customer_info_tab[0];
		$customer_lastname = $customer_info_tab[1];
		
		$customer_street_adress = $customer_billing_adress[0];
		$customer_suburb = $customer_billing_adress[1];
		$customer_city = $customer_billing_adress[2];
		$customer_postcode = $customer_billing_adress[3];
		$customer_state = $customer_billing_adress[4];
		$customer_country = $customer_billing_adress[5];

		// On affiche
		echo $customer_lastname . ' ' . $customer_firstname . '<br />';
		echo $customer_street_adress . '<br />';
		if ($customer_suburb != ''){echo $customer_suburb . '<br />';}
		echo $customer_postcode . ' ' . $customer_city . '<br />';
		if ($customer_state != ''){echo $customer_state . '<br />';}
		echo $customer_country;
	}
	
	// Affiche le tableau des lignes de la facture
	function rows($store_number, $invoice_id)
	{
		// On récupère les id des lignes de cette facture
		$rows_array = $this->invoice_row_object->get_rows_about($store_number, $invoice_id);
		
		// Représentation HTML du tableau
		?>
		<table>
			<thead>
				<tr>
					<td>R&eacute;f&eacute;rence</td>
					<td>Nom</td>
					<td>Description</td>
					<td>Quantit&eacute;</td>
					<td>Prix unitaire HT</td>
					<td>Remise</td>
					<td>Montant TVA (Taux)</td>
					<td>Montant TTC</td>
				</tr>
			</thead>
		<?php

		// Puis on affiche les lignes
		for($i = 0; $i < count($rows_array); $i++)
		{
			$this->row($store_number, $rows_array[$i]);
		}
		
		// Fermeture du tableau
		echo '</table>';
	}
	
	// Affiche un ligne de la facture
	function row($store_number, $row_id)
	{
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		// On récupère le magasin en cours
		$store_selected = $_SESSION[$session_store_word];
		
		// On récupère la ligne en base de données
		$row_array = $this->invoice_row_object->get_invoice_row_array($store_number, $row_id); // sous forme de tableau
		
		// Affichage des valeurs désirées
		?>
			<tr>
				<td><?php echo $row_array['product_reference']; ?></td>
				<td><?php echo $row_array['product_name']; ?></td>
				<td><?php echo $row_array['product_description']; ?></td>
				<td><?php echo $row_array['qty_invoiced']; ?></td>
				<td><?php echo round($row_array['product_base_price'], get_option('eoinvoice__s' . $store_selected . '_round_number_count')); ?></td>
				<td><?php echo round($row_array['discount_amount'], get_option('eoinvoice__s' . $store_selected . '_round_number_count')); ?></td>
				<td><?php echo round($row_array['tax_amount'], get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' (' . round($row_array['tax_percent'], 2). ' %)'; ?></td>
				<td><?php echo round($row_array['price'], get_option('eoinvoice__s' . $store_selected . '_round_number_count')); ?></td>
			</tr>
		<?php
	}
	
	function total($store_number, $invoice_id)
	{
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		// On récupère le magasin en cours
		$store_selected = $_SESSION[$session_store_word];
		
		// On récupère les valeurs nécessaires
		$base_grand_total = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'base_grand_total');
		$total_tax = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'tax_total');
		$grand_total = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'grand_total');
		$currency = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'currency');
		
		?>
		<table>
			<thead>
				<tr>
					<td>Total HT</td>
					<td>Montant total TVA</td>
					<td>Total TTC</td>
				</tr>
			</thead>
			<tr>
				<td><?php echo round($base_grand_total, get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $currency; ?></td>
				<td><?php echo round($total_tax, get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $currency; ?></td>
				<td><?php echo round($grand_total, get_option('eoinvoice__s' . $store_selected . '_round_number_count')) . ' ' . $currency; ?></td>
			</tr>
		</table>
		<?php
		
	}
}
?>
