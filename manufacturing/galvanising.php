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
$page_security = 'SA_MANUF_GALVANISING';
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
page(_($help_context = "Galvanising Entry"), false, false, "", $js);

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

if (isset($_POST['ProcessGalvanising']))
{
	if (!can_process())
		unset($_POST['ProcessGalvanising']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelGalvanising']))
{
	//$selected_id = -1;
	$Ajax->activate('_page_body');
}
if (list_updated('spool_code'))
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
	
	if($_POST['spool_code']==-1)
	{
		display_error( _("Coil Number must be selected."));
		set_focus('spool_code');
		return false;
	}
	
	if (strlen($_POST['rm_in_time']) == 0) 
	{
		display_error( _("The Raw Material Coil in Time must be entered."));
		set_focus('rm_in_time');
		return false;
	}
	if (strlen($_POST['rm_out_time']) == 0) 
	{
		display_error( _("The Raw Material Coil out Time must be entered."));
		set_focus('rm_out_time');
		return false;
	}
	
	if (strlen($_POST['coil_in_time']) == 0) 
	{
		display_error( _("The Coil in Time must be entered."));
		set_focus('coil_in_time');
		return false;
	}
	if (strlen($_POST['coil_out_time']) == 0) 
	{
		display_error( _("The Coil out Time must be entered."));
		set_focus('coil_out_time');
		return false;
	}
	
	if (strlen($_POST['final_coil_no']) == 0) 
	{
		display_error( _("The Final Coil No must be entered."));
		set_focus('final_coil_no');
		return false;
	}
	
		
	if (input_num('length_in_mtr') == 0) 
	{
		display_error( _("The Produced Length in meter  must be entered."));
		set_focus('length_in_mtr');
		return false;
	}
	
	if (input_num('qty') == 0) 
	{
		display_error( _("The Calculated Qty must be entered."));
		set_focus('qty');
		return false;
	}
	
	if(input_num('rm_length')<input_num('length_in_mtr'))
	{
		display_error( _("The Length in mtr couldn't be exceed Raw Material length."));
		set_focus('length_in_mtr');
		return false;
	}
		
	if(galvanised_coil_no_exist($_POST['final_coil_no']))
	{
		display_error( _("The Entered Coil No is already Existed."));
		set_focus('final_coil_no');
		return false;
		
	}
	         
	return true;
}



if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmGalvanising']) && can_process())
{
	add_galvanising_details($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['machine_id'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['spool_code'],$_POST['rm_size_id'],input_num('rm_length'),input_num('rm_qty'),$_POST['rm_in_time'],$_POST['rm_out_time'],trim($_POST['final_coil_no']),$_POST['size_id'],input_num('length_in_mtr'),input_num('qty'),$_POST['coil_in_time'],$_POST['coil_out_time'],$_POST['remarks']);
				
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

	display_galvanising_details($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	
	
if (list_updated('spool_code')|| $_POST['spool_code']!=-1)
{
	$spool_info=get_spool_details($_POST['spool_code'],$selected_id);
	
	
	$rm_qty=$spool_info['qty'];
	$rm_size=$spool_info['size'];
	$rm_size_id=$spool_info['size_id'];
	$rm_length=$spool_info['produced_length'];
	$Ajax->activate('_page_body');
}



	start_outer_table(TABLESTYLE2);

	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	text_row(_("GI Machine No.:"), "machine_id",null,20,50);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	galvanising_spool_codes_list_row(_("Coil/Spool No:"), 'spool_code', null, true,true,$selected_id);
	label_row(_("Size:"),$rm_size);
	hidden('rm_size_id',$rm_size_id);
	label_row(_("Length:"),$rm_length);
	hidden('rm_length',$rm_length);
	label_row(_("Weight:"),$rm_qty);
	hidden('rm_qty',$rm_qty);
	text_row(_("Coil in Time:"), "rm_in_time",null,20,50);
    text_row(_("Coil out Time:"), "rm_out_time",null,20,50);	
	table_section(2);
	table_section_title(_("Output Spool Details"));
	set_no_text_row(_("Final Coil No.:"), "final_coil_no",null, null,null,true);
	stock_size_list_row(_("Size:"),'size_id',null);
	qty_row(_("Produced Length:"), 'length_in_mtr', null, null,null,1);	
	qty_row(_("Calculated Weight in Kgs:"), 'qty', null, null,null,2);
	text_row(_("Coil in Time:"), "coil_in_time",null,20,50);
    text_row(_("Coil out Time:"), "coil_out_time",null,20,50);	
	textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	
	end_outer_table(1);
	if (!isset($_POST['ProcessGalvanising']))
	submit_center('ProcessGalvanising', _("Add Galvanising Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmGalvanising', _("Proceed"), '', true);
    		submit_center_last('CancelGalvanising', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

