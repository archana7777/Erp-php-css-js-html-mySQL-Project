<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_SALESPROFORMAINVOICE';
$path_to_root = "..";

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

include_once($path_to_root . "/sales/includes/db/sales_proforma_invoice_db.inc");
//include($path_to_root . "/modules/ExtendedHRM/includes/ui/employee.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Proforma Invoice"), false, false, "", $js);

simple_page_mode(true);
//---------------------------------------------------------------------------------------
if (isset($_GET['OrderNumber']))
{
	$sales_order_no = $_GET['OrderNumber'];
}

elseif(isset($_POST['OrderNumber']))
{
	$sales_order_no = $_POST['OrderNumber'];
}


if (isset($_GET['id']))
{
	$selected_id = $_GET['id'];
}

elseif(isset($_POST['id']))
{
	$selected_id = $_POST['id'];
}

if (list_updated('proforma_status')) {
	
	
	$Ajax->activate('cust_payment_reference');
	$Ajax->activate('_page_body');
}
//---------------------------------------------------------------------------------------

if (!isset($_POST['date_']))
{
	$_POST['date_'] = new_doc_date();
	if (!is_date_in_fiscalyear($_POST['date_']))
		$_POST['date_'] = end_fiscalyear();
}

function can_process()
{
			
	//if (!isset($selected_id))
	if (!isset($_POST['UPDATE_ITEM']))
	{
    	if (!check_reference($_POST['reference'], ST_PROFORMAINVOICE))
    	{
			
			set_focus('reference');
    		return false;
    	}
	}
	
	
	$sales_order_date = get_sales_order_date($_POST['sales_order_no']);
	$sales_order_date1 = sql2date($sales_order_date);
	
	/*
	if (date1_greater_date2($_POST['sales_order_date1'], $_POST['date_']))
	{
		display_error( _("BEGIN date bigger than END date."));
		set_focus('date_');
		return false;
	}*/
	
	if (strlen($_POST['kind_atten']) == 0) 
	{
		
		display_error( _('The kind atten must be entered.'));
		set_focus('kind_atten');
		return false;
	} 
	
	if (strlen($_POST['subject']) == 0) 
	{
		
		display_error( _('The subject must be entered.'));
		set_focus('subject');
		return false;
	} 
	
	if (strlen($_POST['product_type']) == 0) 
	{
		
		display_error( _('The product type must be entered.'));
		set_focus('product_type');
		return false;
	} 
	
	if (!check_num('invoice_amount', 0))
	{
		$input_error = 1;
		display_error( _("The price entered must be numeric."));
		set_focus('price');
		return false;
	}
	
	
	
	if (!check_num('freight_charges', 0))
	{
		
		display_error( _("The freight charges entered must be numeric."));
		set_focus('freight_charges');
		return false;
	}
	
	
	if (!check_num('gst_percent', 0))
	{
		
		display_error( _("The gst percentage entered must be numeric."));
		set_focus('gst_percent');
		return false;
	}
	
	
	if (strlen($_POST['payment']) == 0) 
	{
		
		display_error( _('The payment must be entered.'));
		set_focus('payment');
		return false;
	} 
	
	
	if (strlen($_POST['price_basis']) == 0) 
	{
		
		display_error( _('The price basis must be entered.'));
		set_focus('price_basis');
		return false;
	} 
	
	if (strlen($_POST['price_basis']) == 0) 
	{
		
		display_error( _('The price basis must be entered.'));
		set_focus('price_basis');
		return false;
	} 
	
	
	

	return true;
}

//-------------------------------------------------------------------------------------

if (isset($_POST['ADD_ITEM']) && can_process())
{
	add_sales_proforma_invoice($_POST['reference'],ST_PROFORMAINVOICE,$_POST['sales_order_no'],$_POST['kind_atten'],$_POST['subject'],$_POST['date_'],$_POST['product_type'],input_num('invoice_amount'),input_num('freight_charges'),input_num('gst_percent'),$_POST['payment'],$_POST['price_basis'],$_POST['comment'],$_POST['proforma_status'],$_POST['cust_payment_reference']);
	
	display_notification(_('Sales proforma invoice has been added !'));
	
	//$Mode = 'RESET';
	$path=$path_to_root."/sales/sales_proforma_invoice.php?OrderNumber=" .$_POST['sales_order_no'];
	meta_forward($path);
	
	//meta_forward($path_to_root.'/sales/inquiry/sales_orders_view.php?type=30');	
}

if (isset($_POST['UPDATE_ITEM']) && can_process())
{	

	update_sales_proforma_invoice($_POST["selected_id"],$_POST['reference'],$_POST['kind_atten'],$_POST['subject'],$_POST['date_'],$_POST['product_type'],input_num('invoice_amount'),input_num('freight_charges'),input_num('gst_percent'),$_POST['payment'],$_POST['price_basis'],$_POST['comment'],$_POST['proforma_status'],$_POST['cust_payment_reference']);
	display_notification(_('Sales proforma invoice has been updated!'));

	$path=$path_to_root."/sales/sales_proforma_invoice.php?OrderNumber=" .$_POST['sales_order_no'];
	meta_forward($path);
		//$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	
	unset($_POST);
	
}


if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'location_category'
	

		delete_sales_proforma_invoice($selected_id);
		display_notification(_('Selected sales proforma invoice has been deleted'));

	$Mode = 'RESET';
}

start_form();
start_table(TABLESTYLE2);
echo "<h1>Sales Order Details<h1>";
end_table();


if($selected_id !==-1){
$sales_order = get_sql_for_sales_orders_number_details($_POST['sales_order_no']);
}
else{
	$sales_order = get_sql_for_sales_orders_number_details($sales_order_no);
}

$th = array(_("Order"), _("Ref"),_("Customer"),_("Branch"),_("Cust Order Ref"),_("Order Date"),_("Required By"),_("Delivery To"),_("Order Total"),_("Currency"));
start_table(TABLESTYLE);
//inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter
while ($sales = db_fetch($sales_order)) 
{

	alt_table_row_color($k);
	label_cell($sales["order_no"]);
	label_cell($sales["reference"]);
	label_cell($sales["name"]);
	label_cell($sales["br_name"]);
	label_cell($sales["customer_ref"]);
	label_cell(sql2date($sales["ord_date"]));
	label_cell(sql2date($sales["delivery_date"]));
	label_cell($sales["deliver_to"]);
	amount_cell($sales["OrderValue"]);
	label_cell($sales["curr_code"]);
 	
	end_row();
}
	//END WHILE LIST LOOP
//inactive_control_row($th);
end_table();  


br();
br();

start_table(TABLESTYLE2);
echo "<h1>Proforma Invoice Details<h1>";
end_table();
if($selected_id !==-1){
$result = get_sales_proforma_invoice_details($_POST['sales_order_no']);
}
else{
	$result = get_sales_proforma_invoice_details($sales_order_no);
}

$th = array(_("Reference"), _("Kind Atten"),_("Date "),_("Product Type"),_("Invoice Amount"),_("Freight Charges"),_("GST(%)"), _("Status"),"", "");
start_table(TABLESTYLE);
//inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter
while ($myrow = db_fetch($result)) 
{

	alt_table_row_color($k);
	label_cell($myrow["reference"]);
	label_cell($myrow["crm_person_name"]);
	label_cell(sql2date($myrow["date_"]));
	label_cell($myrow["product_type"]);
	label_cell(number_format($myrow["invoice_amount"],2));
	label_cell(number_format($myrow["freight_charges"],2));
	label_cell(number_format($myrow["gst_percent"]));
	label_cell($sales_proforma_invoice_status[$myrow["proforma_status"]]);
	
 	edit_button_cell("Edit".$myrow["id"], _("Edit"));
 	delete_button_cell("Delete".$myrow["id"], _("Delete"));
	
	//label_cell(print_document_link($myrow["id"], _("Print Proforma Invoice"), true, 1108,ICON_PRINT));
	
	
	
	end_row();
}
	//END WHILE LIST LOOP
//inactive_control_row($th);
end_table(1); 

br();
br();

start_table(TABLESTYLE2);
if ($selected_id != -1)
{
	
	$myrow = get_sales_proforma_invoice($selected_id);
	
	$_POST['reference'] = $myrow["reference"];
    $_POST['kind_atten'] = $myrow["kind_atten"];
	$_POST['subject'] = $myrow["subject"];
	$_POST['product_type'] = $myrow["product_type"];
	$_POST['payment'] = $myrow["payment"];
	$_POST['price_basis'] = $myrow["price_basis"];
	$_POST['comment'] = $myrow["comment"];
	$_POST['date_'] = sql2date($myrow["date_"]);
	$_POST['invoice_amount'] = price_format($myrow["invoice_amount"]);
	$_POST['freight_charges'] = price_format($myrow["freight_charges"]);
	$_POST['gst_percent'] = price_format($myrow["gst_percent"]);
	$_POST['remarks']=$myrow["remarks"];
	if(isset($myrow["proforma_status"])){
	$_POST['proforma_status'] = $myrow["proforma_status"];
	}
	else{
	$_POST['proforma_status'] = $_POST['proforma_status'];
	}
	$_POST['cust_payment_reference'] = $myrow["cust_payment_reference"];
	
	hidden('reference', $_POST['reference']);
	label_row(_("Reference:"), $_POST['reference']);
}
else
{
	
	ref_row(_("Proforma Invoice Reference :"), 'reference', '', $Refs->get_next(ST_PROFORMAINVOICE, null, get_post('date_')), false, ST_PROFORMAINVOICE);
	
}

if($selected_id !==-1){

$sales_order_branch = get_sales_order_customer_branch($_POST['sales_order_no']);
}
else{
	$sales_order_branch = get_sales_order_customer_branch($sales_order_no);
}
	
   // text_row(_("Kind Atten:"), 'kind_atten', null, 25, 50);
   
   customer_branches_contacts_list_row(_("Kind Atten:"),
	  	   'kind_atten', null, false,false,$sales_order_branch);

    textarea_row(_("Subject:"), 'subject',null);

    date_row(_("Date:") , 'date_', '', true);
	
	text_row(_("Product Type:"), 'product_type', null, 25, 50);
	
	small_amount_row(_("Proforma Invoice Amount:"), 'invoice_amount', null);
	
	small_amount_row(_("Freight Charges:"), 'freight_charges', null);
	
	small_amount_row(_("GST (%):"), 'gst_percent', null);
	
	textarea_row(_("Payment:"), 'payment',null);
	
	textarea_row(_("Price Basis:"), 'price_basis',null);
	
	textarea_row(_("Comments:"), 'comment',null);
	
	
	sales_proforma_invoice_status_row( _("Status:"), 'proforma_status', $_POST['proforma_status'],true);
	
	if($_POST['proforma_status']==2){
	text_row(_("Customer Payment Reference:"), 'cust_payment_reference',null,25,50);
	}
	
	hidden('selected_id',  $selected_id);
	hidden('sales_order_no', $sales_order_no);
	

end_table(1);
/*
if (isset($selected_id))
{
	echo "<table align=center><tr>";
	submit_cells('UPDATE_ITEM', _("Update"), '', _('Save changes to Study Allotment'), 'default');
	echo "</tr></table>";
}
else
{
	submit_center('ADD_ITEM', _("Submit"), true, '', 'default');
	br();
}
*/
submit_add_or_update_center($selected_id == -1, '', 'both');
end_form();
end_page();
?>
