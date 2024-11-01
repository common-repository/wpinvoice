<?php
DEFINE('EURO', chr(128)); // Sigle euro
// DEFINITION CLASSE Settings

class eoinvoice_settings
{
	var $adress_table_object;
	var $store_table_object;
	var $user_store_table_object;
	var $tools_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_settings()
	{
		// On charge les classes nécessaires pour la gestion et la lecture des tables en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_user_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		
		// On instancie un objet de chacune de ces classes
		$this->adress_table_object = new eoinvoice_adress_table();
		$this->store_table_object = new eoinvoice_store_table();
		$this->user_store_table_object = new eoinvoice_user_store_table();
		$this->tools_object = new eoinvoice_tools();
	}
	
	// METHODES
	
	// Page de réglages
	function eoinvoice_settings_page()
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
			
			// On regarde si les informations ont été mise à jour
			if($_POST['eoinvoice_hidden'] == 'Y')
			{
				// Contrôle des chiffres et données entrées
				$settings_regex_array = $this->settings_regex_check();
				
				// Si le contrôle des champs s'est bien passé
				if($settings_regex_array[0] == 1)
				{
					// Mise à jour des options stockées par Wordpress
					$this->eoinvoice_settings_update();
					
					// Mise à jour des coordonnées du magasin / Nouvelles coordonnées
					$this->eoinvoice_store_info_update();

					// Entête de confirmation de mise à jour
					?><div class="updated"><p><strong><?php echo __('Options sauvegard&eacute;es.', 'eoinvoice_trdom' ); ?></strong></p></div><?php
				}
				else // Si erreur
				{
					$this->settings_regex_display($settings_regex_array);
				}
			}
			
			// On regarde si les gestionnaires ont subit un changement
			if($_POST['eoinvoice_hidden'] == 'G')
			{
				// Mise à jour des gestionnaires
				$this->managers_update($store_selected);

				// Entête de confirmation de mise à jour
				?><div class="updated"><p><strong><?php echo __('Gestionnaires modifi&eacute;s.', 'eoinvoice_trdom' ); ?></strong></p></div><?php
			}
			
			
			// INFOS MAGASIN
			// On récupère les infos du magasin dans un tableau
			$store_info_tab = $this->tools_object->eoinvoice_get_store_info($store_selected);
			// Puis ses coordonnées dans un autre tableau
			$store_adress_tab = $this->tools_object->eoinvoice_get_adress($store_info_tab[0]);
			// RIB MAGASIN
			$store_rib_tab = $this->tools_object->eoinvoice_get_store_bic($store_selected, $this->tools_object->get_last_store_id($store_selected));
			
			// Affichage classique de la page
			?>
				<div class="wrap">
					<?php echo "<h2>" . __( 'R&eacute;glages magasin', 'eoinvoice_trdom' ) . "</h2>";
					
					// Panneau de changement de magasin
					$this->tools_object->display_logout_form($store_selected);
					
					// Panneau des gestionnaires (listing + ajouter)
					$this->display_managers_panel($store_selected);
					
					// Reste de la page
					?>
					<form name="eoinvoice_settings_form" method="post" action="">
						<input type="hidden" name="eoinvoice_hidden" value="Y" />
						<input type="hidden" name="eoinvoice_selected_store" value="<?php echo $store_selected; ?>" />
						<table class="widefat" style="width:500px;">
							<thead>
								<tr><th><?php echo __( 'Informations magasin', 'eoinvoice_trdom' ) ?></th><th></th></tr>
							</thead>
							<tr><th><?php echo __('Nom', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[1]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_name" value="<?php if((!$settings_regex_array[1] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_info_tab[1];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_name']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Adresse', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[2]){echo ' class="errori" ';} ?> 
							type="text" name="eoinvoice_store_adress" value="<?php if((!$settings_regex_array[2] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_adress_tab[0];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_adress']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Ville', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[3]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_city" value="<?php if((!$settings_regex_array[3] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_adress_tab[2];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_city']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Code postal', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[4]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_postcode" value="<?php if((!$settings_regex_array[4] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_adress_tab[3];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_postcode']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Pays', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[5]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_country" value="<?php if((!$settings_regex_array[5] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_adress_tab[5];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_country']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('T&eacute;l&eacute;phone', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[6]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_phone" value="<?php if((!$settings_regex_array[6] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_adress_tab[6];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_phone']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Fax', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[7]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_telecopy" value="<?php if((!$settings_regex_array[7] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_adress_tab[7];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_telecopy']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Email', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[8]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_email" value="<?php if((!$settings_regex_array[8] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_info_tab[2];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_email']);} ?>" size="40" /></th></tr>
							
							<tr><th><?php echo __('Num&eacute;ro de TVA intracommunautaire', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[9]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_store_tax_number" value="<?php if((!$settings_regex_array[9] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_info_tab[3];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_store_tax_number']);} ?>" size="40" /></th></tr>
							<tr><th><?php echo __( 'Ch&egrave;ques accept&eacute;s', 'eoinvoice_trdom' ); ?></th>
								<th>
									<select name="eoinvoice_check_accept">
										<?php
										// S'adapte au choix de l'admin
										if($store_info_tab[4] > 0)
										{$y_status = 'selected'; $n_status = '';}else{$y_status = ''; $n_status = 'selected';}
										?>
										<option value="'1'"<?php echo ' ' . $y_status . '>' . __('Oui', 'eoinvoice_trdom'); ?></option>
										<option value="'0'"<?php echo $n_status . '>' . __('Non', 'eoinvoice_trdom'); ?></option>
									</select>
								</th>
							</tr>
							<tr><th><?php echo __( 'Type de soci&eacute;t&eacute;', 'eoinvoice_trdom' ); ?></th>
								<th>
									<select name="eoinvoice_society_type">
										<?php
										// S'adapte au choix de l'admin
										if($store_info_tab[5] == 'SARL')
										{$sarl_status = 'selected'; $sas_status = ''; $sa_status = '';}
										else if($store_info_tab[5] == 'SAS')
										{$sarl_status = ''; $sas_status = 'selected'; $sa_status = '';}
										else
										{$sarl_status = ''; $sas_status = ''; $sa_status = 'selected';}
										?>
										<option value="SARL" <?php echo $sarl_status . ' >' . __('SARL', 'eoinvoice_trdom'); ?></option>
										<option value="SAS" <?php echo $sas_status . ' >' . __('SAS', 'eoinvoice_trdom'); ?></option>
										<option value="SA" <?php echo $sa_status . ' >' . __('SA', 'eoinvoice_trdom'); ?></option>
									</select>
								</th>
							</tr>
							<tr><th><?php echo __('Capital (EURO)', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[10]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_society_capital" value="<?php if((!$settings_regex_array[10] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_info_tab[6];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_society_capital']);} ?>" size="40" /></th></tr>
						</table>
						<table class="widefat" style="width:500px;">
							<thead>
								<tr><th><?php echo __( 'R&eacute;f&eacute;rences des factures', 'eoinvoice_trdom' ) ?></th><th></th><th>Exemple</th></tr>
							</thead>
							<tr><th><?php echo __( 'Format', 'eoinvoice_trdom' ); ?></th>
								<th>
									<select name="eoinvoice_ref_type">
										<?php
										// S'adapte au choix de l'admin
										if(get_option('eoinvoice__s' . $store_selected . '_ref_type')=='simple')
										{$simple_status = 'selected'; $advanced_status = '';}else{$simple_status = ''; $advanced_status = 'selected';}
										?>
										<option value="simple"<?php echo $simple_status . '>' . __('Simple (001, 002, 003 ...)', 'eoinvoice_trdom'); ?></option>
										<option value="advanced"<?php echo $advanced_status . '>' . __('Avanc&eacute; (FA001, FA002 ...)', 'eoinvoice_trdom'); ?></option>
									</select>
								</th><th></th>
							</tr>
							<tr><th><?php echo __('Nombre de chiffres', 'eoinvoice_trdom' ); ?></th>
							<th><input type="text" name="eoinvoice_ref_number_count" value="<?php echo get_option('eoinvoice__s' . $store_selected . '_ref_number_count'); ?>" size="1" maxlength="1" /></th><th><?php echo __("5" ); ?></th></tr>
							
							<tr><th><?php echo __('Pr&eacute;fixe', 'eoinvoice_trdom' ); ?></th><th><input type="text" name="eoinvoice_ref_prefix" value="<?php echo get_option('eoinvoice__s' . $store_selected . '_ref_prefix'); ?>" size="5" maxlength="5" /></th><th><?php echo __("FA", 'eoinvoice_trdom' ); ?></th></tr>
						</table>
						<table class="widefat" style="width:500px;">
							<thead>
								<tr><th><?php echo __( 'Arrondis', 'eoinvoice_trdom' ) ?></th><th></th><th>Exemple</th></tr>
							</thead>
							<tr><th><?php echo __('Nombre de chiffres apr&egrave;s la virgule', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[11]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_round_number_count" value="<?php if((!$settings_regex_array[11] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo get_option('eoinvoice__s' . $store_selected . '_round_number_count');}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_round_number_count']);} ?>" size="10" maxlength="1" /></th><th><?php echo __("2" ); ?></th></tr>
						</table>
						<table class="widefat" style="width:500px;">
							<thead>
								<tr><th><?php echo __( 'Identit&eacute; bancaire', 'eoinvoice_trdom' ) ?></th><th></th></tr>
							</thead>
							
							<tr><th><?php echo __('Code banque', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[12]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_rib_bank_code" value="<?php if((!$settings_regex_array[12] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_rib_tab[0];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_rib_bank_code']);} ?>" size="20" maxlength="5" /></th></tr>
							
							<tr><th><?php echo __('Code guichet', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[13]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_rib_register_code" value="<?php if((!$settings_regex_array[13] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_rib_tab[1];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_rib_register_code']);} ?>" size="20" maxlength="5" /></th></tr>
							
							<tr><th><?php echo __('Num&eacute;ro de compte', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[14]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_rib_account_number" value="<?php if((!$settings_regex_array[14] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_rib_tab[2];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_rib_account_number']);} ?>" size="20" maxlength="11" /></th></tr>
							
							<tr><th><?php echo __('Cl&eacute; RIB', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[15]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_rib_key" value="<?php if((!$settings_regex_array[15] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_rib_tab[3];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_rib_key']);} ?>" size="20" maxlength="2" /></th></tr>
							
							<tr><th><?php echo __('IBAN', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[16]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_rib_iban" value="<?php if((!$settings_regex_array[16] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_rib_tab[4];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_rib_iban']);} ?>" size="20" /></th></tr>
							
							<tr><th><?php echo __('BIC', 'eoinvoice_trdom' ); ?></th><th><input
							<?php if($settings_regex_array[17]){echo ' class="errori" ';} ?>
							type="text" name="eoinvoice_rib_bic" value="<?php if((!$settings_regex_array[17] && $settings_regex_array[0] == 1) || !($_POST['eoinvoice_hidden'] == 'Y')){echo $store_rib_tab[5];}else{echo $this->tools_object->varSanitizer($_POST['eoinvoice_rib_bic']);} ?>" size="20" /></th></tr>
						</table>
						<p class="submit"><input type="submit" class="button-primary" name="Submit" value="<?php echo __('Mettre &agrave; jour', 'eoinvoice_trdom' ); ?>" /></p>
					</form>
				</div>
			<?php
		}
		else
		{
			// Sinon (si on n'est pas passé par l'écran de sélection du magasin)
			// on affiche la liste de choix d'un magasin
			$this->tools_object->eoinvoice_store_list_page(__( 'R&eacute;glages magasin', 'eoinvoice_trdom' ),'setup_store');
		}
	}
	
	// Permet la mise à jour des options stockées par wordpress
	function eoinvoice_settings_update()
	{
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		// On récupère le magasin de travail
		$store_selected = $_SESSION[$session_store_word];
		
		// Si les options ne sont pas encore "créées", on le fait
		if(!get_option('eoinvoice__s' . $store_selected . '_ref_type'))
		{
			add_option('eoinvoice__s' . $store_selected . '_ref_type', 'advanced');
			add_option('eoinvoice__s' . $store_selected . '_ref_number_count', 5);
			add_option('eoinvoice__s' . $store_selected . '_ref_prefix', 'FA');
			add_option('eoinvoice__s' . $store_selected . '_round_number_count', 5);
		}
		// Enfin on les remplis	
		// Type de référence de facture (simple,avancé)
		$ref_type = $this->tools_object->varSanitizer($_POST['eoinvoice_ref_type']);
		update_option('eoinvoice__s' . $store_selected . '_ref_type', $ref_type);
		// Nombre de chiffres dans la référence
		$ref_number_count = $this->tools_object->varSanitizer($_POST['eoinvoice_ref_number_count']);
		update_option('eoinvoice__s' . $store_selected . '_ref_number_count', $ref_number_count);
		// Préfixe de référence facture
		$ref_prefix = $this->tools_object->varSanitizer($_POST['eoinvoice_ref_prefix']);
		update_option('eoinvoice__s' . $store_selected . '_ref_prefix', $ref_prefix);
		// Nombre de ciffres après la virgule
		$round_number_count = $this->tools_object->varSanitizer($_POST['eoinvoice_round_number_count']);
		update_option('eoinvoice__s' . $store_selected . '_round_number_count', $round_number_count);
	}
	
	// Permet la mise à jour des informations d'adresse et de store
	function eoinvoice_store_info_update()
	{
		// On stocke le magasin selectionné
		$store_selected = $_POST['eoinvoice_selected_store'];
		
		// On récupère les infos actuelles
		// [0] $store_adress_id 		[1] $store_name 		[2] $store_email 	[3] $store_tax_number
		// [4] $store_accept_check		[5] $society_type		[6] $society_capital
		$compare_array = $this->tools_object->eoinvoice_get_store_info($store_selected);
		$compare_array_adress = $this->tools_object->eoinvoice_get_adress($compare_array[0]);
		$compare_array_rib = $this->tools_object->eoinvoice_get_store_bic($store_selected);
		
		// Si un élément de l'adresse à été changé
		if ( ($this->tools_object->varSanitizer($_POST['eoinvoice_store_adress']) != $compare_array_adress[0]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_city']) != $compare_array_adress[2]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_postcode']) != $compare_array_adress[3]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_country']) != $compare_array_adress[5]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_phone']) != $compare_array_adress[6]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_telecopy']) != $compare_array_adress[7])) 
		{
			$adress_change = true;
		}
	
		// Si une information du store à été changée
		if ( ($this->tools_object->varSanitizer($_POST['eoinvoice_store_name']) != $compare_array[1]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_email']) != $compare_array[2]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_store_tax_number']) != $compare_array[3]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_check_accept']) != $compare_array[4]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_society_type']) != $compare_array[5]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_society_capital']) != $compare_array[6]))
		{
			$store_info_change = true;
		}
		// Si les infos banques ont changé
		if ( ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_bank_code']) != $compare_array_rib[0]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_register_code']) != $compare_array_rib[1]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_account_number']) != $compare_array_rib[2]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_key']) != $compare_array_rib[3]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_iban']) != $compare_array_rib[4]) || ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_bic']) != $compare_array_rib[5]))
		{
			$store_info_change = true;
		}
		
		// Si l'adresse change
		if ($adress_change == true)
		{
			// Creation d'une ligne adresse
			$control = $this->adress_table_object->new_adress($this->tools_object->varSanitizer($_POST['eoinvoice_store_adress']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_suburb']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_city']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_postcode']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_state']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_country']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_phone']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_telecopy']));
			if($control != TRUE){die($control);}
			// Creation d'une ligne store référencant la nouvelle adresse
			$new_store_adress_id = $this->adress_table_object->get_last_adress_id();
			$control = $this->store_table_object->update_store($store_selected, $this->tools_object->varSanitizer($_POST['eoinvoice_store_name']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_email']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_tax_number']), $new_store_adress_id, $this->tools_object->varSanitizer($_POST['eoinvoice_check_accept']), $this->tools_object->varSanitizer($_POST['eoinvoice_society_type']), $this->tools_object->varSanitizer($_POST['eoinvoice_society_capital']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_bank_code']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_register_code']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_account_number']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_key']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_iban']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_bic']));
			if($control != TRUE){die($control);}
		}
		
		// Si seuls les infos store changent
		if ( ($store_info_change == true) && ($adress_change != true) )
		{
			// Creation d'une nouvelle ligne store référencant l'ancienne adresse et les nouvelles infos
			$new_store_adress_id = ($this->store_table_object->get_store_element($store_selected, 'store_adress_id'));
			$control = $this->store_table_object->update_store($store_selected, $this->tools_object->varSanitizer($_POST['eoinvoice_store_name']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_email']), $this->tools_object->varSanitizer($_POST['eoinvoice_store_tax_number']), $new_store_adress_id, $this->tools_object->varSanitizer($_POST['eoinvoice_check_accept']), $this->tools_object->varSanitizer($_POST['eoinvoice_society_type']), $this->tools_object->varSanitizer($_POST['eoinvoice_society_capital']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_bank_code']), esc_attr($_POST['eoinvoice_rib_register_code']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_account_number']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_key']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_iban']), $this->tools_object->varSanitizer($_POST['eoinvoice_rib_bic']));
			if($control != TRUE){die($control);}
		}
	}
	
	// Methode permettant de vérifier les champs avant insertion en BD
	// Retourne un tableau 
	
	// [0] contrôle global d'erreur 
	
	// INFOS
	
	// [1] $bad_name
	// [2] $bad_adress [3] $bad_city [4] $bad_postcode
	// [5] $bad_country [6] $bad_phone [7] $bad_telecopy
	// [8] $bad_email [9] $bad_tax_number [10] $bad_capital
	// [11] $bad_round_number_count
	
	// RIB

	// [12] $bad_rib_bank_code [13] $bad_rib_register_code 
	// [14] $bad_account_number [15] $bad_rib_key
	// [16] $bad_rib_iban [17] $bad_rib_bic
	
	function settings_regex_check()
	{
		// Tableau de résultats du contrôle de regex de la facture
		$settings_regex_array = array();
		
		$error = 1;
		
		// Test des expressions régulières
		
		// Nom du magasin
		if (!preg_match("#[0-9a-zA-Z\s]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_name']))))
		{$bad_name = TRUE; $error = 0;}else{$bad_name = FALSE;}
		
		// Adresse
		if (!preg_match("#[0-9a-zA-Z\s]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_adress']))))
		{$bad_adress = TRUE; $error = 0;}else{$bad_adress = FALSE;}
		
		// Ville
		if (!preg_match("#[a-zA-Z]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_city']))))
		{$bad_city = TRUE; $error = 0;}else{$bad_city = FALSE;}
		
		// Code postal
		if (!preg_match("#^([0-9]+[- ]?){4,}$#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_postcode']))))
		{$bad_postcode = TRUE; $error = 0;}else{$bad_postcode = FALSE;}
		
		// Etat (USA)
		if (!preg_match("#[a-zA-Z]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_country']))))
		{$bad_country = TRUE; $error = 0;}else{$bad_country = FALSE;}
		
		// Téléphone
		if (!preg_match("#^[+]?[ 0-9.-]+$#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_phone']))))
		{$bad_phone = TRUE; $error = 0;}else{$bad_phone = FALSE;}
		
		// Fax
		if (!preg_match("#^[+]?[ 0-9.-]+$#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_telecopy']))))
		{$bad_telecopy = TRUE; $error = 0;}else{$bad_telecopy = FALSE;}
		
		// E-Mail
		if (!is_email($this->tools_object->varSanitizer($_POST['eoinvoice_store_email'])))
		{$bad_email = TRUE; $error = 0;}else{$bad_email = FALSE;}
		
		// Numéro de TVA intracommunautaire
		if (!preg_match("#^[a-zA-Z]{2}[- ]?[0-9]{2}[- ]?([0-9]{3}[- ]?){3}$#", ($this->tools_object->varSanitizer($_POST['eoinvoice_store_tax_number']))))
		{$bad_tax_number = TRUE; $error = 0;}else{$bad_tax_number = FALSE;}
			
		// Capital
		if (!preg_match("#[0-9A-Z]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_society_capital']))))
		{$bad_capital = TRUE; $error = 0;}else{$bad_capital = FALSE;}
		
		// Arrondis
		if (!preg_match("#[0-9]{1}#", ($this->tools_object->varSanitizer($_POST['eoinvoice_round_number_count']))))
		{$bad_float_count = TRUE; $error = 0;}else{$bad_float_count = FALSE;}
		
		// RIB ---------------------
		
		// Code banque
		if (!preg_match("#[0-9]{5}#", ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_bank_code']))))
		{$bad_rib_bank_code = TRUE; $error = 0;}else{$bad_rib_bank_code = FALSE;}
		
		// Code guichet
		if (!preg_match("#[0-9]{5}#", ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_register_code']))))
		{$bad_rib_register_code = TRUE; $error = 0;}else{$bad_rib_register_code = FALSE;}
		
		// N° compte
		if (!preg_match("#[0-9]{5}#", ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_account_number']))))
		{$bad_account_number = TRUE; $error = 0;}else{$bad_account_number = FALSE;}
		
		// Clé rib
		if (!preg_match("#[0-9a-zA-Z]{2}#", ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_key']))))
		{$bad_rib_key = TRUE; $error = 0;}else{$bad_rib_key = FALSE;}
			
		// IBAN
		if (!preg_match("#[0-9A-Za-z]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_iban']))))
		{$bad_rib_iban = TRUE; $error = 0;}else{$bad_rib_iban = FALSE;}
		
		// BIC
		if (!preg_match("#[0-9A-Za-z]#", ($this->tools_object->varSanitizer($_POST['eoinvoice_rib_bic']))))
		{$bad_rib_bic = TRUE; $error = 0;}else{$bad_rib_bic = FALSE;}
		
		
			
		
		// Tableau de résultats du contrôle de regex
		$settings_regex_array = array($error, $bad_name, $bad_adress, $bad_city, $bad_postcode, $bad_country, $bad_phone, $bad_telecopy, $bad_email, $bad_tax_number, $bad_capital, $bad_float_count, $bad_rib_bank_code, $bad_rib_register_code, $bad_account_number, $bad_rib_key, $bad_rib_iban, $bad_rib_bic);
		
		// On retourne le tableau
		return $settings_regex_array;
	}
	
	// Méthode permettant l'affichage et la mise en forme des erreurs de saisies
	// à partir du tableau retourné par la fonction settings_regex_check()
	function settings_regex_display($settings_regex_array)
	{
		// Entête / Panneau erreur
		echo '<div class="error"><p style="color:red;"><strong>' . __('Erreur(s) (Champs vide ou caract&egrave;re(s) non autoris&eacute;(s))', 'eoinvoice_trdom') . '</strong></p>';
	
		// INFOS
		if ($settings_regex_array[1])
		{echo __("Nom du magasin non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[2])
		{echo __("Adresse non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[3])
		{echo __("Ville non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[4])
		{echo __("Code postal non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[5])
		{echo __("Etat non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[6])
		{echo __("Num&eacute;ro de t&eacute;l&eacute;phone non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[7])
		{echo __("Num&eacute;ro de fax non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[8])
		{echo __("E-Mail non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[9])
		{echo __("Num&eacute;ro de TVA intracommunautaire non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[10])
		{echo __("Capital non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[11])
		{echo __("Nombre de chiffres apr&egrave;s la virgule non conforme", 'eoinvoice_trdom') . '<br />';}
		// RIB
		if ($settings_regex_array[12])
		{echo __("Code banque non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[13])
		{echo __("Code guichet non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[14])
		{echo __("Num&eacute;ro de compte non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[15])
		{echo __("Cl&eacute; RIB non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[16])
		{echo __("Code IBAN non conforme", 'eoinvoice_trdom') . '<br />';}
		if ($settings_regex_array[17])
		{echo __("Code BIC non conforme", 'eoinvoice_trdom') . '<br />';}
			
			
		echo '</div>';
	}
	
	// Affiche le tableau des gestionnaires
	function display_managers_panel($store_selected)
	{
		?>
			<div>
			<form method="post" action="">
			<input type="hidden" name="eoinvoice_hidden" value="G"/>
				<table class="widefat" style="width:350px;">
					<thead><tr><th><?php echo __( 'Gestionnaires', 'eoinvoice_trdom' ) ?></th><th></th></tr></thead>
					<?php
					$manager_array = $this->user_store_table_object->store_manager_list($store_selected);
					for($i = 0; $i < count($manager_array); $i++)
					{
						$user_info = get_userdata($manager_array[$i]);

						?>
						<tr>
							<th>
							<?php
								echo $user_info->last_name . ' ' . $user_info->first_name . ' | ' . $user_info->user_login;
							?>
							</th>
							<th><input type="submit" class="remove_something" name="eoinvoice_remove_manager_id<?php echo $manager_array[$i]; ?>" value="x" /></th>
						</tr>
						<?php
					}
					?>
					<tr>
						<th>
						<?php
						// Affiche la liste déroulante des utilisateurs suceptibles de devenir gestionnaire
						$this->tools_object->display_user_list();
						?>
						</th>
						<th><input type="submit" class="add_something" name="eoinvoice_add_manager" value="+" /></th>
					</tr>
				</table>
			</div>
			</form>
		<?php
	}
	
	function managers_update($store_number)
	{
		if(isset($_POST['eoinvoice_add_manager']))
		{
			$control = $this->user_store_table_object->link_user_as_manager($_POST['eoinvoice_selected_customer'], $store_number);
			if($control != 1){die($control);}
		}
		
		// On récupère le tableau contenant la liste des managers
		$manager_array = $this->user_store_table_object->store_manager_list($store_number);
		
		for($i=0; $i < count($manager_array); $i++)
		{
			if(isset($_POST['eoinvoice_remove_manager_id' . $manager_array[$i]]))
			{
				$this->user_store_table_object->unlink_manager($manager_array[$i], $store_number);
			}
		}
	}
}
?>
