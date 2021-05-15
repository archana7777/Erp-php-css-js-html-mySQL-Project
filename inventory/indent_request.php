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
$page_security = 'SA_INDENTREQUEST';
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/items_cart.inc");

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/indent_request_ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['NewTransfer'])) {
	
		$_SESSION['page_title'] = _($help_context = "Material Requisition  Entry");
	}

page($_SESSION['page_title'], false, false, "", $js);
if(list_updated('StockLocation')){
	
	$Ajax->activate('_page_body');
}
check_db_has_locations(_("There are no locations defined in the system."));
//-----------------------------------------------------------------------------------------------
//display_error(hi);die;
if (isset($_GET['AddedID'])) 
{
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_INDENTREQUEST;

display_notification_centered(_("Indent Request has been processed"));
	display_note(get_trans_view_str($trans_type, $trans_no, _("&View this Indent Request")));

  $itm = db_fetch(get_stock_transfer_items($_GET['AddedID']));
/*
  if (is_fixed_asset($itm['mb_flag']))
	  hyperlink_params($_SERVER['PHP_SELF'], _("Enter &Another Fixed Assets Transfer"), "NewTransfer=1&FixedAsset=1");
  else */
	  hyperlink_params($_SERVER['PHP_SELF'], _("Enter &Another Indent Request"), "NewTransfer=1");

	display_footer_exit();
}
//--------------------------------------------------------------------------------------------------
$sess=$_SESSION["wa_current_user"]->loginname;

$emp_code=get_emp_code($sess);
//display_error(hie);
//display_error('hie');die;
$emp_id=get_emp_id($emp_code);
//display_error(hie);die;
$dept_id=get_dept_id($emp_code);

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}
//-----------------------------------------------------------------------------------------------

function handle_new_order()
{
	if (isset($_SESSION['adj_items']))
	{
		$_SESSION['adj_items']->clear_items();
		unset ($_SESSION['adj_items']);
	}

    $_SESSION['adj_items'] = new items_cart(ST_INDENTREQUEST);
    $_SESSION['adj_items']->fixed_asset = isset($_GET['FixedAsset']);
	$_POST['AdjDate'] = new_doc_date();
	if (!is_date_in_fiscalyear($_POST['AdjDate']))
		$_POST['AdjDate'] = end_fiscalyear();
	$_SESSION['adj_items']->tran_date = $_POST['AdjDate'];	
}

//-----------------------------------------------------------------------------------------------

function can_process()
{
	global $SysPrefs;

	$adj = &$_SESSION['adj_items'];

	if (count($adj->line_items) == 0)	{
		display_error(_("You must enter at least one non empty item line."));
		set_focus('stock_id');
		return false;
	}

	if (!check_reference($_POST['ref'], ST_INDENTREQUEST))
	{
		set_focus('ref');
		return false;
	}

	if (!is_date($_POST['AdjDate'])) 
	{
		display_error(_("The entered date for the adjustment is invalid."));
		set_focus('AdjDate');
		return false;
	} 
	elseif (!is_date_in_fiscalyear($_POST['AdjDate'])) 
	{
		display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('AdjDate');
		return false;
	}
	elseif (!$SysPrefs->allow_negative_stock())
	{
		$low_stock = $adj->check_qoh($_POST['StockLocation'], $_POST['AdjDate']);

		if ($low_stock)
		{
    		display_error(_("The adjustment cannot be processed because it would cause negative inventory balance for marked items as of document date or later."));
			unset($_POST['Process']);
			return false;
		}
	}
	return true;
}

//-------------------------------------------------------------------------------

if (isset($_POST['Process']) && can_process()){

  $fixed_asset = $_SESSION['adj_items']->fixed_asset; 
	
$sess=$_SESSION["wa_current_user"]->loginname;
$emp_code=get_emp_code($sess);
$emp_id=get_emp_id($emp_code);
$dept_id=get_dept_id($emp_code);

$trans_no = add_indent_adjustment($_SESSION['adj_items']->line_items,$_POST['StockLocation'],$_POST['AdjDate'],$_POST['ref'], $_POST['memo_'],$emp_id,$dept_id);
	new_doc_date($_POST['AdjDate']);
	$_SESSION['adj_items']->clear_items();
	unset($_SESSION['adj_items']);

  if ($fixed_asset)
   	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no&FixedAsset=1");
  else
   	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");

} /*end of process credit note */

//-----------------------------------------------------------------------------------------------

function check_item_data()
{
		
	if (input_num('qty') == 0)
	{
		display_error(_("The quantity entered is invalid."));
		set_focus('qty');
		return false;
	}
	$av_qty=check_quantity($_POST['stock_id']);
	if($_POST['available_qty']<input_num('qty'))
	{
	/* 	display_error(_("The quantity entered is not available in stock .available qty---.".$_POST['available_qty']));
			set_focus('qty'); */
			//return false;
	}
	 /* if($_POST['required_date']<Today())
	{
		display_error("Rquired date must be today or above the today date!");
		set_focus('required_date');
			return false;
	} */


   	return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
	
	$id = $_POST['LineNo'];
   	$_SESSION['adj_items']->update_cart_item($id, input_num('qty'),'','',$_POST['required_date'],$_POST['supplier_id']);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id)
{
	$_SESSION['adj_items']->remove_from_cart($id);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item()
{

	add_to_indent($_SESSION['adj_items'], $_POST['stock_id'], 
	input_num('qty'),input_num('available_qty'),$_POST['required_date'],$_POST['supplier_id']);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------
$id = find_submit('Delete');
if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['AddItem']) && check_item_data())
	handle_new_item();

if (isset($_POST['UpdateItem']) && check_item_data())
	handle_update_item();

if (isset($_POST['CancelItemChanges'])) {
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------

if (isset($_GET['NewAdjustment']) || !isset($_SESSION['adj_items']))
{

	if (isset($_GET['FixedAsset']))
		check_db_has_disposable_fixed_assets(_("There are no fixed assets defined in the system."));
	else
		check_db_has_costable_items(_("There are no inventory items defined in the system which can be adjusted (Purchased or Manufactured)."));

	handle_new_order();
}

//-----------------------------------------------------------------------------------------------
start_form();

if ($_SESSION['adj_items']->fixed_asset) {
	$items_title = _("Disposal Items");
	$button_title = _("Process Disposal");
} else {
	$items_title = _("Indent Request");
	$button_title = _("Process Indent");
}
 //display_error(hie);die;
indent_display_order_header($_SESSION['adj_items']);
//display_error(hie);die;
start_outer_table(TABLESTYLE, "width='70%'", 10);

display_indent_items($items_title, $_SESSION['adj_items']);
indent_options_controls();

end_outer_table(1, false);

submit_center_first('Update', _("Update"), '', null);
submit_center_last('Process', $button_title, '', 'default');

end_form();
end_page();

