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
$page_security = 'SA_MM_BREK_REQINQ';
$path_to_root = "../..";


include_once($path_to_root . "/includes/ui/items_cart.inc");
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/maintenance/includes/db/maintenance_item_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/maintenance/includes/break_down_request_ui.inc");
include_once($path_to_root . "/maintenance/includes/db/breakdown_request_db.inc");


$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();
	$outstanding_only = 0;
	page(_($help_context = "Breakdown Request Verification"), false, false, "", $js);

simple_page_mode(true);
?>
<style>
select{
	width:150px;
}
</style>
    <link rel="stylesheet" href="<?php echo $path_to_root . "/js/jquery-ui.css" ?>">

	<link rel="stylesheet" href="<?php echo $path_to_root . "/js/jquery.timepicker.css" ?>">
    <script src="<?php echo $path_to_root . "/js/jquery-1.10.2.js" ?>"></script>
	<script src="<?php echo $path_to_root . "/js/jquery-ui.js"?>"></script>
	<script src="<?php echo $path_to_root . "/js/jquery.timepicker.js"?>"></script>
</style>
<?php 
//----------------------------------------------------------------------------------
if($_GET["id"])
{
	$_POST['id']=$_GET["id"];
	$req_id=$_GET["id"];
	$id = $_GET["id"];
	
}
if(list_updated('item_req'))
{	
		$Ajax->activate('_page_body');
		
} 
if(($_GET['email']==1))
{
	display_notification_centered(_("Email has been sent successfully!"));
}


if (isset($_GET['AddedID'])) 
{
	
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_BREAKMAINTENTRY;
	display_notification_centered(_("Breakdown Request Verification has been added!") . " #$req_id");
	display_note(get_breakdown_maintenance_entry_view_str($trans_type, $trans_no, _("&View this Verification")));
		br();
	
	

	echo'<center>';
	echo "<a href='breakdown_email.php?id=".$trans_no."'>Email This Verification</a>";
	//<!-- <a href='/maintenance/inquiry/breakdown_email.php?id=".$_GET["id"]."'>Email This Verification</a></center> --> <?php 
	display_note(print_document_link($order_no, _("&Email This Enquiry"), true, $trans_type, false, "printlink", "", 1));
	display_footer_exit;
	
}

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}
//items


function handle_new_order()
{
	if (isset($_SESSION['adj_items']))
	{
		$_SESSION['adj_items']->clear_items();
		unset ($_SESSION['adj_items']);
	}

    $_SESSION['adj_items'] = new items_cart(ST_BREAKMAINTENTRY);
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

	if (!check_reference($_POST['ref'], ST_BREAKMAINTENTRY))
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



//-----------------------------------------------------------------------------------------------

function check_item_data()
{
	if (input_num('qty') == 0)
	{
		display_error(_("The quantity entered is invalid."));
		set_focus('qty');
		return false;
	}	
	//display_error($_POST["std_cost"]);
	/* if (!check_num('std_cost', 0))
	{
		display_error(_("The entered standard cost is negative or invalid."));
		set_focus('std_cost');
		return false;
	} */

   	return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
	$id = $_POST['LineNo'];
   	$_SESSION['adj_items']->update_cart_item($id, input_num('qty'), 
		input_num('std_cost'));
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
	
	add_to_order($_SESSION['adj_items'], $_POST['stock_id'], 
	input_num('qty'), input_num('std_cost'),input_num('available_qty'));
	
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



//items end

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['prob_desc']) == 0)
	{
		$input_error = 1;
		display_error(_("The Problem Description cannot be empty."));
		set_focus('prob_desc');
	}
		
	if ($input_error !=1) {
		
		$user_id=$_SESSION["wa_current_user"]->loginname;
		$user_sql="SELECT empl_id FROM ".TB_PREF."users WHERE user_id=".db_escape($user_id)."";
		
		$user_res=db_query($user_sql);
		$verify_empl_id=db_fetch($user_res);
		
	/* $trans_no = add_break_down_items($_SESSION['adj_items']->line_items, $_POST['AdjDate'],$_POST['ref'],$_POST['memo_'],$_POST["schedule_id"],ST_BREAKMAINTENTRY);
	new_doc_date($_POST['AdjDate']);
	$_SESSION['adj_items']->clear_items();
	unset($_SESSION['adj_items']); */

    	update_verification_details($_POST['id'], $_POST['prob_desc'], $_POST['item_req'],$_POST["mt_info"],$_POST["supplier_id"],$verify_empl_id['empl_id'],$_POST['mc_problem_type']);
		display_notification(_('Verification details has been added'));
		$Mode = 'RESET';
		meta_forward($_SERVER['PHP_SELF'], "AddedID=".$_POST['id']);
		//meta_forward($path_to_root.'/maintenance/inquiry/breakdown_request_inquiry.php');	
	}
	
}


start_form();
start_outer_table(TABLESTYLE2, "width='80%'");
if(isset($_GET['id']))
		{
			$id=$_GET['id'];
		}	
	if(isset($_POST['id']))
		{
			$id=$_POST['id'];
		}
$mc_info=get_breakdown_information($id);
global $wrarranty_type;
start_form();
if ($_SESSION['adj_items']->fixed_asset) {
	$items_title = _("Disposal Items");
	$button_title = _("Process Disposal");
} else {
	$items_title = _("Maintenance Items");
	$button_title = _("Process To Issues");
}

table_section(1);
	start_row();
	label_row(_("Machine Code:"),$mc_info["mac_code"],'class="label"');
	label_row(_("Machine Name:"),$mc_info["mac_eqp"],'class="label"');
	label_row(_("Machine Model:"),$mc_info["mac_model_no"],'class="label"');
	end_row();
	table_section(2);
	start_row();
	label_row(_("Machine Warranty Type:"),$wrarranty_type[$mc_info["warranty_type"]],'class="label"');
	label_row(_("Machine Warranty Expired:"),$mc_info["warranty_exp_date"],'class="label"');
	label_row(_("Operator:"),$mc_info["empl_firstname"],'class="label"');
	end_row();
	table_section(3);
	label_row(_("Supplier Name :"),$mc_info["supp_name"],'class="label"');
	label_row(_("Problem:"),$mc_info["ope_problem"],'class="label"');
	
end_outer_table();
br();
start_table(TABLESTYLE2);
//div_start('ram');
textarea_row(_("Problem Description"), 'prob_desc', null, 40, 5);
maintenance_mc_analysis_type_row(_("Problem Type Type:"),'mc_problem_type',null,false);
 //check_row(_("Materials Required:"), 'item_req',null,true);
	
$selected_id=$mc_info['id'];
hidden('id',$selected_id);
hidden('supplier_id',$mc_info["supplier_id"]);
end_outer_table(1, false);

if($_POST['item_req']==1)
	{
display_order_header_break_down($_SESSION['adj_items'],$id);
start_outer_table(TABLESTYLE, "width='70%'", 10);

display_maintenance_items_break_down($items_title, $_SESSION['adj_items']);
adjustment_options_controls_break_down();
end_outer_table(1, false);
	}
	

//submit_add_or_update_center($selected_id == '', '', 'both');
submit_center('ADD_ITEM', _("Submit"), true, '', 'default');
end_form();
//div_end();
end_page();
?>
<script>
 $( function() {
$("input[name='schedule_time']").timepicker();
  } );
  
</script>
