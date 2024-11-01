<?php

class eoinvoice_tools
{
	var $adress_table_object;
	var $store_table_object;
	var $user_adress_table_object;
	var $store_list_table_object;
	var $user_store_table_object;
	var $invoice_table_object;
	
	function eoinvoice_tools()
	{
		// On charge les classes nécessaires pour la gestion et la lecture des tables en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_user_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_user_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		
		$this->adress_table_object = new eoinvoice_adress_table();
		$this->store_table_object = new eoinvoice_store_table();
		$this->user_adress_table_object = new eoinvoice_user_adress_table();
		$this->store_list_table_object = new eoinvoice_store_list_table();
		$this->user_store_table_object = new eoinvoice_user_store_table();
		$this->invoice_table_object = new eoinvoice_invoice_table();
	}

	// Récupère tous les réglages magasin en base de données et les retourne sous forme d'un tableau
	// [0] $store_adress_id 		[1] $store_name 		[2] $store_email 	[3] $store_tax_number
	// [4] $store_accept_check		[5] $society_type		[6] $society_capital
	function eoinvoice_get_store_info($store_number)
	{
		$store_adress_id = $this->store_table_object->get_store_element($store_number, 'store_adress_id'); // id de l'adresse actuelle
		$store_tax_number  = $this->store_table_object->get_store_element($store_number, 'store_tax_number'); // numero tva du magasin
		$store_name = $this->store_table_object->get_store_element($store_number, 'store_name'); // nom du magasin
		$store_email = $this->store_table_object->get_store_element($store_number, 'store_email'); // email du magasin
		$store_accept_check = $this->store_table_object->get_store_element($store_number, 'store_accept_check'); // accepte les cheques ?
		$society_type = $this->store_table_object->get_store_element($store_number, 'society_type'); // accepte les cheques ?
		$society_capital = $this->store_table_object->get_store_element($store_number, 'society_capital');
	
		$info_array = array($store_adress_id, $store_name, $store_email, $store_tax_number, $store_accept_check, $society_type, $society_capital);
		return $info_array;
	}
	
		// Récupère le RIB d'un magasin et le rtourne sous forme d'un tableau
	// [0] code banque	[1] code guichet	[2] numéro de compte	[3] clé rib
	// [4] code IBAN	[5] code BIC
	function eoinvoice_get_store_bic($store_number)
	{
		$bank_code = $this->store_table_object->get_store_element($store_number, 'rib_fr_bank');
		$register_code = $this->store_table_object->get_store_element($store_number, 'rib_fr_register');
		$account = $this->store_table_object->get_store_element($store_number, 'rib_fr_account');
		$key = $this->store_table_object->get_store_element($store_number, 'rib_fr_key');
		$iban = $this->store_table_object->get_store_element($store_number, 'rib_IBAN');
		$bic = $this->store_table_object->get_store_element($store_number, 'rib_BIC');
		
		$rib_array = array($bank_code, $register_code, $account, $key, $iban, $bic);
		return $rib_array;
	}
	
	// Retourne l'id_store le plus récent
	function get_last_store_id($store_number)
	{
		return $this->store_table_object->get_last_store_id($store_number);
	}
	
	// Récupère tous les réglages magasin en base de données et les retourne sous forme d'un tableau
	// [0] $street_adress 	[1] $suburb		[2] $city 	[3] $postcode
	// [4] $state			[5] $country	[6] $phone	[7] $telecopy
	function eoinvoice_get_adress($adress_id)
	{
		$street_adress = $this->adress_table_object->get_adress_element($adress_id, 'street_adress'); // adresse rue du magasin
		$city = $this->adress_table_object->get_adress_element($adress_id, 'city'); // ville
		$postcode = $this->adress_table_object->get_adress_element($adress_id, 'postcode'); // code postal
		$suburb = $this->adress_table_object->get_adress_element($adress_id, 'suburb'); // quartier
		$state = $this->adress_table_object->get_adress_element($adress_id, 'state'); // état
		$country = $this->adress_table_object->get_adress_element($adress_id, 'country'); // pays
		$phone = $this->adress_table_object->get_adress_element($adress_id, 'phone'); // numéro de téléphone
		$telecopy = $this->adress_table_object->get_adress_element($adress_id, 'telecopy'); // numéro de fax
	
		$info_array = array($street_adress, $suburb, $city, $postcode, $state, $country, $phone, $telecopy);
		return $info_array;
	}
	
	// Récupère les coordonnées user en base de données et les retourne sous forme d'un tableau
	// [0] $user_first_name 				[1] $user_last_name
	// [2] $user_delivery_adress_id 		[3] $user_billing_adress_id
	function eoinvoice_get_customer_info($user_id)
	{	
		$user_first_name = $this->user_adress_table_object->eoinvoice_get_user_info($user_id, 1); // prenom
		$user_last_name = $this->user_adress_table_object->eoinvoice_get_user_info($user_id, 2); // nom
		$user_delivery_adress_id = $this->user_adress_table_object->eoinvoice_get_user_adress_id($user_id, 1); // id adresse de livraison
		$user_billing_adress_id = $this->user_adress_table_object->eoinvoice_get_user_adress_id($user_id, 2); // id adresse de facturation
		$user_nickname = $this->user_adress_table_object->eoinvoice_get_user_info($user_id, 0); // login/nickname client
		
		$info_array = array($user_first_name, $user_last_name, $user_delivery_adress_id, $user_billing_adress_id, $user_id, $user_nickname);
		return $info_array;
	}
	
	// Retourne l'id de l'utilisateur parcourant la page
	function eoinvoice_get_current_user_id()
	{
		global $current_user;
		get_currentuserinfo();
		
		return $current_user->id;
	}
	
	// Crée une liste déroulante contenant tout les users/clients
	function eoinvoice_get_users_list()
	{
		// On récupère tout les ID user/client
		$users_id_array = $this->user_adress_table_object->eoinvoice_get_user_id_list();
		
		// On va rajouter les infos clients dans un nouveau tableau
		$users_info_array = array();
		// On ajoute les infos client par client
		for($i = 0; $i < (count($users_id_array)); $i++)
		{
			// Utilisation de eoinvoice_get_customer_info() -> voir header fonction pour les spécifications
			$users_info_array[$i] = $this->eoinvoice_get_customer_info($users_id_array[$i]);
		}
		
		// On retourne le tout
		return $users_info_array;
	}
	
	// Affiche le contenu de POST
	function display_post()
	{
		echo '<pre>'; 
		print_r($_POST);
		echo '</pre>';
	}
	
	// Fonction de compatibilité (même principe que user_can de WP >= 3.1)
	function user_can_legacy($user_id, $user_level)
	{
		$user_info = get_userdata($user_id);
		$user_can = $user_info->user_level;
		return ($user_level <= $user_can);
	}
	
	// Retourne la liste des magasins a afficher dans la liste de sélection pour l'utilisateur en cours (admin, manager ou basic)
	// Cette liste contient les magasins dont l'utilisateur est manager ainsi que ceux dont il est client
	function current_user_store_list($type)
	{
		// Variable globale wp indispensable
		global $current_user;
		// On récupère les infos
		get_currentuserinfo();
		// On récupère l'id de l'utilisateur en cours
		$user_id = $current_user->ID;
		// On récupère la liste des magasins / gestionnaire
		$user_role_array = $this->user_store_table_object->user_store_list($user_id);
		// On récupère la liste des magasins / admin
		$admin_role_array = $this->store_list_table_object->get_store_list();
		// On récupère la liste des magasins / client
		$customer_role_array = array();
		$f = 0; // Chariot
		for($i = 1; $i <= count($admin_role_array); $i++)
		{
			// On récupère les factures de l'user_id sur le store $i
			$invoice_array = $this->invoice_table_object->get_invoices($i, $user_id);
			// Si au moins une facture on note le store_number
			if(count($invoice_array) > 0)
			{$customer_role_array[$f] = $i;}
		}
		
		$user_role = $this->current_user_is();
		// On retourne le bon tableau en fonction du rôle de l'user
		if($user_role == 'admin'){$role_array = $admin_role_array;}
		else if($user_role == 'manager')
		{
			// La consultation de factures en tant que client et manager
			if($type == 'consult')
			{
				// On fusionne les 2 tableaux
				$role_array = array_merge($user_role_array, $customer_role_array);
			}
			else // La consultation en tant que manager
			{
				$role_array = $user_role_array;
			}
		}
		else{$role_array = $customer_role_array;}
		
		// On retourne le tableau
		return $role_array;
	}
	
	// Renvoie vrai si l'utilisateur en cours est gestionnaire du magasin $store_number
	function current_user_is_manager_of($store_number)
	{
		// Variable globale wp indispensable
		global $current_user;
		// On récupère les infos
		get_currentuserinfo();
		// On récupère l'id de l'utilisateur en cours
		$user_id = $current_user->ID;
		
		// On initialise la sortie à FALSE
		$answer = 0;
		$store_list_as_manager = $this->user_store_table_object->user_store_list($user_id);
		for($i = 0; $i < count($store_list_as_manager); $i++)
		{
			if($store_list_as_manager[$i] == $store_number)
			{$answer = 1;}
		}
		return $answer;
	}
	
	
	
	// Retourne le rôle de l'utilisateur en cours (admin/manager/client)
	function current_user_is()
	{
		// Variable globale wp indispensable
		global $current_user;
		// On récupère les infos
		get_currentuserinfo();
		// On récupère l'id de l'utilisateur en cours
		$user_id = $current_user->ID;
		// On repère le rôle/les droits de l'utilisateur
		$verif_array = $this->user_store_table_object->user_store_list($user_id);
		if(isset($verif_array[0])){$role = 'manager';}else{$role = 'client';}
		if(current_user_can(ADMIN_CAPABILITY)){$role = 'admin';}
		
		return $role;
	}
	
	// Affiche un menu déroulant listant tout les store (numéro/nom)
	// Sortie en $_POST['eoinvoice_selected_store']
	function display_store_list($type)
	{
		// On récupère la liste des magasins adapté au rôle de l'utilisateur
		$store_list_array = $this->current_user_store_list($type);
		
		// Si cette liste contient qqch, on affiche le panneau de sélection
		if(isset($store_list_array[0]))
		{
			?>
				<form name="eoinvoice_store_selection_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					<table class="widefat" style="width:200px;">
						<thead>
							<tr><th><?php echo __( 'Choix du magasin', 'eoinvoice_trdom' ) ?></th><th></th></tr>
						</thead>
						<tr><th>
			<?php

			$user_id = $this->eoinvoice_get_current_user_id();
			// On récupère le rôle de l'utilisateur en cours ainsi que son id
			$role = $this->current_user_is();
			
			// On ouvre la liste
			echo '<select name="eoinvoice_selected_store">';
			for($i = 0; $i < (count($store_list_array)); $i++)
			{
				
				// On récupère le nombre de factures non payées à afficher en fonction du rang de l'utilisateur [client/gestionnaire/admin]
				// Si l'user en cours est l'admin
				if($role == 'admin' || $this->current_user_is_manager_of($store_list_array[$i]))
				{
					// On compte toutes les factures non payées
					$unpaid_invoices_count = count($this->unpaid_invoices($store_list_array[$i]));
				}
				else // S'il est client
				{
					// On affiche seulement le nombre de factures qu'IL n'a pas encore payé
					$unpaid_invoices_count = count($this->user_unpaid_invoices($store_list_array[$i], $user_id));
				}
				
				echo '<option value=' . $store_list_array[$i];
				echo '>';
				// On affiche le numéro
				echo  __( 'Magasin n°', 'eoinvoice_trdom' ) . $store_list_array[$i] . ' | ';
				// On récupère le nom
				$store_name = $this->store_table_object->get_store_element($store_list_array[$i], 'store_name');
				echo  $store_name;
				echo ' | ' . $unpaid_invoices_count . ' factures non réglées';
				echo '</option>';
			}
			?>
				</th><th><input class="button" type="submit" name="eoinvoice_selected_store_button" value="<?php echo __( 'Choisir', 'eoinvoice_trdom' ); ?>"/></th></tr>
				</select>
				</table>
				</form>
			<?php
		}
		else
		{
			?>
				<div class="updated"><p><strong><?php echo __('Vous n\'avez aucune facture à consulter.', 'eoinvoice_trdom' ); ?></strong></p></div>
			<?php
		}
	}
	
	// Affiche la page de choix du magasin
	function eoinvoice_store_list_page($page_title, $type)
	{
		?>
			<div class="wrap">
				<?php echo "<h2>" . $page_title . "</h2>"; ?>
				<?php $this->display_store_list($type); ?>
			</div>
		<?php
	}
	
	// Se déconnecte du store en cours
	function logout_check()
	{
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		
		if(isset($_POST['logout']))
		{unset($_SESSION[$session_store_word]);}
	}
	
	function display_logout_form($store_selected)
	{
		$store_info_tab = $this->eoinvoice_get_store_info($store_selected);
		?>
			<table class="widefat" style="width:200px;">
			<thead><tr><th><?php echo __( 'Magasin', 'eoinvoice_trdom' ); ?></th><th></th></tr></thead>
			<tr><th><?php echo __( 'Num. ', 'eoinvoice_trdom' ) . $store_selected . '<br />' . $store_info_tab[1]; ?></th><th><?php $this->logout_button(); ?></th></tr>
			</table>
		<?php
	}
	function logout_button()
	{
		?><div id="export_html"><form method="post" name="logout_button" action=""><input class="button" type="submit" name="logout" value="<?php echo __( 'Changer', 'eoinvoice_trdom' ); ?>"/></form></div><?php
	}
	
	// Si le gestionnaire n'a qu'un magasin, on le logue automatiquement
	function single_store_autologin()
	{
		$store_array = $this->current_user_store_list('consult');
		if(count($store_array) == 1)
		{
			// On forme l'identifiant de session
			$session_store_word = 'user' . $this->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
			// On enregistre
			$_SESSION[$session_store_word] = $store_array[0];
		}
	}
	
	// Retourne le numéro d'un store sans coordonnées
	function is_any_store_empty()
	{
		// On récupère la liste des stores gérés par l'utilisateur courant
		$store_list = $this->current_user_store_list('consult');
		
		$store_to_fill = 0;
		
		// Parcours
		for($i = 0; $i < count($store_list); $i++)
		{
			$store_info_array = $this->eoinvoice_get_store_info($store_list[$i]);
			if(!isset($store_info_array[1])){$store_to_fill = $store_list[$i];}
		}

		return $store_to_fill;
	}
	
	// Retourne un tableau contenant les numéros des factures non payées pour un magasin donné
	function unpaid_invoices($store_number)
	{
		// On récupère la liste des factures du magasin concerné
		$invoices_array = $this->invoice_table_object->get_invoices($store_number, 0);
		// Tableau répertoriant les id des factures non payées
		$unpaid_invoices_array = array();
		// Chariot
		$t = 0;
		// Parcours du tableau des factures à la recherche de factures non payées
		for($i = 0; $i < count($invoices_array); $i++)
		{
			if($this->invoice_table_object->get_invoice_element($store_number, $invoices_array[$i], 'invoice_payment_status') == 'NotYet')
			{
				$unpaid_invoices_array[$t] = $invoices_array[$i];
				$t++;
			}
		}
		// On retourne notre tableau
		return $unpaid_invoices_array;
	}
	
	// Retourne un tableau contenant les id des factures non payées pour un magasin donné ($store_number) pour un client donné ($user_id)
	function user_unpaid_invoices($store_number, $user_id)
	{
		// On récupère le tableau des factures non payées
		$unpaid_invoices_array = $this->unpaid_invoices($store_number);
		echo count($unpaid_invoices_array);
		// Tableau répertoriant les id des factures non payées
		$user_unpaid_invoices_array = array();
		// Chariot
		$t = 0;
		// Parcours du tableau des factures se référant au client d'user_id donné
		for($i = 0; $i < count($unpaid_invoices_array); $i++)
		{
			if($this->invoice_table_object->get_invoice_element($store_number, $unpaid_invoices_array[$i], 'user_id') == $user_id)
			{
				$user_unpaid_invoices_array[$t] = $unpaid_invoices_array[$i];
				$t++;
			}
		}
		// On retourne notre tableau
		return $user_unpaid_invoices_array;
	}
	
	// Affiche un menu déroulant de sélection de l'utilisateur (exclu l'admin)
	function display_user_list()
	{
		// On récupère le nom, le prénom et les coordonnées de tous nos clients dans un tableau
		$customers_array = $this->eoinvoice_get_users_list();
		
		// On ouvre la liste
		echo '<select name="eoinvoice_selected_customer">';
		for($i = 0; $i < (count($customers_array)); $i++)
		{
			if (!$this->user_can_legacy($customers_array[$i][4], ADMIN_LEVEL))
			{
				echo '<option value=\'' . $customers_array[$i][4];
				echo '\'>';
				echo $customers_array[$i][0] . " " . $customers_array[$i][1] . " | login: " . $customers_array[$i][5];
				echo '</option>';
			}
		}
		// On ferme notre liste
		echo '</select>';
	}
	
	function make_recursiv_dir($directory)
	{
		$directoryComponent = explode('/',$directory);
		$str = '';
		foreach($directoryComponent as $k => $component)
		{
			if((trim($component) != '') && (trim($component) != '..') && (trim($component) != '.'))
			{
				$str .= '/' . trim($component);
				if(long2ip(ip2long($_SERVER["REMOTE_ADDR"])) == '127.0.0.1')
				{
					if(!is_dir(substr($str,1)) && (!is_file(substr($str,1)) ) )
					{
					   mkdir( substr($str,1) );
					}
				}
				else
				{
					if(!is_dir($str) && (!is_file($str) ) )
					{
					   mkdir( $str );
					}
				}
			}
		}
		//$this->chmod_dir($directory);
	}
   
	function chmod_dir($dir)
	{
		$tab=explode('/',$dir);
		$str='';
		foreach($tab as $k => $v )
		{
			if((trim($v)!=''))
			{
				$str.='/'.trim($v);
				if( (trim($v)!='..') &&(trim($v)!='.') )
				{
					if(!is_dir(substr($str,1)) && (!is_file(substr($str,1)) ) )
					{
					   chmod(str_replace('//','/',$str), 0755);
					}
				}
			}
		}
	}
	
	// Ajoute du texte ($label) à un champ d'un tableau ($tab)
	// à la position donnée ($position)
	function add_label_to_tab($tab,$position,$label)
	{
		for($i = 0; $i < count($tab); $i++)
		{
			$tab[$position] = $tab[$position] . ' ' . $label;
		}
	}

	function varSanitizer($varToSanitize, $varDefaultValue = '', $varType = '')
	{
		$sanitizedVar = (trim(strip_tags(stripslashes($varToSanitize))) != '') ? trim(strip_tags(stripslashes(($varToSanitize)))) : $varDefaultValue ;
		return $sanitizedVar;
	}

}

?>
