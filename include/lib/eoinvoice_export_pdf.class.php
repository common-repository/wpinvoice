<?php
DEFINE('EURO', chr(128)); // Sigle €

// On charge la librairie fpdf
require_once(EOINVOICE_HOME_DIR . 'include/lib/pdf/fpdf.php');
// DEFINITION CLASSE export_pdf
// Classe permettant l'export d'une facture au format pdf, hérite de la classe FPDF
class eoinvoice_export_pdf extends FPDF
{
	var $pdf_export_object;
	var $tools_object;
	var $invoice_row_object;
	var $invoice_object;
	var $ged_documents_object;
	
	// CONSTRUCTEUR
	
	function eoinvoice_export_pdf()
	{
		// Appel du constructeur parent avant toute redéfinition
		parent::FPDF();
		
		// On charge les classes nécessaires pour la lecture des tables de coordonnées en base de données
		require_once(EOINVOICE_HOME_DIR . 'include/lib/eoinvoice_tools.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_row_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_invoice_table.class.php');
		require_once(EOINVOICE_HOME_DIR . 'include/lib/db/eoinvoice_ged_documents_table.class.php');
		
		// On instancie
		// Outils divers
		$this->tools_object = new eoinvoice_tools();
		// Factures
		$this->invoice_row_object = new eoinvoice_invoice_row_table();
		$this->invoice_object = new eoinvoice_invoice_table();
		// Historisation document
		$this->ged_documents_object = new eoinvoice_ged_documents_table();
	}
	
	function invoice_export($store_selected, $invoice_id)
	{
		// On définit un alias pour le nombre de pages total
		$this->AliasNbPages();
		
		// On ajoute une page au document
		$this->AddPage();
		$this->SetFont('Arial','',10);
		// Coordonnées magasin
		$this->store_head($store_selected, $invoice_id);
		// Coordonnées client
		$name = $this->client_head($store_selected, $invoice_id);
		// Date de facturation et référence facture
		$refdate = $this->invoice_refdate($store_selected, $invoice_id);
		// Tableau des lignes de facture
		$this->rows($store_selected, $invoice_id);
		// Ligne de total
		$this->total($store_selected, $invoice_id);
		// On affiche le rib du magasin
		$this->rib($store_selected);
		// On mentionnes les informations obigatoires en bas de page
		$this->pre_footer($store_selected);
		
		// On crée le dossier si celui ci n'existe pas
		$this->tools_object->make_recursiv_dir(WP_CONTENT_DIR . "/uploads/eoinvoice/" . $store_selected);
		// On enregistre
		$this->Output(WP_CONTENT_DIR . "/uploads/eoinvoice/" . $store_selected . "/" . $refdate . ".pdf", "F");
		
		header('content-type: application/octet-stream');
		
		// Affiche le document dans une iframe.
		echo '<iframe src="' . WP_CONTENT_URL . '/uploads/eoinvoice/' . $store_selected . '/' . $refdate . '.pdf" width="100%" height="600px">
			[Your browser does <em>not</em> support <code>iframe</code>,
			or has been configured not to display inline frames.
			You can access <a href="' . WP_CONTENT_URL . '/uploads/eoinvoice/' . $store_selected . '/' . $refdate . '.pdf">the document</a>
			via a link though.]</iframe>';
		
		$user_id = $this->tools_object->eoinvoice_get_current_user_id();
		// On historise cet export en base de données
		$control = $this->ged_documents_object->add_a_mark($user_id, 'PDF_invoices', 'STORE#' . $store_selected . ' ' . $refdate . '.pdf', '/uploads/eoinvoice/' . $store_selected . '/' . $refdate . '.pdf');
		if($control != 1){die($control);}
	}
	
	// En-tête magasin
	function store_head($store_number, $invoice_id)
	{
		// On récupère l'id du magasin
		$store_id = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'store_id');
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
		$store_telecopy = $store_adress_tab[7];
				
		//Positionnement
		$this->SetY(45);
		$this->SetX(12);
		// Cadre client destinataire
		$this->rect(10, 42, 80, 40);
		// Et on écris
		// On règle la police d'écriture
		// gras pour le titre
		$this->SetFont('','B',10);
		$this->Cell($xsize,5,$store_name,0,1,'L'); $this->SetX(12);
		// Police normale pour le reste
		$this->SetFont('','',9);
		$this->Cell($xsize,4,$store_street_adress,0,1,'L'); $this->SetX(12);
		if ($store_suburb != ''){$this->Cell(80,4,$store_suburb,0,1,'L');} $this->SetX(12);
		$this->Cell($xsize,4,$store_postcode . ' ' . $store_city,0,1,'L'); $this->SetX(12);
		if ($store_state != ''){$this->Cell(80,4,$store_state,0,1,'L');} $this->SetX(12);
		$this->Cell($xsize,4,$store_country,0,1,'L'); $this->SetX(12);
		$this->Cell($xsize,4,utf8_decode(__( 'Tél.:', 'eoinvoice_trdom' )) . $store_phone,0,1,'L'); $this->SetX(12);
		$this->Cell($xsize,4,'Fax: ' . $store_telecopy,0,1,'L'); $this->SetX(12);
		$this->Cell($xsize,4,$store_email,0,1,'L'); $this->SetX(12);
		$this->Cell($xsize,4,$store_tax_number,0,1,'L'); $this->SetX(12);
		
		return $store_name;
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
		// FPDF ne décodant pas l'UTF-8, on le fait via PHP
		$customer_firstname = utf8_decode($customer_info_tab[0]);
		$customer_lastname = utf8_decode($customer_info_tab[1]);
		
		$customer_street_adress = utf8_decode($customer_billing_adress[0]);
		$customer_suburb = utf8_decode($customer_billing_adress[1]);
		$customer_city = utf8_decode($customer_billing_adress[2]);
		$customer_postcode = utf8_decode($customer_billing_adress[3]);
		$customer_state = utf8_decode($customer_billing_adress[4]);
		$customer_country = utf8_decode($customer_billing_adress[5]);

		$xsize = 80;
		
		//Positionnement
		$this->SetY(45);
		$this->SetX(102);
		// Cadre client destinataire
		$this->rect(100, 42, 100, 40);
		// Et on écris
		// On règle la police d'écriture
		// gras pour le titre
		$this->SetFont('','B',10);
		$this->Cell($xsize,5,$customer_lastname . ' ' . $customer_firstname,0,1,'L'); $this->SetX(102);
		// Police normale pour le reste
		$this->SetFont('','',9);
		$this->Cell($xsize,4,$customer_street_adress,0,1,'L'); $this->SetX(102);
		if ($customer_suburb != ''){$this->Cell($xsize,4,$customer_suburb,0,1,'L');} $this->SetX(102);
		$this->Cell($xsize,4,$customer_postcode . ' ' . $customer_city,0,1,'L'); $this->SetX(102);
		if ($customer_state != ''){$this->Cell($xsize,4,$customer_state,0,1,'L');} $this->SetX(102);
		$this->Cell($xsize,4,$customer_country . ' ',0,1,'L');
	}
	
	// Référence et date de facturation
	function invoice_refdate($store_number, $invoice_id)
	{	
		// On récupère la référence
		$invoice_ref = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'invoice_ref');
		// On récupère la date de facturation
		$invoice_add_date = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'invoice_date_add');
		// On récupère la date d'échéance
		$invoice_max_date = '';
		
		//Positionnement
		$this->SetY(25);
		$this->SetX(150);
		// Et on écris
		// On règle la police d'écriture
		$this->SetFont('','B',14);
		$this->Cell(50, 5, utf8_decode(__( 'Réf. : ', 'eoinvoice_trdom' )) . $invoice_ref,0,1,'L'); $this->SetX(135);
		$this->SetFont('','',9);
		$this->Cell(50, 4, utf8_decode(__( 'Date de facturation : ', 'eoinvoice_trdom' )) . $invoice_add_date,0,1,'L'); $this->SetX(135);
		$this->Cell(50, 4, utf8_decode(__( 'Date d\'échéance : ', 'eoinvoice_trdom' )) . $invoice_max_date,0,1,'L');
		
		return $invoice_ref . '_' . substr($invoice_add_date,0,10);
	}
	
	// Affiche le tableau des lignes de la facture
	function rows($store_number, $invoice_id)
	{
		$title_ref = utf8_decode(__( 'Référence', 'eoinvoice_trdom' ));
		$title_name = utf8_decode(__( 'Designation', 'eoinvoice_trdom' ));
		$title_qty = utf8_decode(__( 'Qté', 'eoinvoice_trdom' ));
		$title_baseprice = utf8_decode(__( 'PU HT', 'eoinvoice_trdom' ));
		$title_discount = utf8_decode(__( 'Remise', 'eoinvoice_trdom' ));
		$title_tax = utf8_decode(__( 'TVA (Taux)', 'eoinvoice_trdom' ));
		$title_price = utf8_decode(__( 'Prix TTC', 'eoinvoice_trdom' ));
		
		//Titres des colonnes
		$header = array($title_ref,$title_name,$title_qty,$title_baseprice,$title_discount,$title_tax,$title_price);
		// Largeur des colonnes
		$w = array(25,80,10,15,15,30,15);
		
		// On récupère les id des lignes de cette facture
		$rows_array = $this->invoice_row_object->get_rows_about($store_number, $invoice_id);
		
		
		$this->setXY(10,95);
		for($i=0;$i<count($header);$i++)
        {$this->Cell($w[$i],5,$header[$i],1,0,'C');}
		$this->Ln();
		
		// Puis on affiche les lignes
		for($i = 0; $i < count($rows_array); $i++)
		{
			$this->row($store_number, $rows_array[$i], $w);
		}
	}
	
	// Affiche un ligne de la facture
	function row($store_number, $row_id, $dim_array)
	{
		// On forme l'identifiant de session
		$session_store_word = 'user' . $this->tools_object->eoinvoice_get_current_user_id() . '_eoinvoice_selected_store';
		// On récupère le magasin en cours
		$store_selected = $_SESSION[$session_store_word];
		// On récupère la ligne en base de données
		$row_array = $this->invoice_row_object->get_invoice_row_array($store_number, $row_id); // sous forme de tableau
		
		// On affiche les valeurs
		$this->Cell($dim_array[0],8,$row_array['product_reference'],'LRB',0,'C');
		$this->Cell($dim_array[1],8,$row_array['product_name'],'LRB',0,'C');
		$this->Cell($dim_array[2],8,$row_array['qty_invoiced'],'LRB',0,'C');
		$this->Cell($dim_array[3],8,$row_array['product_base_price'],'LRB',0,'C');
		$this->Cell($dim_array[4],8,$row_array['discount_amount'],'LRB',0,'C');
		$this->Cell($dim_array[5],8,$row_array['tax_amount'] . ' (' . round($row_array['tax_percent'], 2) . '%)','LRB',0,'C');
		$this->Cell($dim_array[6],8,$row_array['price'],'LRB',0,'C');
		$this->Ln();
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
		$grand_total = round($this->invoice_object->get_invoice_element($store_number, $invoice_id, 'grand_total'),2);
		$currency = $this->invoice_object->get_invoice_element($store_number, $invoice_id, 'currency');
		if($currency = '€')
		{
			$currency = EURO;
		}
		
		// Décalage
		$this->Ln(); 
		$this->Cell(120,10);
		
		$this->Cell(25,8,'Total HT',1); $this->Cell(35,8,$base_grand_total . ' ' . $currency,1,0,'C'); $this->Ln();
		$this->Cell(120,10); 
		$this->Cell(25,8,'Montant TVA',1); $this->Cell(35,8,$total_tax . ' ' . $currency,1,0,'C'); $this->Ln();
		$this->Cell(120,10);
		$this->Cell(25,8,'Total TTC',1); $this->SetFont('','B',10); $this->Cell(35,8,$grand_total . ' ' . $currency,1,0,'C'); $this->Ln();
	}
	
	function rib($store_number)
	{
		// On récupère les infos du magasin
		$store_bic_array = $this->tools_object->eoinvoice_get_store_bic($store_number);
		
		// On trie
		$bank_code = $store_bic_array[0];
		$register_code = $store_bic_array[1];
		$account_number = $store_bic_array[2];
		$rib_key = $store_bic_array[3];
		$iban = $store_bic_array[4];
		$bic = $store_bic_array[5];
		
		// On affiche
		$this->SetFont('','B',10);
		$this->Ln(); $this->Ln();
		$this->Cell(40,8,utf8_decode(__('Indentité bancaire', 'eoinvoice_trdom')));
		$this->SetFont('','',8); $this->Ln();
		$this->Cell(20,8,__('Code banque', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(20,8,__('Code guichet', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(20,8,utf8_decode(__('N° Compte', 'eoinvoice_trdom')),'LRT',0,'C');
		$this->Cell(20,8,utf8_decode(__('Clé RIB', 'eoinvoice_trdom')),'LRT',0,'C');
		$this->Cell(40,8,__('IBAN', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(25,8,__('BIC', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Ln();
		$this->Cell(20,8,$bank_code,1,0,'C');
		$this->Cell(20,8,$register_code,1,0,'C');
		$this->Cell(20,8,$account_number,1,0,'C');
		$this->Cell(20,8,$rib_key,1,0,'C');
		$this->Cell(40,8,$iban,1,0,'C');
		$this->Cell(25,8,$bic,1,0,'C');
	}
	
	function pre_footer($store_number)
	{
		// On récupère les infos du magasin
		$store_info_array = $this->tools_object->eoinvoice_get_store_info($store_number);
		// On trie
		$store_name = $store_info_array[1];
		$check_accept = $store_info_array[4];
		$society_type = $store_info_array[5];
		$society_capital = $store_info_array[6];
		
		$this->SetFont('','',10);
		$this->SetXY(10,-50);
		if($check_accept > 0)
		{
			$this->MultiCell(190,4,html_entity_decode(__('Adh&eacute;rent d\'un centre de gestion agr&eacute;&eacute;, acceptant &agrave; ce titre les r&egrave;glements par ch&egrave;que.', 'eoinvoice_trdom'), ENT_QUOTES),0,'L',FALSE);
			$this->Ln();
		}
		$this->MultiCell(190,4,html_entity_decode(__( 'Loi 83-629 du 12/07/83, art. 8 : "L\'autorisation administrative ne conf&egrave;re aucun caract&egrave;re officiel &agrave; l\'entreprise ou aux personnes qui en b&eacute;n&eacute;ficient. Elle n\'engage en aucune mani&egrave;re la responsabilit&eacute; des pouvoirs publics."', 'eoinvoice_trdom'), ENT_QUOTES),0,'L',FALSE);
		$this->Ln();
		$this->MultiCell(190,4,html_entity_decode($store_name . ', ' . $society_type . __(' au capital de ', 'eoinvoice_trdom') . $society_capital . EURO . '.', ENT_QUOTES),0,'L',FALSE);
	}
	
	//En-tête
	function Header()
	{
		$this->SetFont('Arial','B',15);
		//Décalage à droite
		$this->Cell(70);
		//Titre
		$this->Cell(30,10,'FACTURE',0,0,'L');
	}

	//Pied de page
	function Footer()
	{
		//Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		//Police Arial italique 8
		$this->SetFont('Arial','I',8);
		//Numéro de page
		$this->Cell(0,10,$this->PageNo() . '/{nb}',0,0,'C');
	}
}
?>
