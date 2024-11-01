<?php
// DEFINITION CLASSE Init

class eoinvoice_init
{
	var $eoinvoice_settings;
	var $eoinvoice_front;
	var $eoinvoice_entry;
	var $eoinvoice_customer;
	var $eoinvoice_store_creation;
	var $eoinvoice_service;
	var $eoinvoice_stats;
	
	var $tools_object;

	// CONSTRUCTEUR
	
	function eoinvoice_init($from_main_settings, $from_main_front, $from_main_entry, $from_main_customer, $from_main_store_creation, $from_main_service, $from_main_stats)
	{
		$this->eoinvoice_settings = $from_main_settings;
		$this->eoinvoice_front = $from_main_front;
		$this->eoinvoice_entry = $from_main_entry;
		$this->eoinvoice_customer = $from_main_customer;
		$this->eoinvoice_store_creation = $from_main_store_creation;
		$this->eoinvoice_service = $from_main_service;
		$this->eoinvoice_stats = $from_main_stats;
	
		// ACTIVATION / DESACTIVATION
		// Fonctions de WordPress appelant la méthode d'activation/ de désactivation
		register_activation_hook(EOINVOICE_MAIN_PHPFILE_PATH, array($this,'eoinvoice_activate'));
		register_deactivation_hook(EOINVOICE_MAIN_PHPFILE_PATH, array($this,'eoinvoice_deactivate'));
		
		// INITIALISATION DES STYLES
		add_action('init', array($this,'eoinvoice_init_css'));
		// INITIALISATION DE JQUERY
		add_action('init', array($this,'eoinvoice_init_js'));
		// VARIABLES A TRANSMETTRE AU JS
		add_action('admin_head', array($this,'eoinvoice_add_js_var'));
		
		// OUTILS
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		$this->tools_object = new eoinvoice_tools();
		
		// MISE EN PLACE DU MENU
		add_action('admin_menu', array($this,'eoinvoice_init_menu'));
	}
	
	// METHODES
	
	// FONCTION D'ACTIVATION
	function eoinvoice_activate()
	{
		// Création des tables communes à tous les stores
		eoinvoice_db_creation_generic();
	}
	
	// FONCTION DE DESACTIVATION
	function eoinvoice_deactivate()
	{
		update_option('eoinvoice_db_version', 1);
	}
	
	// CREATION DES MENUS
	function eoinvoice_init_menu()
	{
		// On récupère le rôle de l'utilisateur
		$role = $this->tools_object->current_user_is();
		
		// Si les infos magasins ne sont pas encore remplies, on ne proposera que le menu de réglages
		// apres avoir automatiquement logué le manager sur ce magasin
		if($role == 'manager')
		{
			// On cherche un magasin sans coordonnées
			$empty_store = $this->tools_object->is_any_store_empty();
			// Si on trouve un magasin sans coordonnées, on logue automatiquement sur le magasin en question
			if($empty_store != 0)
			{
				$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
				$_SESSION[$session_store_word] = $empty_store;
			}
		}
			
		// et on adapte l'affichage du menu en conséquence
		
		// Si coordonnées magasin pas encore remplies
		// seul le menu réglages magasin est affiché...
		if($empty_store == 0)
		{
			// Menu principal
			add_menu_page( __( 'Facturation', 'eoinvoice_trdom' ), __( 'Facturation', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu', array($this->eoinvoice_front,'eoinvoice_front_page'), EOINVOICE_HOME_URL . '/media/icon.png');
			// Renommage premier item consultation
			add_submenu_page( 'eoinvoice_menu', __( 'Consultation', 'eoinvoice_trdom' ), __( 'Consultation', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu', array($this->eoinvoice_front,'eoinvoice_front_page'));
		}
		else
		{
			// Menu principal
			add_menu_page( __( 'Facturation', 'eoinvoice_trdom' ), __( 'Facturation', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu', array($this->eoinvoice_settings,'eoinvoice_settings_page'), EOINVOICE_HOME_URL . '/media/icon.png');
			// Réglages
			add_submenu_page( 'eoinvoice_menu', __( 'R&eacute;glages magasin', 'eoinvoice_trdom' ), __( 'R&eacute;glages magasin', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu', array($this->eoinvoice_settings,'eoinvoice_settings_page'));
		}
		
		// Tableau de bord... pas affiché côté client
		if($role != 'client' && $empty_store == 0)
		{
			add_submenu_page( 'eoinvoice_menu', __( 'Tableau de bord', 'eoinvoice_trdom' ), __( 'Tableau de bord', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu_stats', array($this->eoinvoice_stats,'eoinvoice_stats_page'));
		}
		
		// Nouvelle facture... pas affiché côté client
		if($role != 'client' && $empty_store == 0)
		{
			add_submenu_page( 'eoinvoice_menu', __( 'Nouvelle facture', 'eoinvoice_trdom' ), __( 'Nouvelle facture', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu_entry', array($this->eoinvoice_entry,'eoinvoice_entry_page'));
		}
		
		// Réglages magasin... pas affiché côté client
		if($role != 'client' && $empty_store == 0)
		{
			add_submenu_page( 'eoinvoice_menu', __( 'R&eacute;glages magasin', 'eoinvoice_trdom' ), __( 'R&eacute;glages magasin', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu_settings', array($this->eoinvoice_settings,'eoinvoice_settings_page'));
		}
		
		// Création magasin... pas affiché côté gestionnaire
		if($role != 'manager'  && $empty_store == 0)
		{
			add_submenu_page( 'eoinvoice_menu', __( 'Nouveau magasin', 'eoinvoice_trdom' ), __( 'Nouveau magasin', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu_store_creation', array($this->eoinvoice_store_creation,'eoinvoice_store_creation_page'));
		}
		
		// Import
		if($empty_store == 0)
		{
			add_submenu_page( 'eoinvoice_menu', __( 'Web-service', 'eoinvoice_trdom' ), __( 'Web-service', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu_service', array($this->eoinvoice_service,'eoinvoice_service_page'));
		}
		
		// Réglages client... pas affiché côté admin
		if($role != 'admin'  && $empty_store == 0)
		{
			add_submenu_page( 'eoinvoice_menu', __( 'R&eacute;glages client', 'eoinvoice_trdom' ), __( 'R&eacute;glages client', 'eoinvoice_trdom' ), 'read', 'eoinvoice_menu_customer', array($this->eoinvoice_customer,'eoinvoice_customer_page'));
		}
	}
	
	// CHARGEMENT DES STYLES CSS
	function eoinvoice_init_css()
	{
		wp_register_style('eoinvoice_style', EOINVOICE_HOME_URL . 'css/whole.css');
		wp_enqueue_style('eoinvoice_style');
	}
	
	// CHARGEMENT DES SCRIPTS JS
	function eoinvoice_init_js()
	{
		// INITIALISATION DE JQUERY
		wp_enqueue_script('jquery');
		wp_enqueue_script('snippets', EOINVOICE_HOME_URL . 'js/eoinvoice_snippets.js');
	}
	
	// VARIABLES DE CHEMINS JS
	function eoinvoice_add_js_var()
	{
		echo '<script type=\'text/javascript\'>var eoinvoice_home_url="' . EOINVOICE_HOME_URL . '";</script>';
	}
}

?>
