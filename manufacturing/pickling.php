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
$page_security = 'SA_MANUF_PICKLING';
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
page(_($help_context = "Pickling Entry"), false, false, "", $js);

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
	display_notification_centered(_("The coil has been Pickled."));
}

if (isset($_POST['ProcessPickling']))
{
	if (!can_process())
		unset($_POST['ProcessPickling']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelPickling']))
{
	$selected_id = -1;
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
	if (strlen($_POST['at_in']) == 0) 
	{
		display_error( _("Acid Tank Coil in Time must be entered."));
		set_focus('at_in');
		return false;
	}
	if (strlen($_POST['at_out']) == 0) 
	{
		display_error( _("Acid Tank Coil out Time must be entered."));
		set_focus('at_out');
		return false;
	}
	if (strlen($_POST['pt_in']) == 0) 
	{
		display_error( _("Phosphate Tank Coil in Time must be entered."));
		set_focus('pt_in');
		return false;
	}
	if (strlen($_POST['pt_out']) == 0) 
	{
		display_error( _("Phosphate Tank Coil out Time must be entered."));
		set_focus('pt_out');
		return false;
	}
	if (strlen($_POST['bt_in']) == 0) 
	{
		display_error( _("Borax Tank Coil in Time must be entered."));
		set_focus('bt_in');
		return false;
	}
	if (strlen($_POST['bt_out']) == 0) 
	{
		display_error( _("Borax Tank Coil in Time must be entered."));
		set_focus('bt_out');
		return false;
	}
	
	if(!$_POST['stock_id'])
	{
		display_error( _("Raw Material must be selected."));
		set_focus('stock_id');
		return false;
	}
	
	if($_POST['coil_no']==-1)
	{
		display_error( _("Coil Number must be selected."));
		set_focus('coil_no');
		return false;
	}
      
	return true;
}


if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmPickling']) && can_process())
{
	
		add_pickling($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['stock_id'],$_POST['coil_no'],$_POST['heat_no'],$_POST['qty'],$_POST['at_in'],$_POST['at_out'],$_POST['pt_in'],$_POST['pt_out'],$_POST['bt_in'],$_POST['bt_out'],$_POST['remarks'],$_POST['loc_code']);
		
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

	display_pickled_items_summary($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	

$heat_no=$grade=$size=$weight="";	
if (list_updated('stock_id') || list_updated('loc_code'))
{
	$Ajax->activate('_page_body');
}
if (list_updated('coil_no')|| $_POST['coil_no']!=-1)
{
	$coil_details=get_coil_details($_POST['stock_id'],$_POST['coil_no']);
	
	$heat_no=$coil_details['heat_no'];
	$grade=$coil_details['grade'];
	$size=$coil_details['size'];
	$weight=$coil_details['qty'];	
	$Ajax->activate('_page_body');
}


	start_outer_table(TABLESTYLE2);

	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	locations_list_row(_("Location to Draw From:"), 'loc_code', null,false,true);
    production_rm_items_list_row(_("Raw Material:"), 'stock_id', null, true,true);
	rm_coils_list_row(_("Coil No:"), 'coil_no', null, true,true,$_POST['stock_id'],$_POST['loc_code']);
	label_row(_("Heat No:"), $heat_no);
	hidden('heat_no',$heat_no);
	label_row(_("Size in (mm):"),$size );
	label_row(_("Grade:"), $grade);
	label_row(_("Weight:"), $weight);
	hidden('qty',$weight);
	table_section(2);
	text_row(_("Acid Tank(Coil in Time):"), "at_in",null,20,50);
	text_row(_("Acid Tank(Coil out Time):"), "at_out",null,20,50);
	text_row(_("Phosphate Tank (Coil in Time):"), "pt_in",null,20,50);
	text_row(_("Phosphate Tank (Coil out Time):"), "pt_out",null,20,50);
	text_row(_("Borax Tank(Coil in Time):"), "bt_in",null,20,50);
	text_row(_("Borax Tank (Coil out Time):"), "bt_out",null,20,50);
	textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	end_outer_table(1);
	if (!isset($_POST['ProcessPickling']))
	submit_center('ProcessPickling', _("Add Pickling Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmPickling', _("Proceed"), '', true);
    		submit_center_last('CancelPickling', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

