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
$page_security = 'SA_MANUF_OILPACKING';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Oiling and Packing Entry"), false, false, "", $js);

//---------------------------------------------------------------------------------------

if (isset($_GET['trans_no']))
{
	$selected_id = $_GET['trans_no'];
}
elseif(isset($_POST['selected_id']))
{
	$selected_id = $_POST['selected_id'];
}

hidden('selected_id',$selected_id);
br();


if (isset($_GET['AddedID']))
{
	display_notification_centered(_("The coil has been Drawn."));
}

if (isset($_POST['ProcessOilingPacking']))
{
	if (!can_process())
		unset($_POST['ProcessOilingPacking']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelOilingPacking']))
{
	//$selected_id = -1;
	$Ajax->activate('_page_body');
}


function can_process()
{
	global $selected_id, $SysPrefs;
	

	if (!is_date($_POST['date_']))
	{
		display_error( _("The date entered is in an invalid format."));
		set_focus('date_');
		return false;
	}
	
	if (strlen($_POST['oil_tank_no']) == 0) 
	{
		display_error( _("The Oil Tank No  must be entered."));
		set_focus('oil_tank_no');
		return false;
	}
	
		
	if (input_num('gross_qty') == 0) 
	{
		display_error( _("The Gross Weight must be entered."));
		set_focus('gross_qty');
		return false;
	}
	
	if (input_num('net_qty') == 0) 
	{
		display_error( _("The Net Weight must be entered."));
		set_focus('net_qty');
		return false;
	}
	
	
	if($_POST['coil_no']==-1)
	{
		display_error( _("Coil No must be selected."));
		set_focus('coil_no');
		return false;
	}
	         
	return true;
}



if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmOilingPacking']) && can_process())
{

		add_oiling_packing_details($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['oil_tank_no'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['coil_no'],$_POST['size_id'],$_POST['product_class'],input_num('net_qty'),input_num('gross_qty'),$_POST['oiling_status'],$_POST['coil_surface_condition_status'],$_POST['final_packing_status'],$_POST['remarks']);
	
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

	display_oiling_packing_details($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	
	
	if (list_updated('coil_no')|| $_POST['coil_no']!=-1)
{
	$coil_details=get_drawing_coil_details($_POST['coil_no'],$selected_id);
	
	$size=$coil_details['size'];
	$size_id=$coil_details['size_id'];
	$product_class=$coil_details['product_class'];
	$Ajax->activate('_page_body');
}

	start_outer_table(TABLESTYLE2);
	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	text_row(_("Oil Tank No.:"), "oil_tank_no",null,20,50);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	drawing_coil_list_row(_("Coil No:"), 'coil_no', null, true,true,$selected_id);
	label_row(_("Size:"),$size);
	label_row(_("Class:"),$product_class);
	hidden('size_id',$size_id);
	hidden('product_class',$product_class);
	oiling_status_list_row(_("Oiling Status:"), 'oiling_status', null, true);
	coil_surface_condition_status_list_row(_("Coil Surface Condition:"), 'coil_surface_condition_status', null, true);
	final_packing_status_list_row(_("Final Packing Status:"), 'final_packing_status', null, true);
	qty_row(_("Net Weight of Coil in Kgs:"), 'net_qty', null, null,null,1);	
	qty_row(_("Gross Weight in Kgs:"), 'gross_qty', null, null,null,2);
	
	textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	
	end_outer_table(1);
	if (!isset($_POST['ProcessOilingPacking']))
	submit_center('ProcessOilingPacking', _("Add Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmOilingPacking', _("Proceed"), '', true);
    		submit_center_last('CancelOilingPacking', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

