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
$page_security = 'SA_MANUF_LAYERWINDER';
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
page(_($help_context = "Layer Winder Entry"), false, false, "", $js);

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

if (isset($_POST['ProcessLayerWinder']))
{
	if (!can_process())
		unset($_POST['ProcessLayerWinder']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelLayerWinder']))
{
	//$selected_id = -1;
	$Ajax->activate('_page_body');
}
if (list_updated('core_wire') || $_POST['outer1'] || $_POST['outer2'] || $_POST['outer3'] || $_POST['outer4'] || $_POST['outer5'] || $_POST['outer6'])
{
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
	
	if (strlen($_POST['start_time']) == 0) 
	{
		display_error( _("The Start Time must be entered."));
		set_focus('start_time');
		return false;
	}
	if (strlen($_POST['stop_time']) == 0) 
	{
		display_error( _("The Stop Time must be entered."));
		set_focus('stop_time');
		return false;
	}
	
	if (strlen($_POST['layer_winder_no']) == 0) 
	{
		display_error( _("The Layer Winder No  must be entered."));
		set_focus('layer_winder_no');
		return false;
	}
	
	if (strlen($_POST['winder_coil_no']) == 0) 
	{
		display_error( _("The Coil No  must be entered."));
		set_focus('winder_coil_no');
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
	
	if(input_num('remaining_length')<input_num('length_in_mtr'))
	{
		display_error( _("The Length in mtr couldn't be exceed remaining length."));
		set_focus('length_in_mtr');
		return false;
	}
	
	
	if(winder_coil_no_exist($_POST['winder_coil_no']))
	{
		display_error( _("The Entered Coil No is already Existed."));
		set_focus('winder_coil_no');
		return false;
		
	}
		
	if($_POST['set_no']==-1)
	{
		display_error( _("Set Number must be selected."));
		set_focus('set_no');
		return false;
	}
	         
	return true;
}



if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmLayerWinder']) && can_process())
{
	
		
		add_laywer_winder_details($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['layer_winder_no'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['set_no'],$_POST['take_up_no'],$_POST['size_id'],input_num('length_in_mtr'),input_num('qty'),trim($_POST['winder_coil_no']),$_POST['start_time'],$_POST['stop_time'],$_POST['coil_status'],$_POST['remarks']);
				
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

	display_layer_winder_details($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	
	
	if (list_updated('set_no')|| $_POST['set_no']!=-1)
{
	$set_details=get_set_no_details($_POST['set_no'],$selected_id);
	
	$take_up_no=$set_details['take_up_no'];
	$size=$set_details['size'];
	$size_id=$set_details['size_id'];
	$remaining_length=$set_details['length_in_mtr']-$set_details['used_length'];
	$Ajax->activate('_page_body');
}



	start_outer_table(TABLESTYLE2);

	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	text_row(_("Layer Winder No.:"), "layer_winder_no",null,20,50);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	set_no_list_row(_("SET No:"), 'set_no', null, true,true,$selected_id);
	label_row(_("Take Up No:"),$take_up_no);
	hidden('take_up_no',$take_up_no);
	label_row(_("Size:"),$size);
	hidden('size_id',$size_id);
	label_row(_("Remaining Length:"),$remaining_length);
	hidden('remaining_length',$remaining_length);
	
	text_row(_("Start Time:"), "start_time",null,20,50);
    text_row(_("End Time:"), "stop_time",null,20,50);	
	table_section(2);
	table_section_title(_("Coiling Details of a Set"));
	set_no_text_row(_("Coil No.:"), "winder_coil_no",null, null,null,true);
	qty_row(_("Length in Meter:"), 'length_in_mtr', null, null,null,1);	
	qty_row(_("Calculated Weight in Kgs:"), 'qty', null, null,null,2);
	layer_winder_coil_status_list_row(_("Coil Status:"), 'coil_status', null, true);
	textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	
	end_outer_table(1);
	if (!isset($_POST['ProcessLayerWinder']))
	submit_center('ProcessLayerWinder', _("Add Layer Winder Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmLayerWinder', _("Proceed"), '', true);
    		submit_center_last('CancelLayerWinder', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

