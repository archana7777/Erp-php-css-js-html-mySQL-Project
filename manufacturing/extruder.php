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
$page_security = 'SA_MANUF_EXTRUDER';
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
$js .= get_js_time_picker();
page(_($help_context = "Extruder Entry"), false, false, "", $js);

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

if (isset($_POST['ProcessExtruder']))
{
	if (!can_process())
		unset($_POST['ProcessExtruder']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelExtruder']))
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
	
	if (strlen($_POST['in_time']) == 0) 
	{
		display_error( _("The Coil In Time must be entered."));
		set_focus('in_time');
		return false;
	}
	if (strlen($_POST['out_time']) == 0) 
	{
		display_error( _("The Coil Out Time must be entered."));
		set_focus('out_time');
		return false;
	}
	
	if (strlen($_POST['extruder_machine_no']) == 0) 
	{
		display_error( _("The Extruder Machine No  must be entered."));
		set_focus('extruder_machine_no');
		return false;
	}
	
	if (strlen($_POST['extruder_coil_no']) == 0) 
	{
		display_error( _("The Extruder Coil No  must be entered."));
		set_focus('extruder_coil_no');
		return false;
	}
	
	if (input_num('length_in_mtr') == 0) 
	{
		display_error( _("The Length in meter  must be entered."));
		set_focus('length_in_mtr');
		return false;
	}
	
	if (input_num('qty') == 0) 
	{
		display_error( _("The Calculated Qty must be entered."));
		set_focus('qty');
		return false;
	}
	
	if (input_num('steel_qty') == 0) 
	{
		display_error( _("The Steel Qty must be entered."));
		set_focus('steel_qty');
		return false;
	}
	if (input_num('grease_qty') == 0) 
	{
		display_error( _("The Grease Qty must be entered."));
		set_focus('grease_qty');
		return false;
	}
	if (input_num('hdpe_qty') == 0) 
	{
		display_error( _("The HDPE Qty must be entered."));
		set_focus('hdpe_qty');
		return false;
	}
	
	if(input_num('rm_length')<input_num('length_in_mtr'))
	{
		display_error( _("The Length in mtr couldn't be exceed Raw Material length."));
		set_focus('length_in_mtr');
		return false;
	}
	
	
if(extruder_coil_no_exist($_POST['extruder_coil_no']))
	{
		display_error( _("The Entered Coil No is already Existed."));
		set_focus('extruder_coil_no');
		return false;
		
	}
		
	if($_POST['rm_coil_no']==-1)
	{
		display_error( _("Raw Material Coil no must be selected."));
		set_focus('rm_coil_no');
		return false;
	}
	         
	return true;
}



if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmExtruder']) && can_process())
{
			add_extruder_details($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['extruder_machine_no'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['rm_coil_no'],$_POST['rm_length'],$_POST['rm_size_id'],$_POST['rm_qty'],$_POST['in_time'],$_POST['out_time'],trim($_POST['extruder_coil_no']),$_POST['take_up_no'],$_POST['size_id'],input_num('length_in_mtr'),input_num('qty'),input_num('steel_qty'),input_num('grease_qty'),input_num('hdpe_qty'),$_POST['remarks']);
				
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

    display_extruder_details($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	
	
	if (list_updated('rm_coil_no')|| $_POST['rm_coil_no']!=-1)
{
	$layer_winder_coil_details=get_layer_winder_coil_details($_POST['rm_coil_no'],$selected_id);
	
	$weight=$layer_winder_coil_details['qty'];
	$size=$layer_winder_coil_details['size'];
	$size_id=$layer_winder_coil_details['size_id'];
	$remaining_length=$layer_winder_coil_details['length_in_mtr']-$layer_winder_coil_details['used_length'];
	$Ajax->activate('_page_body');
}



	start_outer_table(TABLESTYLE2);

	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	text_row(_("Extruder Machine No.:"), "extruder_machine_no",null,20,50);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	layer_winder_coil_list_row(_("Coil No:"), 'rm_coil_no', null, true,true,$selected_id);
	label_row(_("Size:"),$size);
	hidden('rm_size_id',$size_id);
	label_row(_("Length:"),$remaining_length);
	hidden('rm_length',$remaining_length);
	label_row(_("Weight:"),$weight);
	hidden('rm_qty',$weight);

	text_row(_("Coil In Time:"), "in_time",null,20,50);
    text_row(_("Coil Out Time:"), "out_time",null,20,50);	
	table_section(2);
	table_section_title(_("Final Coil Details"));
	set_no_text_row(_("Coil No.:"), "extruder_coil_no",null, null,null,true);
	text_row(_("Take Up No:"), "take_up_no",null,20,50);
	stock_size_list_row(_("Size:"),'size_id',null);
	qty_row(_("Length in Meter:"), 'length_in_mtr', null, null,null,1);	
	qty_row(_("Calculated Weight in Kgs:"), 'qty', null, null,null,2);
	qty_row(_("Steel Weight in Kgs:"), 'steel_qty', null, null,null,2);
	qty_row(_("Grease Weight in Kgs:"), 'grease_qty', null, null,null,2);
	qty_row(_("HDPE Weight in Kgs:"), 'hdpe_qty', null, null,null,2);
	
	textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	
	end_outer_table(1);
	if (!isset($_POST['ProcessExtruder']))
	submit_center('ProcessExtruder', _("Add Extruder Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmExtruder', _("Proceed"), '', true);
    		submit_center_last('CancelExtruder', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

