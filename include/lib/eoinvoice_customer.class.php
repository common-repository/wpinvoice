<?php
// DEFINITION Classe customer / client

class eoinvoice_customer
{
	var $adress_table_object;
	var $user_adress_table_object;
	var $tools_object;
	
	function eoinvoice_customer()
	{	
		// On charge les classes nécessaires pour la gestion et la lecture des tables en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_user_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		
		// On instancie un objet de chacune de ces classes
		$this->adress_table_object = new eoinvoice_adress_table();
		$this->user_adress_table_object = new eoinvoice_user_adress_table();
		$this->tools_object = new eoinvoice_tools();
	}
	
	// Function d'affichage du front client
	function eoinvoice_customer_page()
	{	
		// On vérifie les permissions
		if (!current_user_can('read'))
		{
			wp_die( __('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'eoinvoice_trdom') );
		}
		
		// On regarde si le formulaire à été envoyé
		if($_POST['eoinvoice_hidden'] == 'Y')
		{
			// Test des champs à l'aide d'expressions régulières
			$regex_array = $this->customer_regex_check();
			
			if (!$regex_array[0])
			{
				// Si oui	
				// Mise à jour des info/coordonnées du client
				$this->eoinvoice_customer_info_update();
				// Entête de confirmation de mise à jour en haut de page
				?><div class="updated"><p><strong><?php echo __('R&eacute;glages sauvegard&eacute;es.', 'eoinvoice_trdom'); ?></strong></p></div><?php 
			}
			else
			{
				$this->customer_regex_display($regex_array);
			}

		}
		
		// INFOS Client
		// ID de l'utilisateur
		$customer_id = $this->tools_object->eoinvoice_get_current_user_id();
		// On récupère les infos du client dans un tableau
		$customer_info_tab = $this->tools_object->eoinvoice_get_customer_info($customer_id);
		// Adresse livraison
		$customer_delivery_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[2]);
		// Adresse facturation
		$customer_billing_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[3]);
		
		
		
				
		// Affichage classique de la page
		?>
		<div class="wrap">
			<?php echo "<h2>" . __( 'R&eacute;glages client', 'eoinvoice_trdom' ) . "</h2>";?>
			<form name="eoinvoice_client_form" method="post" action="">
				<input type="hidden" name="eoinvoice_hidden" value="Y" />
				<div>
					<div style="float:left;">
						<table class="widefat">
							<thead><tr><th><?php echo __( 'Coordonn&eacute;es de facturation', 'eoinvoice_trdom' ); ?></th><th></th></tr></thead>
							<tr><th><?php echo __( 'Nom', 'eoinvoice_trdom' ); ?></th><th><?php echo $customer_info_tab[1] ?></th></tr>
							<tr><th><?php echo __( 'Prenom', 'eoinvoice_trdom' ); ?></th><th><?php echo $customer_info_tab[0] ?></th></tr>
							
							<tr><th><?php echo __( 'Adresse', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[1][0]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_billing_adress" value="<?php if((!$regex_array[1][0] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_billing_adress[0];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_adress']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __( 'Quartier', 'eoinvoice_trdom' ); ?></th><th><input 
							type="text" name="eoinvoice_customer_billing_suburb" value="<?php echo $customer_billing_adress[1]; ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __( 'Ville', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[1][1]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_billing_city" value="<?php if((!$regex_array[1][1] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_billing_adress[2];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_city']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Code postal', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[1][2]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_billing_postcode" value="<?php if((!$regex_array[1][2] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_billing_adress[3];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_postcode']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Etat / r&eacute;gion', 'eoinvoice_trdom' ); ?></th><th><input 
							type="text" name="eoinvoice_customer_billing_state" value="<?php echo $customer_billing_adress[4]; ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Pays', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[1][3]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_billing_country" value="<?php if((!$regex_array[1][3] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_billing_adress[5];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_country']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('T&eacute;l&eacute;phone', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[1][4]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_billing_phone" value="<?php if((!$regex_array[1][4] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_billing_adress[6];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_phone']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Fax', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[1][5]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_billing_telecopy" value="<?php if((!$regex_array[1][5] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_billing_adress[7];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_telecopy']);} ?>" size="20" /></th></tr>
							
						</table>
					</div>
					<div style="float:left;">
						<table class="widefat" style="margin-left:10px;" >
							<thead><tr><th><?php echo __( 'Coordonn&eacute;es de livraison', 'eoinvoice_trdom' ); ?></th><th></th></tr></thead>
							<tr><th>Adresses identiques</th><th><input type="checkbox" name="same_as_billing" value="Adresse de facturation identique" /></th></tr>
							<tr><th><?php echo __( 'Nom', 'eoinvoice_trdom' ); ?></th><th><?php echo $customer_info_tab[1] ?></th></tr>
							<tr><th><?php echo __( 'Prenom', 'eoinvoice_trdom' ); ?></th><th><?php echo $customer_info_tab[0] ?></th></tr>
							
							<tr><th><?php echo __( 'Adresse', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[2][0]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_delivery_adress" value="<?php if((!$regex_array[2][0] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_delivery_adress[0];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_adress']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __( 'Quartier', 'eoinvoice_trdom' ); ?></th><th><input 
							type="text" name="eoinvoice_customer_delivery_suburb" value="<?php echo $customer_delivery_adress[1]; ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __( 'Ville', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[2][1]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_delivery_city" value="<?php if((!$regex_array[2][1] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_delivery_adress[2];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_city']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Code postal', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[2][2]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_delivery_postcode" value="<?php if((!$regex_array[2][2] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_delivery_adress[3];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_postcode']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Etat / r&eacute;gion', 'eoinvoice_trdom' ); ?></th><th><input 
							type="text" name="eoinvoice_customer_delivery_state" value="<?php echo $customer_delivery_adress[4]; ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('Pays', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[2][3]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_delivery_country" value="<?php if((!$regex_array[2][3] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_delivery_adress[5];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_country']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('T&eacute;l&eacute;phone', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($regex_array[2][4]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_customer_delivery_phone" value="<?php if((!$regex_array[2][4] && !$regex_array[0]) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $customer_delivery_adress[6];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_phone']);} ?>" size="20" /></th></tr>
							
						</table>
					</div>
				</div>
				<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php echo __('Mettre &agrave; jour', 'eoinvoice_trdom' );?>" style="float:bottom left;" /></p>
			</form>
		</div>
		<?php
	}
	
	// Permet la mise à jour des informations d'adresse et de store
	function eoinvoice_customer_info_update()
	{
		// On récupère les infos actuelles
		$customer_info_tab = $this->tools_object->eoinvoice_get_customer_info($this->tools_object->eoinvoice_get_current_user_id());
		// Et les 2 adresses associées
		$customer_billing_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[3]);
		$customer_delivery_adress = $this->tools_object->eoinvoice_get_adress($customer_info_tab[2]);
		
		// Controle
		// Si un élément de l'adresse de facturation a été changé
		if ( ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_adress']) != $customer_billing_adress[0]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_city']) != $customer_billing_adress[2]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_postcode']) != $customer_billing_adress[3]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_country']) != $customer_billing_adress[5]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_phone']) != $customer_billing_adress[6]) )
		{
			// On note que ca a changé
			$billing_adress_change = true;
			// Creation d'une nouvelle adresse en bd
			$control = $this->adress_table_object->new_adress($this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_adress']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_suburb']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_city']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_postcode']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_state']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_country']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_phone']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_telecopy']));
			if($control != TRUE){die($control);}
			// On retiens l'id de cette adresse
			$new_billing_adress_id = $this->adress_table_object->get_last_adress_id();
		}
		// Controle
		// si un élément de l'adresse de livraison a été changé et si la case n'est pas cochée
		if (!($_POST['same_as_billing']))
		{
			if ( ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_adress']) != $customer_delivery_adress[0]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_city']) != $customer_delivery_adress[2]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_postcode'] != $customer_delivery_adress[3])) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_country'] != $customer_delivery_adress[5])) || ($this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_phone']) != $customer_delivery_adress[6]) )
			{
				// Creation d'une nouvelle adresse en bd
				$control = $this->adress_table_object->new_adress($this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_adress']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_suburb']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_city']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_postcode']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_state']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_country']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_phone']), $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_telecopy']));
				if($control != TRUE){die($control);}
				// On note que ca a changé
				$delivery_adress_change = true;
				// On retiens l'id de cette adresse
				$new_delivery_adress_id = $this->adress_table_object->get_last_adress_id();
			}
		}
		else
		{
			// On note que ca a changé
			$delivery_adress_change = true;
			// On retiens l'id de cette adresse
			$new_delivery_adress_id = $this->adress_table_object->get_last_adress_id();
		}		
		
		// Enfin si l'adresse a changé
		// On fait le lien avec l'utilisateur
		if ($billing_adress_change == true)
		{
			$control = $this->user_adress_table_object->eoinvoice_new_user_adress_link(($this->tools_object->eoinvoice_get_current_user_id()), $new_billing_adress_id, 2);
			if($control != 1){die($control);}
		}
		if ($delivery_adress_change == true)
		{
			$control = $this->user_adress_table_object->eoinvoice_new_user_adress_link(($this->tools_object->eoinvoice_get_current_user_id()), $new_delivery_adress_id, 1);
			if($control != 1){die($control);}
		}
	}
	
	// Methode permettant de vérifier les champs du formulaire avant modifications en BD
	// Retourne un tableau à deux dimensions
	// [0] contrôle global d'erreur
	// [1][0] $bad_billing_adress 	[1][1] $bad_billing_city		[1][2] $bad_billing_postcode
	// [1][3] $bad_billing_country 	[1][4] $bad_billing_phone
	// [2][0] $bad_delivery_adress 	[2][1] $bad_delivery_city		[2][2] $bad_delivery_postcode
	// [2][3] $bad_delivery_country [2][4] $bad_delivery_phone		[2][5] $bad_delivery_telecopy
	function customer_regex_check()
	{
		// Test des expressions régulières
		// Livraison
		// Si la case "adresses identiques" est coché on ne teste pas
		// Sinon on test
		if (!($_POST['same_as_billing']))
		{
			if (!preg_match("#[0-9a-zA-Z]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_adress'])))
			{$bad_d_adress = TRUE; $error = TRUE;}else{$bad_d_adress = FALSE;}
			
			if (!preg_match("#[a-zA-Z]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_city'])))
			{$bad_d_city = TRUE; $error = TRUE;}else{$bad_d_city = FALSE;}
			
			if (!preg_match("#[0-9]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_postcode'])))
			{$bad_d_postcode = TRUE; $error = TRUE;}else{$bad_d_postcode = FALSE;}
			
			if (!preg_match("#[a-zA-Z]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_country'])))
			{$bad_d_country = TRUE; $error = TRUE;}else{$bad_d_country = FALSE;}
			
			if (!preg_match("#[0-9]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_delivery_phone'])))
			{$bad_d_phone = TRUE; $error = TRUE;}else{$bad_d_phone = FALSE;}
			
			$d_array = array($bad_d_adress, $bad_d_city, $bad_d_postcode, $bad_d_country, $bad_d_phone);
		}
		// Facturation
		if (!preg_match("#[0-9a-zA-Z]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_adress'])))
		{$bad_b_adress = TRUE; $error = TRUE;}else{$bad_b_adress = FALSE;}
		
		if (!preg_match("#[a-zA-Z]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_city'])))
		{$bad_b_city = TRUE; $error = TRUE;}else{$bad_b_city = FALSE;}
		
		if (!preg_match("#[0-9]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_postcode'])))
		{$bad_b_postcode = TRUE; $error = TRUE;}else{$bad_b_postcode = FALSE;}
		
		if (!preg_match("#[a-zA-Z]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_country'])))
		{$bad_b_country = TRUE; $error = TRUE;}else{$bad_b_country = FALSE;}
		
		if (!preg_match("#[0-9]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_phone'])))
		{$bad_b_phone = TRUE; $error = TRUE;}else{$bad_b_phone = FALSE;}
		
		if (!preg_match("#[0-9]#", $this->tools_object->varSanitizer($_POST['eoinvoice_customer_billing_telecopy'])))
		{$bad_b_telecopy = TRUE; $error = TRUE;}else{$bad_b_phone = FALSE;}
		
		$b_array = array($bad_b_adress, $bad_b_city, $bad_b_postcode, $bad_b_country, $bad_b_phone, $bad_b_telecopy);
		
		// Tableau de résultats du contrôle de regex de la ligne
		$regex_array = array($error, $b_array, $d_array);
		
		// On retourne le tableau
		return $regex_array;
	}
	
	// Méthode permettant l'affichage et la mise en forme des erreurs de saisies
	// à partir du tableau retourné par la fonction customer_regex_check()
	function customer_regex_display($customer_regex_array)
	{		
		// Entête / Panneau erreur
		echo '<div class="error"><p><strong style="color:red;">' . __('Erreur(s) (Champs vide ou caract&egrave;re(s) non autorisé(s))', 'eoinvoice_trdom') . '</strong></p><p>';
		echo '<strong>Facturation</strong><br />';
			if ($customer_regex_array[1][0])
			{echo __("Adresse non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[1][1])
			{echo __("Ville non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[1][2])
			{echo __("Code postal non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[1][3])
			{echo __("Pays non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[1][4])
			{echo __("Num&eacute;ro de t&eacute;l&eacute;phone non conforme", 'eoinvoice_trdom') . '<br /></p><p>';}
			if ($customer_regex_array[1][5])
			{echo __("Num&eacute;ro de fax non conforme", 'eoinvoice_trdom') . '<br /></p><p>';}
			
			if(!$customer_regex_array[1][0] && !$customer_regex_array[1][1] && !$customer_regex_array[1][2] && !$customer_regex_array[1][3] && !$customer_regex_array[1][4] && !$customer_regex_array[1][5])
			{echo 'OK';}
			
		echo '<br/><strong>Livraison</strong><br />';
			if ($customer_regex_array[2][0])
			{echo __("Adresse non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[2][1])
			{echo __("Ville non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[2][2])
			{echo __("Code postal non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[2][3])
			{echo __("Pays non conforme", 'eoinvoice_trdom') . '<br />';}
			if ($customer_regex_array[2][4])
			{echo __("Num&eacute;ro de t&eacute;l&eacute;phone non conforme", 'eoinvoice_trdom') . '<br /></p><p>';}
			
			if(!$customer_regex_array[2][0] && !$customer_regex_array[2][1] && !$customer_regex_array[2][2] && !$customer_regex_array[2][3] && !$customer_regex_array[2][4] && !$customer_regex_array[2][5])
			{echo 'OK';}
			
		echo '</p></div>';
	}

}


?>
