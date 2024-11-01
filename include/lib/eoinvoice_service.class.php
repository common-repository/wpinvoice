<?php
// CLASSE Service
// Permet le traitement de fichiers de commande au format XML

class eoinvoice_service
{
	// Objet Tools permettant de récupérer les coordonnées magasin
	var $tools_object;
	// Objets de gestion de table
	var $invoice_row_object;
	var $invoice_object;
	var $store_object;
	var $store_list_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_service()
	{
		// On charge les classes nécessaires pour la lecture des tables de coordonnées en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_row_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_user_store_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_adress_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_store_list_table.class.php');
		
		// INSTANCIATION
		// Outils divers
		$this->tools_object = new eoinvoice_tools();
		// Gestion de la base de données
		$this->invoice_row_table_object = new eoinvoice_invoice_row_table();
		$this->invoice_table_object = new eoinvoice_invoice_table();
		$this->store_table_object = new eoinvoice_store_table();
		$this->store_list_table_object = new eoinvoice_store_list_table();
		$this->user_store_table_object = new eoinvoice_user_store_table();
		$this->adress_table_object = new eoinvoice_adress_table();
	}
	
	
	
	
	// METHODES
	
	function eoinvoice_service_page()
	{
		// DEBUG - Permet de crypter un fichier à la volée
		//$this->crypt_purchase('http://localhost/crypt.xml', EOINVOICE_HOME_DIR . '70c916466dfed6ba4dee921059b180f2.xml', '23cM4JKE?L');
		
		if(isset($_POST['eoinvoice_xml_filepath']))
		{		
			// Si ce fichier est une description d'un nouveau magasin
			if(substr($_POST['eoinvoice_xml_filepath'],-9) == 'store.xml')
			{
				// On charge le fichier XML
				$xml = simplexml_load_file($_POST['eoinvoice_xml_filepath']);
				// On crée le magasin en base de données
				$this->storeXML_to_key($xml);
				// On affiche/mail l'identifiant unique ainsi que le mot de passe XML
				// Entête de confirmation d'importation
				?><div class="updated"><p><?php echo __('Votre magasin à bien été importé , vous pouvez d&eacute;sormais gérer votre facturation via EOinvoice !<br/><br/>Votre identifiant unique: <strong>'. $this->store_list_table_object->get_store_uniqid($this->store_list_table_object->how_many_stores()) . '</strong><br/>Votre clé: <strong>' . $this->store_list_table_object->get_store_xml_import_key($this->store_list_table_object->how_many_stores()) . '</strong>', 'eoinvoice_trdom' ); ?></strong></p></div><?php
			}
			else // Sinon si c'est une description de commande
			{
				// On récupère le nom du fichier sans le chemin ni l'extension
				$base_filename = basename($_POST['eoinvoice_xml_filepath'], '.xml');
				// On regarde si le nom du fichier correspond à un uniqid existant
				$store_number = $this->store_list_table_object->get_store_by_uniqid($base_filename);
				
				if(isset($store_number))
				{
					// On récupère et on décode la clé de cryptage
					$key = $this->store_list_table_object->get_store_xml_import_key($store_number);
					// On décrypte le fichier - la clé doit être bonne
					$purchase = $this->decrypt_purchase($_POST['eoinvoice_xml_filepath'], $key);
					// On charge le fichier XML décrypté
					$xml = simplexml_load_string($purchase);
					// On génère la facture
					$this->purchaseXML_to_invoice($store_number, $xml);
					// Entête de confirmation d'importation
					?><div class="updated"><p><strong><?php echo __('La commande a bien été traitée, vous pouvez d&eacute;sormais consulter votre facture dans le menu "Consultation".', 'eoinvoice_trdom' ); ?></strong></p></div><?php
				}
				else
				{
					?><div class="error"><p><?php echo __('Le fichier fait référence à un <strong>uniqid</strong> inexistant, veuillez vérifier vos réglages, ou contactez votre administrateur.', 'eoinvoice_trdom' ); ?></p></div><?php
				}
			
			}
		}
		else
		{
		?>
			<div id="eoinvoice_xml_filepath" style="margin-top:20px;">
				<form method="post" action="">
				<table class="widefat" style="width:350px;">
					<thead>
						<tr><th><?php echo __( 'Chemin XML', 'eoinvoice_trdom' ) ?></th><th></th></tr>
					</thead>
					<tr><th><?php echo __('URL du fichier', 'eoinvoice_trdom' ); ?></th><th><input type="text" name="eoinvoice_xml_filepath" value="http://www.market.com/purchase.xml" size="40" /></th></tr>
				</table>
				</form>
			</div>
		<?php
		}
	}
	
	// Méthode principale permettant la création d'un magasin depuis un fichier XML correspondant
	// Retourne une clé unique permettant au magasin de générer les XML pour EOinvoice
	function storeXML_to_key($store)
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
		$user_id = $this->tools_object->eoinvoice_get_current_user_id();
		$control = $this->user_store_table_object->link_user_as_manager($user_id, $store_number);
		if($control != 1){die($control);}
		// Creation d'une ligne adresse
		$control = $this->adress_table_object->new_adress($store->address->street, $store->address->suburb, $store->address->city, $store->address->postcode, $store->address->state, $store->address->country, $store->address->phone, $store->address->telecopy);
		if($control != 1){die($control);}
		// Creation d'une ligne store référencant la nouvelle adresse
		$new_store_adress_id = $this->adress_table_object->get_last_adress_id();
		$control = $this->store_table_object->update_store($store_number, $store->info->name, $store->info->email, $store->info->taxnumber, $new_store_adress_id, $store->info->acceptcheck, $store->info->type, $store->info->capital, $store->bank->frbank, $store->bank->frreg, $store->bank->fraccount, $store->bank->frkey, $store->bank->iban, $store->bank->bic);
		if($control != 1){die($control);}
	}
	
	// Méthode principale permettant la création d'un facture depuis un fichier XML correspondant
	function purchaseXML_to_invoice($store_number, $produits)
	{	
		// Tableau contenant les tableaux de chaque ligne
		$rows_array = array();
		
		// Compteur de ligne
		$row_count = 0;
		
		foreach ($produits->cart->item as $produit)
		{
			// Tableau contenant les infos d'une ligne
			$row_array = array();
			
			// On trie
			$row_array['name'] = $produit->name;
			$row_array['desc'] = $produit->desc;
			$row_array['ref'] = $produit->ref;
			$row_array['qty'] = $produit->qty;
			$row_array['weight'] = $produit->weight;
			$row_array['tax'] = $produit->tax;
			$row_array['discount'] = $produit->discount;
			$row_array['pricenotax'] = $produit->pricenotax;
			
			// On calcule les sommes
			$row_array_sum = $this->entry_row_check($row_array);
			// On organise
			$row_array_final = $this->row_data($row_array, $row_array_sum);
			
			// On ajoute la ligne au tableau des lignes
			$rows_array[$row_count] = $row_array_final;
			
			// On incrémente le compteur de lignes
			$row_count++;
		}
		
		// On récupère l'id du client
		$customer_id = $produits->customer['id'];
		
		// On calcule les totaux
		$totals_array = $this->total_row_check($rows_array);
		
		// On sort les informations concernant la facture
		$invoice_array = $this->invoice_data($totals_array, $customer_id);
		
		// On crée la facture
		$this->create_invoice($store_number, $invoice_array, $rows_array);
	}
	
	// Effectue les calculs d'une ligne de la facture référencée par son numéro
	// dont on retourne les totaux dans un tableau :
	// [0] product_qty		[1] row_weight	[2] product_price
	// [3] row_base_price	[4] row_price	[5] row_discount
	function entry_row_check($row_array)
	{
		// Quantité commandée
		$product_qty = $row_array['qty'];
		// Prix du produit HT
		$product_base_price = $row_array['pricenotax'];
		// Prix du produit HT avec remise
		$product_base_price_with_discount = $product_base_price - ($product_base_price * (0.01 * $row_array['discount']));
		// Poids total de la ligne
		$row_weight = ($product_qty * $row_array['weight']);
		// Prix du produit TTC
		$product_price = ($product_base_price + ( (0.01 * $row_array['tax']) * $product_base_price));
		// Prix du produit TTC avec remise
		$product_price_with_discount = ($product_base_price_with_discount + ( (0.01 * $row_array['tax']) * $product_base_price_with_discount));
		// Prix de la ligne HT
		$row_base_price = ($product_qty * $product_base_price);
		// Prix de la ligne HT avec remise
		$row_base_price_with_discount = ($product_qty * $product_base_price_with_discount);
		// Montant de la remise (% -> €)
		$row_total_discount = ((0.01 * $row_array['discount']) * $row_base_price);
		// Prix de la ligne TTC
		$row_price = ($product_qty * $product_price);
		// Prix de la ligne TTC avec remise
		$row_price_with_discount = ($product_qty * $product_price_with_discount);
		
		// On stocke les informations dans un tableau
		$row_totals = array($product_qty, $row_weight, $product_price_with_discount, $row_base_price_with_discount, $row_price_with_discount, $row_total_discount);
		
		// Qu'on renvoie
		return $row_totals;
	}

	// Retourne un tableau contenant toutes les informations d'une ligne de facture $row_number
	// [0] $product_name 		[1] $product_desc 		[2] $product_base_price
	// [3] $product_price 		[4] $product_ref		[5] $product_weight
	// [6] $product_tax_percent [7] $row_weight			[8] $product_qty
	// [9] $product_discount 	[10] $row_base_price	[11] $row_price
	// [12] $row_discount
	function row_data($row_array, $row_array_sum)
	{		
			$product_qty = $row_array_sum[0];
			$row_weight = $row_array_sum[1];
			$row_base_price = $row_array_sum[3];
			$row_price = $row_array_sum[4];
			$row_discount = $row_array_sum[5];
			
			$product_base_price = $row_array['pricenotax'];
			$product_price = $row_array_sum[2];
			$product_ref = $row_array['ref'];
			$product_name = $row_array['name'];
			$product_desc = $row_array['desc'];
			$product_weight = $row_array['weight'];
			$product_tax_percent = $row_array['tax'];
			$product_discount = $row_array['discount'];
			
			$row_array_final = array($product_name, $product_desc, $product_base_price, $product_price, $product_ref, $product_weight, $product_tax_percent, $row_weight, $product_qty, $product_discount, $row_base_price, $row_price, $row_discount);
			
			// On retourne le tableau complet
			return $row_array_final;
	}
	
	// Effectue le calcul des grand totaux
	// qu'on retourne dans un tableau
	// [0] base_total_price		[1] total_price	[2] total_weight
	// [3] total_qty			[4] total_discount
	function total_row_check($rows_array)
	{
		for($row_number = 0; $row_number <= count($rows_array); $row_number++)
		{	
			$total_base_price = $total_base_price + $rows_array[$row_number][10];
			$total_price = $total_price + $rows_array[$row_number][11];
			$total_weight = $total_weight + $rows_array[$row_number][7];
			$total_qty = $total_qty + $rows_array[$row_number][8];
			$total_discount = $total_discount + $rows_array[$row_number][12];
		}
				
		// On renvoie un tableau de ces totaux
		$totals_array = array($total_base_price, $total_price, $total_weight, $total_qty, $total_discount);
		return $totals_array;
	}
	
	// Retourne un tableau contenant toutes les informations d'une facture
	// [0] $base_total_price 		[1] $total_price 				[2] $total_qty
	// [3] $total_weight 			[4] $customer_billing_adress	[5] $customer_delivery_adress
	// [6] $customer_id				[7] $total_discount
	function invoice_data($totals_array, $customer_id)
	{	
		
		// A FAIRE : 	RECUPERER L'ID DU CLIENT DEPUIS LE FICHIER XML
		//				UTILISER CET ID POUR LA PARTIE CI DESSOUS
		
		// On récupère les adresses du client dans un tableau
		$customer_info_tab = $this->tools_object->eoinvoice_get_customer_info($customer_id);
		
		$customer_delivery_adress = $customer_info_tab[2];
		$customer_billing_adress = $customer_info_tab[3];
		
		// ------------------------------------------------------------
		
		$base_total_price = $totals_array[0];
		$total_price = $totals_array[1];
		$total_qty = $totals_array[3];
		$total_weight = $totals_array[2];
		$total_discount = $totals_array[4];
		
		$invoice_array = array($base_total_price, $total_price, $total_qty, $total_weight, $customer_billing_adress, $customer_delivery_adress, $customer_id, $total_discount);
		
		// On retourne le tableau complet
		return $invoice_array;
	}
	
	// Crée une nouvelle facture en base de données
	function create_invoice($store_number, $invoice_array, $rows_array)
	{
		// Entrée dans la table invoice
		$control = $this->invoice_table_object->eoinvoice_new_invoice($store_number, $invoice_array);
		if($control != 1){die($control);}
		// On récupère l'id de la facture qu'on vient de créer
		$invoice_id = $this->invoice_table_object->get_last_invoice_id($store_number);
		
		// Entrées dans la table invoice_row pour les différentes lignes
		for($row_number = 0; $row_number < count($rows_array); $row_number++)
		{	
			$this->create_invoice_row($store_number, $invoice_id, $rows_array[$row_number]);
		}
	}
	
	// Crée une nouvelle ligne de facture en base de données
	function create_invoice_row($store_number, $invoice_id, $row_array)
	{
		// Crée la ligne en base de données pour un $invoice_id donné
		$control = $this->invoice_row_table_object->new_invoice_row($store_number, $invoice_id, $row_array);
		if($control != 1){die($control);}
	}
	
	
	// Permet de chiffrer un fichier à partir d'une clé donnée
	function crypt_purchase($file, $file_output, $key)
	{
		// Lecture du fichier
		$huge_string = file_get_contents($file,'r'); 

		// Choix d'un algo, mode (couple)
		$algo = EOINVOICE_CRYPT_ALGO;    // ou la constante php MCRYPT_BLOWFISH
		$mode = EOINVOICE_CRYPT_MODE;        // ou la constante php MCRYPT_MODE_NOFB

		// Calcul des longueurs max de la clé et de l'IV
		$key_size = mcrypt_module_get_algo_key_size($algo); // 56
		$iv_size  = mcrypt_get_iv_size($algo, $mode); // 8

		// Création d'un IV aléatoire de la bonne longueur
		// N'importe quoi du moment qu'il est de la bonne longueur
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		// Choix d'une clé secrète de cryptage/décryptage et mise à longueur si trop longue
		$key = substr($key, 0, $key_size);

		// On encrypte
		$huge_string_crypt  = mcrypt_encrypt($algo, $key, $huge_string, $mode, $iv);
		
		// On crée un fichier de sortie
		$file_crypt = fopen($file_output, 'w+');
		// On met le IV au début du fichier ($iv_size caractères) puis le reste du fichier, cripté
		fputs($file_crypt, $iv.$huge_string_crypt);
		// On ferme le fichier
		fclose($file_crypt);
	}
	
	// Permet le déchiffrage d'un fichier crypté
	function decrypt_purchase($file, $key)
	{
		// Lecture du fichier
		$huge_string_crypt = file_get_contents($file,'r');
		
		// Choix d'un algo, mode
		$algo = EOINVOICE_CRYPT_ALGO;    // ou la constante php MCRYPT_BLOWFISH
		$mode = EOINVOICE_CRYPT_MODE;        // ou la constante php MCRYPT_MODE_NOF
		
		// On récupère la taille de l'IV
		$iv_size  = mcrypt_get_iv_size($algo, $mode);

		// Décryptage
		$huge_string_decrypt = mcrypt_decrypt($algo, $key, substr($huge_string_crypt,$iv_size) , $mode, substr($huge_string_crypt,0,$iv_size));
		
		return $huge_string_decrypt;
	} 
}
?>
