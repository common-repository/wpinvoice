// On règle le numéro de la ligne à ajouter
var chariot_input = 1;

// Ajoute une ligne produit au tableau de création de factures
function new_line()
{
	var line = 	'<tr>'+
				'<td><input type="text" value="" size=7 name="row_'+chariot_input+'_product_ref"></td>'+
				'<td><input type="text" value="" size=7 name="row_'+chariot_input+'_product_name"></td>'+
				'<td><input type="text" value="" size=12 name="row_'+chariot_input+'_product_desc"></td>'+
				'<td class="product_weight"><input type="text" size=2 name="row_'+chariot_input+'_product_weight"></td>'+
				'<td class="product_qty"><input type="text" value="" size=2 name="row_'+chariot_input+'_product_qty"></td>'+
				'<td><input type="text" value="" size=2 name="row_'+chariot_input+'_product_tax_percent"></td>'+
				'<td><input type="text" value="" size=2 name="row_'+chariot_input+'_product_discount"></td>'+
				'<td><input type="text" value="" size=4 name="row_'+chariot_input+'_product_base_price"></td>'+
				'</tr>';
	
	chariot_input++;
	jQuery("tr:last").before(line);
}

// Appel jQuery
jQuery(document).ready(function(){
	// Ajouter une ligne à la facture
	jQuery("#add_line").click(function(){
		new_line();
	});
	// Exporter en PDF
	jQuery(".export_pdf").click(function(){
		var item_id = this.id; // On récupère l'id de l'élément
		var invoice_id = item_id.substring(item_id.indexOf('_invoice')+8,item_id.length); // On en prend le numéro de facture
		var store_number = item_id.substring(5,item_id.indexOf('_invoice')); // Et le numéro du magasin
		jQuery("#export_output").load(eoinvoice_home_url + "include/ajax.php", {"action":"export", "export_type":"pdf", "invoice_id":invoice_id, "store_number":store_number});
	});
	// Exporter en ODT
	jQuery(".export_odt").click(function(){
		var item_id = this.id; // On récupère l'id de l'élément
		var invoice_id = item_id.charAt(14); // On en prend le numéro de facture
		var store_number = item_id.substring(5,item_id.indexOf('_invoice')); // Et le numéro du magasin
		jQuery("#export_output").load(eoinvoice_home_url + "include/ajax.php", {"action":"export", "export_type":"odt", "invoice_id":invoice_id, "store_number":store_number});
	});
});
