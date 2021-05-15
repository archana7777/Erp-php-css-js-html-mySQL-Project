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
$page_security = 'SA_MANUF_DRAWING';
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
page(_($help_context = "Drawing Entry"), false, false, "", $js);

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

if (isset($_POST['ProcessDrawing']))
{
	if (!can_process())
		unset($_POST['ProcessDrawing']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelDrawing']))
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
	
	if (strlen($_POST['spool_code']) == 0) 
	{
		display_error( _("The Spool Code must be entered."));
		set_focus('spool_code');
		return false;
	}
	
	if (input_num('used_coil_qty') == 0) 
	{
		display_error( _("The Coil Weight Used in Spool must be entered."));
		set_focus('used_coil_qty');
		return false;
	}
	
	if (strlen($_POST['spool_in_time']) == 0 && $_POST['spool_in_time_check']) 
	{
		display_error( _("Spool in Time must be entered."));
		set_focus('spool_in_time');
		return false;
	}
	if (strlen($_POST['spool_out_time']) == 0 && $_POST['spool_out_time_check']) 
	{
		display_error( _("Spool out Time must be entered."));
		set_focus('spool_in_time');
		return false;
	}
	if (input_num('actual_spool_qty')==0 && $_POST['spool_out_time_check']) 
	{
		display_error( _("Spool Actual Quantity must be entered."));
		set_focus('actual_spool_qty');
		return false;
	}
	
	if (input_num('produced_length')==0 && $_POST['spool_out_time_check']) 
	{
		display_error( _("Spool Produced Length must be entered."));
		set_focus('produced_length');
		return false;
	}
	
	if (strlen($_POST['weld_joint_meters']) == 0 && $_POST['weld_joint_meters_check']) 
	{
		display_error( _("Weld Joint Meters must be entered."));
		set_focus('weld_joint_meters');
		return false;
	}
	if (input_num('no_of_weld_joints')==0 && $_POST['weld_joint_meters_check']) 
	{
		display_error( _("No. of Weld Joints must be entered."));
		set_focus('no_of_weld_joints');
		return false;
	}
	
	if (input_num('used_coil_qty')>input_num('qty')) 
	{
		display_error( _("Used Coil Weight could not be exceed the Raw Material Coil Weight."));
		set_focus('no_of_weld_joints');
		return false;
	}
	
	if(allow_spool_code($_POST['spool_code']))
	{
		display_error( _("Entered Spool Code is Already Finished."));
		set_focus('spool_code');
		return false;	
	}
	
      
	return true;
}


if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmDrawing']) && can_process())
{
	
		add_drawing_coil_details($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['machine_id'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['stock_id'],$_POST['coil_no'],$_POST['heat_no'],$_POST['qty'],input_num('used_coil_qty'),trim($_POST['spool_code']),$_POST['coil_in_time'],$_POST['coil_out_time'],$_POST['remarks']);
		
		add_or_update_spool_details($selected_id,trim($_POST['spool_code']),$_POST['spool_id'],$_POST['size_id'],input_num('produced_length'),input_num('actual_spool_qty'),$_POST['spool_in_time'],$_POST['spool_out_time'],input_num('no_of_weld_joints'),$_POST['weld_joint_meters']);
		
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

	display_drawing_items_summary($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	

$heat_no=$grade=$size=$weight="";	
if (list_updated('stock_id') || list_updated('loc_code'))
{
	$Ajax->activate('_page_body');
}
if (list_updated('coil_no')|| $_POST['coil_no']!=-1)
{
	$coil_details=get_pickled_coil_details($_POST['stock_id'],$_POST['coil_no'],$selected_id);
	
	$heat_no=$coil_details['heat_no'];
	$grade=$coil_details['grade'];
	$size=$coil_details['size'];
	$weight=$coil_details['qty']-$coil_details['used_qty'];	
	$Ajax->activate('_page_body');
}

if($_POST['spool_code'])
{
	$spool_details=get_spool_details($_POST['spool_code'],$selected_id);
	if($spool_details['spool_in_time']!='')
	{
		$_POST['spool_in_time_check']=1;
		$_POST['spool_in_time']=$spool_details['spool_in_time'];
		$_POST['spool_id']=$spool_details['spool_id'];
		$_POST['size_id']=$spool_details['size_id'];
	}
	
	if($spool_details['spool_out_time']!='')
	{
		$_POST['spool_out_time_check']=1;
		$_POST['spool_out_time']=$spool_details['spool_out_time'];
		$_POST['actual_spool_qty']=$spool_details['qty'];
		$_POST['produced_length']=$spool_details['produced_length'];
	}
	
	if($spool_details['no_of_weld_joints']!=0)
	{
		$_POST['weld_joint_meters_check']=1;
		$_POST['no_of_weld_joints']=$spool_details['no_of_weld_joints'];
		$_POST['weld_joint_meters']=$spool_details['weld_joint_meters'];
	}
	
	$Ajax->activate('_page_body');	
}



	start_outer_table(TABLESTYLE2);

	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	operators_list_row(_("Machine:"),'machine_id',null,false,false);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	table_section_title(_("Input Raw Material Coil Details"));
	locations_list_row(_("Location to Draw From:"), 'loc_code', null,false,true);
    production_rm_items_list_row(_("Raw Material:"), 'stock_id', null, true,true);
	pickled_coils_list_row(_("Coil No:"), 'coil_no', null, true,true,$_POST['stock_id'],$_POST['loc_code'],$selected_id);
	label_row(_("Heat No:"), $heat_no);
	hidden('heat_no',$heat_no);
	label_row(_("Size in (mm):"),$size );
	label_row(_("Grade:"), $grade);
	label_row(_("Weight:"), $weight);
	hidden('qty',$weight);
	text_row(_("Coil in Time:"), "coil_in_time",null,20,50);
	text_row(_("Coil out Time:"), "coil_out_time",null,20,50);
	textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	table_section(2);
	
	table_section_title(_("Output Spool Details"));
	qty_row(_("Coil Weight Used in Spool:"), 'used_coil_qty', null, null,null,2);
	//text_row(_("Spool Code:"), "spool_code",null,20,50);
	spool_code_text_row(_("Spool Code:"), "spool_code",null, null,null,true);
	check_row(_("Check to Enter Spool in Time"), 'spool_in_time_check', null,true);
	if($_POST['spool_in_time_check'])
	{
	   operators_list_row(_("Spool No.:"),'spool_id',null,false,false);
	  text_row(_("Spool in Time:"), "spool_in_time",null,20,50);
	  stock_size_list_row(_("Size:"),'size_id',null);
	}
	check_row(_("Check to Enter Spool out Time"), 'spool_out_time_check', null,true);
	if($_POST['spool_out_time_check'])
	{
	text_row(_("Spool out Time:"), "spool_out_time",null,20,50);
	qty_row(_("Produced Length:"), 'produced_length', null, null,null,2);
	qty_row(_("Actual Weight:"), 'actual_spool_qty', null, null,null,2);
	}
	check_row(_("Is Any Weld Joint"), 'weld_joint_meters_check', null,true);
	if($_POST['weld_joint_meters_check'])
	{
	qty_row(_("No. of Weld Joints:"), 'no_of_weld_joints', null, null,null,1);	
	text_row(_("Weld Joint Meters:"), "weld_joint_meters",null,20,50);
	}
	
	end_outer_table(1);
	if (!isset($_POST['ProcessDrawing']))
	submit_center('ProcessDrawing', _("Add Drawing Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmDrawing', _("Proceed"), '', true);
    		submit_center_last('CancelDrawing', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

