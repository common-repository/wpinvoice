<?php
// DEFINITION CLASSE view_save

class eoinvoice_view_save
{
	// Objet Tools permettant de récupérer les coordonnées magasin
	var $tools_object;
	// Objets de gestion de table
	var $invoice_row_object;
	var $invoice_object;
	
	
	// CONSTRUCTEUR

	function eoinvoice_view_save()
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
	
	// METHODES
	
	// Page de création de nouvelle facture
	// TODO : A transformer en template
	function eoinvoice_view_save_page()
	{
		// On stocke le magasin selectionné
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		
		// On retiens le magasin selectionné
		$store_selected = $_SESSION[$session_store_word];
	
		// INFOS MAGASIN
		// On récupère les infos du magasin dans un tableau
		$store_info_tab = $this->tools_object->eoinvoice_get_store_info($store_selected);
		// Puis ses coordonnées dans un autre tableau
		$store_adress_tab = $this->tools_object->eoinvoice_get_adress($store_info_tab[0]);
		
		// INFOS CLIENT
		// On regarde si un client est sélectionné
		if(isset($_POST['eoinvoice_selected_customer']))
		{$selected_customer = $_POST['eoinvoice_selected_customer'];}
		// On récupère les adresses du client dans un tableau
		$customer_info_tab = $this->tools_object->eoinvoice_get_customer_info($selected_customer);
		// Adresse livraison
		$customer_delivery_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[2]);
		// Adresse facturation
		$customer_billing_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[3]);
		
		
		
		// On regarde il y a eu enregistrement de la facture
		if(isset($_POST['save']))
		{
			// On crée la facture
			$this->create_invoice($store_selected);
			// On affiche l'entête de confirmation d'enregistrement
			?><div class="updated"><p><strong><?php echo __('Facture enregistrée.' ); ?></strong></p></div><?php 
		}
		
		// AFFICHAGE DE LA PAGE
		
		?>
		<div class="wrap">
			<?php echo "<h2>" . __( 'Facture', 'eoinvoice_trdom' ) . "</h2>";?>
			<div>
				<table class="widefat" style="width:150px;">
					<thead><tr><th><?php echo __( 'Client', 'eoinvoice_trdom' ) ?></th></tr></thead>
					<tr><th><?php echo $customer_info_tab[0] . ' ' . $customer_info_tab[1];?></th></tr>
				</table>
				<div style="float:left;">
					<table class="widefat" style="width:300px;">
						<thead><tr><th><?php echo __( 'Coordonn&eacute;es magasin', 'eoinvoice_trdom' ) ?></th><th></th></tr></thead>	
						<tr><th><?php echo __( 'Nom de l\'entreprise', 'eoinvoice_trdom' ); ?></th><th id="store_name"><?php echo $store_info_tab[1] ?></th></tr>
						<tr><th><?php echo __( 'Adresse', 'eoinvoice_trdom' ); ?></th><th id="store_street_adress"><?php echo $store_adress_tab[0] ?></th></tr>
						<tr><th><?php echo __( 'Quartier', 'eoinvoice_trdom' ); ?></th><th id="store_suburb"><?php echo $store_adress_tab[1] ?></th></tr>
						<tr><th><?php echo __( 'Ville', 'eoinvoice_trdom' ); ?></th><th id="store_city"><?php echo $store_adress_tab[2] ?></th></tr>
 						<tr><th><?php echo __( 'Etat', 'eoinvoice_trdom' ); ?></th><th id="store_postcode"><?php echo $store_adress_tab[4] ?></th></tr>
						<tr><th><?php echo __( 'Pays', 'eoinvoice_trdom' ); ?></th><th id="store_country"><?php echo $store_adress_tab[5] ?></th></tr>
						<tr><th><?php echo __( 'T&eacute;l&eacute;phone', 'eoinvoice_trdom' ); ?></th><th id="store_phone"><?php echo $store_adress_tab[6] ?></th></tr>
						<tr><th><?php echo __( 'Num&eacute;ro TVA intracommunautaire', 'eoinvoice_trdom' ); ?></th><th id="store_tax_number"><?php echo $store_info_tab[3] ?></th></tr>
						<tr><th><?php echo __( 'E-mail', 'eoinvoice_trdom' ); ?></th><th id="store_email"><?php echo $store_info_tab[2] ?></th></tr>
					</table>
				</div>
				<div style="float:left;">
					<table class="widefat" style="width:300px; margin-left:10px;" >
						<thead><tr><th><?php echo __( 'Coordonn&eacute;es de facturation', 'eoinvoice_trdom' ); ?></th><th></th></tr></thead>
						<tr><th><?php echo __( 'Nom', 'eoinvoice_trdom' ); ?></th><th id="billing_last_name"><?php echo $customer_info_tab[1] ?></th></tr>
						<tr><th><?php echo __( 'Prenom', 'eoinvoice_trdom' ); ?></th><th id="billing_first_name"><?php echo $customer_info_tab[0] ?></th></tr>
						<tr><th><?php echo __( 'Adresse', 'eoinvoice_trdom' ); ?></th><th id="billing_street_adress"><?php echo $customer_billing_adress[0] ?></th></tr>
						<tr><th><?php echo __( 'Quartier', 'eoinvoice_trdom' ); ?></th><th id="billing_suburb"><?php echo $customer_billing_adress[1] ?></th></tr>
						<tr><th><?php echo __( 'Ville', 'eoinvoice_trdom' ); ?></th><th id="billing_city"><?php echo $customer_billing_adress[2] ?></th></tr>
						<tr><th><?php echo __( 'Code postal', 'eoinvoice_trdom' ); ?></th><th id="billing_postcode"><?php echo $customer_billing_adress[3] ?></th></tr>
						<tr><th><?php echo __( 'Etat', 'eoinvoice_trdom' ); ?></th><th id="billing_state"><?php echo $customer_billing_adress[4] ?></th></tr>
						<tr><th><?php echo __( 'Pays', 'eoinvoice_trdom' ); ?></th><th id="billing_country"><?php echo $customer_billing_adress[5] ?></th></tr>
						<tr><th><?php echo __( 'T&eacute;l&eacute;phone', 'eoinvoice_trdom' ); ?></th><th id="billing_phone"><?php echo $customer_billing_adress[6] ?></th></tr>
					</table>
				</div>
				<div style="float:left;">
					<table class="widefat" style="width:300px; margin-left:10px;" >
						<thead><tr><th><?php echo __( 'Coordonn&eacute;es de livraison', 'eoinvoice_trdom' ); ?></th><th></th></tr></thead>
						<tr><th><?php echo __( 'Nom', 'eoinvoice_trdom' ); ?></th><th id="delivery_last_name"><?php echo $customer_info_tab[1] ?></th></tr>
						<tr><th><?php echo __( 'Prenom', 'eoinvoice_trdom' ); ?></th><th id="delivery_first_name"><?php echo $customer_info_tab[0] ?></th></tr>
						<tr><th><?php echo __( 'Adresse', 'eoinvoice_trdom' ); ?></th><th id="delivery_street_adress"><?php echo $customer_delivery_adress[0] ?></th></tr>
						<tr><th><?php echo __( 'Quartier', 'eoinvoice_trdom' ); ?></th><th id="delivery_suburb"><?php echo $customer_delivery_adress[1] ?></th></tr>
						<tr><th><?php echo __( 'Ville', 'eoinvoice_trdom' ); ?></th><th id="delivery_city"><?php echo $customer_delivery_adress[2] ?></th></tr>
						<tr><th><?php echo __( 'Code postal', 'eoinvoice_trdom' ); ?></th><th id="delivery_postcode"><?php echo $customer_delivery_adress[3] ?></th></tr>
						<tr><th><?php echo __( 'Etat', 'eoinvoice_trdom' ); ?></th><th  id="delivery_state"><?php echo $customer_delivery_adress[4] ?></th></tr>
						<tr><th><?php echo __( 'Pays', 'eoinvoice_trdom' ); ?></th><th id="delivery_country"><?php echo $customer_delivery_adress[5] ?></th></tr>
						<tr><th><?php echo __( 'T&eacute;l&eacute;phone', 'eoinvoice_trdom' ); ?></th><th id="delivery_phone"><?php echo $customer_delivery_adress[6] ?></th></tr>
					</table>
				</div>
			</div>
			<div>
				<form method="post">
				<input type="hidden" name="eoinvoice_selected_store" value="<?php echo $store_selected; ?>">
					<table id="invoice_table" class="widefat">
						<thead>
							<tr>
								<th></th><th></th><th></th><th></th><th></th>
								<th><?php echo __( 'D&eacute;tail', 'eoinvoice_trdom' ); ?></th>
								<th></th><th></th><th></th><th></th><th></th><th></th>
							</tr>
							<tr>
								<th class="header"><?php echo __("R&eacute;f&eacute;rence", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Nom", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Description", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Poids", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Quantit&eacute;", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Poids total", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Taxe (%)", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Remise (%)", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Prix unité HT", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Prix unité TTC", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Prix total HT", 'eoinvoice_trdom'); ?></th>
								<th class="header"><?php echo __("Prix total TTC", 'eoinvoice_trdom'); ?></th>
							</tr>
						</thead>
							<?php $this->display_rows(FALSE); ?>
							<?php $this->display_totals_row(FALSE); ?>
					</table>
					<?php
						if(!isset($_POST['save']))
						{?>
							<p class="submit">
								<input type="hidden" value="<?php echo $selected_customer; ?>" name="eoinvoice_selected_customer" />
								<input type="submit" class="button-primary" name="modify" value="<?php echo __('Modifier', 'eoinvoice_trdom' ); ?>" />
								<input type="submit" class="button-primary" name="save" value="<?php echo __('Enregistrer', 'eoinvoice_trdom' ); ?>" />
							</p>
						<?php
						}?>
				</form>
			</div>
		</div>
	<?php
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
			echo '<option value=' . $customers_array[$i][4];
			// On regarde si il est sélectionné
			if($selected_customer == $customers_array[$i][4])
			{echo ' selected';}	// Et on adapte en conséquence
			echo '>';
			echo $customers_array[$i][0] . " " . $customers_array[$i][1];
			echo '</option>';
		}
		// On ferme notre liste
		echo '</select>';
	}

	// Compte le nombre de lignes remplies dans le formulaire
	function entry_row_qty()
	{
		// Pour chaque ligne remplie
		for($row_qty = 1; (isset($_POST['row_'.$row_qty.'_product_ref']) && $_POST['row_'.$row_qty.'_product_ref'] != ''); $row_qty++) 
		{}
		$row_qty--;
		
		return $row_qty;
	}
	
	// Effectue les calculs d'une ligne de la facture référencée par son numéro
	// dont on retourne les totaux dans un tableau :
	// [0] product_qty		[1] row_weight	[2] product_price
	// [3] row_base_price	[4] row_price	[5] row_discount
	function entry_row_check($row_number)
	{
		// Quantité commandée
		$product_qty = $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_qty']);
		// Prix du produit HT
		$product_base_price = $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_base_price']);
		// Prix du produit HT avec remise
		$product_base_price_with_discount = $product_base_price - ($product_base_price * (0.01 * $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_discount'])));
		// Poids total de la ligne
		$row_weight = ($product_qty * $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_weight']));
		// Prix du produit TTC
		$product_price = ($product_base_price + ( (0.01 * $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_tax_percent'])) * $product_base_price));
		// Prix du produit TTC avec remise
		$product_price_with_discount = ($product_base_price_with_discount + ( (0.01 * $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_tax_percent'])) * $product_base_price_with_discount));
		// Prix de la ligne HT
		$row_base_price = ($product_qty * $product_base_price);
		// Prix de la ligne HT avec remise
		$row_base_price_with_discount = ($product_qty * $product_base_price_with_discount);
		// Montant de la remise (% -> €)
		$row_total_discount = ((0.01 * $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_discount'])) * $row_base_price);
		// Prix de la ligne TTC
		$row_price = ($product_qty * $product_price);
		// Prix de la ligne TTC avec remise
		$row_price_with_discount = ($product_qty * $product_price_with_discount);
		
		
		$row_totals = array($product_qty, $row_weight, $product_price_with_discount, $row_base_price_with_discount, $row_price_with_discount, $row_total_discount);
		
		return $row_totals;
	}
	
	// Affiche les lignes du tableau remplies à l'étape précédente (eoinvoice_entry)
	// $display_type = TRUE  --> champs modifiables
	// $display_type = FALSE  --> champs non modifiables et affichage des totaux
	function display_rows($display_type)
	{
		for($row_number = 1; $row_number <= $this->entry_row_qty(); $row_number++)
		{		
			// On récupère les données en rapport avec notre ligne
			$row_array = $this->row_data($row_number);
			
			$product_qty = $row_array[8];
			$row_weight = $row_array[7];
			$row_base_price = $row_array[10];
			$row_price = $row_array[11];
			
			$product_base_price = $row_array[2];
			$product_price = $row_array[3];
			$product_ref = $row_array[4];
			$product_name = $row_array[0];
			$product_desc = $row_array[1];
			$product_weight = $row_array[5];
			$product_tax_percent = $row_array[6];
			$product_discount = $row_array[9];
			
			// On affiche notre ligne
			?>
			<tr>
				<td><input type="text" value="<?php echo $product_ref; ?>" size=7 name="row_<?php echo $row_number; ?>_product_ref" <?php if(!$display_type){echo 'readonly';}?>></td>
				<td><input type="text" value="<?php echo $product_name; ?>" size=7 name="row_<?php echo $row_number; ?>_product_name" <?php if(!$display_type){echo 'readonly';}?>></td>
				<td><input type="text" value="<?php echo $product_desc; ?>" size=12 name="row_<?php echo $row_number; ?>_product_desc" <?php if(!$display_type){echo 'readonly';}?>></td>
				<td class="product_weight"><input type="text" value="<?php echo $product_weight; ?>" size=2 name="row_<?php echo $row_number; ?>_product_weight" <?php if(!$display_type){echo 'readonly';}?>></td>
				<td class="product_qty"><input type="text" value="<?php echo $product_qty; ?>" size=2 name="row_<?php echo $row_number; ?>_product_qty" <?php if(!$display_type){echo 'readonly';}?>></td>
				<?php if(!$display_type){ ?>
				<td><div id="row_<?php echo $row_number; ?>_weight"><?php echo $row_weight . ' Kg'; ?></div></td>
				<?php } ?>
				<td><input type="text" value="<?php echo $product_tax_percent; ?>" size=2 name="row_<?php echo $row_number; ?>_product_tax_percent" <?php if(!$display_type){echo 'readonly';}?>></td>
				<td><input type="text" value="<?php echo $product_discount; ?>" size=2 name="row_<?php echo $row_number; ?>_product_discount" <?php if(!$display_type){echo 'readonly';}?>></td>
				<td><input type="text" value="<?php echo $product_base_price; ?>" size=4 name="row_<?php echo $row_number; ?>_product_base_price" <?php if(!$display_type){echo 'readonly';}?>></td>
				<?php if(!$display_type){ ?>
				<td><div id="row_<?php echo $row_number; ?>_product_price"><?php echo $product_price . ' €'; ?></div></td>
				<td><div id="row_<?php echo $row_number; ?>_base_price"><?php echo $row_base_price . ' €'; ?></div></td>
				<td><div id="row_<?php echo $row_number; ?>_price"><?php echo $row_price . ' €'; ?></div></td>
				<?php } ?>
			</tr>
			<?php
		}
	}
		
	// Effectue le calcul des grand totaux
	// qu'on retourne dans un tableau
	// [0] base_total_price		[1] total_price	[2] total_weight
	// [3] total_qty			[4] total_discount
	function total_row_check()
	{
		// On initialise les valeurs
		$total_base_price = 0;
		$total_price = 0;
		$total_weight = 0;
		$total_qty = 0;
		$total_discount = 0;
		
		for($row_number = 1; $row_number <= $this->entry_row_qty(); $row_number++)
		{
			// On récupère les valeurs en rapport avec notre ligne
			$row_array = $this->entry_row_check($row_number);
			
			$total_base_price = $total_base_price + $row_array[3];
			$total_price = $total_price + $row_array[4];
			$total_weight = $total_weight + $row_array[1];
			$total_qty = $total_qty + $row_array[0];
			$total_discount = $total_discount + $row_array[5];
		}
				
		// On renvoie un tableau de ces totaux
		$totals_array = array($total_base_price, $total_price, $total_weight, $total_qty, $total_discount);
		return $totals_array;
	}
	
	// Affiche la ligne des totaux de la facture
	function display_totals_row()
	{
		// Retourne un tableau contenant toutes les informations d'une facture
		// [0] $base_total_price 		[1] $total_price 				[2] $total_qty
		// [3] $total_weight 			[4] $customer_billing_adress	[5] $customer_delivery_adress
		// [6] $customer_id				[7] $total_discount
		$totals_array = $this->invoice_data();

		?>
		<tr>
			<th>Total</th>
			<th></th>
			<th></th>
			<th></th>
			<th id="total_qty"><?php echo $totals_array[2] . ' Pcs'; ?></th>
			<th id="total_weight"><?php echo $totals_array[3] . ' Kg'; ?></th>
			<th></th>
			<th id="total_discount"><?php echo $totals_array[7] . ' €'; ?></th>
			<th></th>
			<th></th>
			<th id="base_total_price"><?php echo $totals_array[0] . ' €'; ?></th>
			<th id="total_price"><?php echo $totals_array[1] . ' €'; ?></th>
		</tr>
		<?php
	}
	
	// Retourne un tableau contenant toutes les informations d'une ligne de facture $row_number
	// [0] $product_name 		[1] $product_desc 		[2] $product_base_price
	// [3] $product_price 		[4] $product_ref		[5] $product_weight
	// [6] $product_tax_percent [7] $row_weight			[8] $product_qty
	// [9] $product_discount 	[10] $row_base_price	[11] $row_price
	function row_data($row_number)
	{
			// On forme l'identifiant de session
			$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
			// On récupère le magasin en cours
			$store_selected = $_SESSION[$session_store_word];
		
			// On récupère les valeurs en rapport avec notre ligne
			$row_array = $this->entry_row_check($row_number);
			
			$product_qty = $row_array[0];
			$row_weight = $row_array[1];
			$row_base_price = $row_array[3];
			$row_price = $row_array[4];
			$row_discount = $row_array[5];
			
			$product_base_price = preg_replace('#([,])#', '.', $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_base_price']));
			$product_price = round($row_array[2], get_option('eoinvoice__s' . $store_selected . '_round_number_count'));
			$product_ref = $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_ref']);
			$product_name = $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_name']);
			$product_desc = $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_desc']);
			$product_weight = preg_replace('#([,])#', '.', $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_weight']));
			$product_tax_percent = preg_replace('#([,])#', '.', $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_tax_percent']));
			$product_discount = preg_replace('#([,])#', '.', $this->tools_object->varSanitizer($_POST['row_'.$row_number.'_product_discount']));
			
			$row_data_array = array($product_name, $product_desc, $product_base_price, $product_price, $product_ref, $product_weight, $product_tax_percent, $row_weight, $product_qty, $product_discount, $row_base_price, $row_price);
			
			// On retourne le tableau complet
			return $row_data_array;
	}
	
	// Retourne un tableau contenant toutes les informations d'une facture
	// [0] $base_total_price 		[1] $total_price 				[2] $total_qty
	// [3] $total_weight 			[4] $customer_billing_adress	[5] $customer_delivery_adress
	// [6] $customer_id				[7] $total_discount
	function invoice_data()
	{
		// On récupère les totaux
		// [0] base_total_price	[1] total_price	
		// [2] total_weight		[3] total_qty
		$totals_array = $this->total_row_check();
		
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		// On récupère le magasin en cours
		$store_selected = $_SESSION[$session_store_word];
		
		// On récupère le client en cours
		$selected_customer = $_POST['eoinvoice_selected_customer'];
		// On récupère les adresses du client dans un tableau
		$customer_info_tab = $this->tools_object->eoinvoice_get_customer_info($selected_customer);
		
		$customer_delivery_adress = $customer_info_tab[2];
		$customer_billing_adress = $customer_info_tab[3];
		$base_total_price = round($totals_array[0], get_option('eoinvoice__s' . $store_selected . '_round_number_count'));
		$total_price = round($totals_array[1], get_option('eoinvoice__s' . $store_selected . '_round_number_count'));
		$total_qty = $totals_array[3];
		$total_weight = $totals_array[2];
		$total_discount = $totals_array[4];
		
		$invoice_array = array($base_total_price, $total_price, $total_qty, $total_weight, $customer_billing_adress, $customer_delivery_adress, $selected_customer, $total_discount);
		
		// On retourne le tableau complet
		return $invoice_array;
	}
	
	// Crée une nouvelle facture en base de données
	function create_invoice($store_selected)
	{
		// Entrée dans la table invoice
		$control = $this->invoice_object->eoinvoice_new_invoice($store_selected, $this->invoice_data());
		if($control != 1){die($control);}
		// On récupère l'id de la facture qu'on vient de créer
		$invoice_id = $this->invoice_object->get_last_invoice_id($store_selected);
		
		// Entrées dans la table invoice_row pour les différentes lignes
		for($row_number = 1; $row_number <= $this->entry_row_qty(); $row_number++)
		{	
			$this->create_invoice_row($store_selected, $invoice_id, $row_number);
		}
	}
	
	// Crée une nouvelle ligne de facture en base de données
	function create_invoice_row($store_selected, $invoice_id, $row_number)
	{
		// Récupère les données de la ligne
		$row_array = $this->row_data($row_number);

		// Crée la ligne en base de données pour un $invoice_id donné
		$control = $this->invoice_row_object->new_invoice_row($store_selected, $invoice_id, $row_array);
		if($control != 1){die($control);}
	}
}
?>
