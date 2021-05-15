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
$page_security = 'SA_MANUF_STRANDING';
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
page(_($help_context = "LRPC/STRANDING Entry"), false, false, "", $js);

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

if (isset($_POST['ProcessLrpc']))
{
	if (!can_process())
		unset($_POST['ProcessLrpc']);
	$Ajax->activate('_page_body');
}

if (isset($_POST['CancelLrpc']))
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
	
	if($_POST['core_wire']==-1 || $_POST['outer1']==-1 || $_POST['outer2']==-1 || $_POST['outer3']==-1 || $_POST['outer4']==-1 || $_POST['outer5']==-1 || $_POST['outer6']==-1)
	{
		display_error( _("Incorrect selection of Spool Loading details."));
		set_focus('core_wire');
		return false;
	}
	
	if (strlen($_POST['machine_start_time']) == 0) 
	{
		display_error( _("The Machine Start Time must be entered."));
		set_focus('machine_start_time');
		return false;
	}
	if (strlen($_POST['machine_stop_time']) == 0) 
	{
		display_error( _("The Machine Stop Time must be entered."));
		set_focus('machine_stop_time');
		return false;
	}
	
	if (strlen($_POST['lrpc_line_no']) == 0) 
	{
		display_error( _("The LRPC Line No  must be entered."));
		set_focus('lrpc_line_no');
		return false;
	}
	
	if (strlen($_POST['set_no']) == 0) 
	{
		display_error( _("The Set No  must be entered."));
		set_focus('set_no');
		return false;
	}	
	
	if (strlen($_POST['take_up_no']) == 0) 
	{
		display_error( _("The Take Up No  must be entered."));
		set_focus('take_up_no');
		return false;
	}	
	
	if (input_num('length_in_mtr') == 0) 
	{
		display_error( _("The Length in Meter  must be entered."));
		set_focus('length_in_mtr');
		return false;
	}	
	
	if (input_num('qty') == 0) 
	{
		display_error( _("The Calculated Weight must be entered."));
		set_focus('qty');
		return false;
	}	
	
	$spool_loading_details[]=$_POST['core_wire'];
	$spool_loading_details[]=$_POST['outer1'];
	$spool_loading_details[]=$_POST['outer2'];
	$spool_loading_details[]=$_POST['outer3'];
	$spool_loading_details[]=$_POST['outer4'];
	$spool_loading_details[]=$_POST['outer5'];
	$spool_loading_details[]=$_POST['outer6'];
	
    $flag=0;
	for($i=0;$i<count($spool_loading_details);$i++)
	{
			for($j=$i+1;$j<count($spool_loading_details);$j++)
			{
				if($spool_loading_details[$i]==$spool_loading_details[$j])
				{
						$flag=1;
						break;
				}					
			}
	}
		
	if($flag==1)
	{
		display_error( _("Duplicate selection in Spool Loading Details."));
		return false;
	}
	
	if($_POST['reason']==2 && strlen($_POST['rejected_spool_code']) == 0 )
	{
		display_error( _("The Rejected Spool Code must be entered."));
		return false;
	}
	
	if($_POST['reason']==2 && !in_array($_POST['rejected_spool_code'], $spool_loading_details))
	{
		display_error( _("Incorrect Rejected Spool Code."));
		return false;
	}
	
	if($_POST['reason']==6 && (strlen($_POST['loading_start_time'])==0 || strlen($_POST['loading_end_time'])==0))
	{
		display_error( _("The Loading Start Time and Ending Time must be entered."));
		return false;
	}
	
	
         
	return true;
}


if (list_updated('reason'))
{
	$Ajax->activate('_page_body');
}

if ($selected_id != '')
{ 
	if (isset($_POST['ConfirmLrpc']) && can_process())
{
	
		add_lrpc_details($selected_id,$_POST['date_'],$_POST['shift_id'],$_POST['lrpc_line_no'],$_POST['operator_id'],$_POST['supervisor_id'],$_POST['core_wire'],$_POST['outer1'],$_POST['outer2'],$_POST['outer3'],$_POST['outer4'],$_POST['outer5'],$_POST['outer6'],input_num('length_in_mtr'),input_num('qty'),$_POST['take_up_no'],trim($_POST['set_no']),$_POST['machine_start_time'],$_POST['machine_stop_time'],$_POST['reason'],$_POST['rejected_spool_code'],$_POST['remarks']);
		
		add_or_update_lrpc($selected_id,trim($_POST['set_no']),$_POST['lrpc_line_no'],$_POST['take_up_no'],input_num('length_in_mtr'),input_num('qty'),$_POST['loading_start_time'],$_POST['loading_end_time'],$_POST['reason']);
		
	new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id&trans_no=$selected_id");
}
	//--------------------------------------------------------------------------------------
 start_form();

	display_lrpc_details_summary($selected_id);
	//--------------------------------------------------------------------------------------
	echo '<br>';
	

	start_outer_table(TABLESTYLE2);

	table_section(1);
    date_row(_("Date") . ":", 'date_', '', true);
	shift_types_list_row(_("Shift:"), 'shift_id', null, true);
	text_row(_("LRPC Line No.:"), "lrpc_line_no",null,20,50);
	operators_list_row(_("Operator:"),'operator_id',null,false,false);
	supervisors_list_row(_("Supervisor:"),'supervisor_id',null,false,false);
	table_section_title(_("Spool Loading Details of a Set"));
	spool_codes_list_row(_("Core Wire:"), 'core_wire', null, true,true,$selected_id);
	spool_codes_list_row(_("Outer-1:"), 'outer1', null, true,true,$selected_id);
	spool_codes_list_row(_("Outer-2:"), 'outer2', null, true,true,$selected_id);
	spool_codes_list_row(_("Outer-3:"), 'outer3', null, true,true,$selected_id);
	spool_codes_list_row(_("Outer-4:"), 'outer4', null, true,true,$selected_id);
	spool_codes_list_row(_("Outer-5:"), 'outer5', null, true,true,$selected_id);
	spool_codes_list_row(_("Outer-6:"), 'outer6', null, true,true,$selected_id);
	
	table_section(2);
		table_section_title(_("Production Details of a Set"));
		set_no_text_row(_("SET No.:"), "set_no",null, null,null,true);
		set_no_text_row(_("Take Up No.:"), "take_up_no",null, null,null,true);
		text_row(_("Loading Start Time:"), "loading_start_time",null,20,50);
        text_row(_("Loading End Time:"), "loading_end_time",null,20,50);
		text_row(_("Start Time of Machine:"), "machine_start_time",null,20,50);
        text_row(_("Stop Time of Machine:"), "machine_stop_time",null,20,50);
	    qty_row(_("Length in Meter:"), 'length_in_mtr', null, null,null,1);	
		qty_row(_("Calculated Weight in Kgs:"), 'qty', null, null,null,2);
		lrpc_machine_stoppage_status_list_row(_("Reason of Stoppage of Machine:"), 'reason', null, true);
		if($_POST['reason']==2)
		text_row(_("Rejected Spool Code:"), "rejected_spool_code",null,20,50);	
		textarea_row(_("Remarks (if Any):"), 'remarks', null, 18, 3);
	
	if($_POST['core_wire']!=-1)
	{
		$core_wire_info=get_spool_details($_POST['core_wire'],$selected_id);
		$core_wire_info_detatils="Length:".$core_wire_info['produced_length'].", Weight:".$core_wire_info['qty'].", No. of Welds:".$core_wire_info['no_of_weld_joints'].", Weld Joint Meters:".$core_wire_info['weld_joint_meters'];
		label_row(_("Core Wire #"), $core_wire_info_detatils);		
	}
	if($_POST['outer1']!=-1)
	{
		$outer1_info=get_spool_details($_POST['outer1'],$selected_id);
		$outer1_info_detatils="Length:".$outer1_info['produced_length'].", Weight:".$outer1_info['qty'].", No. of Welds:".$outer1_info['no_of_weld_joints'].", Weld Joint Meters:".$outer1_info['weld_joint_meters'];
		label_row(_("Outer1 #"), $outer1_info_detatils);		
	}
	if($_POST['outer2']!=-1)
	{
		$outer2_info=get_spool_details($_POST['outer2'],$selected_id);
		$outer2_info_detatils="Length:".$outer2_info['produced_length'].", Weight:".$outer2_info['qty'].", No. of Welds:".$outer2_info['no_of_weld_joints'].", Weld Joint Meters:".$outer2_info['weld_joint_meters'];
		label_row(_("Outer2 #"), $outer2_info_detatils);		
	}
	if($_POST['outer3']!=-1)
	{
		$outer3_info=get_spool_details($_POST['outer3'],$selected_id);
		$outer3_info_detatils="Length:".$outer3_info['produced_length'].", Weight:".$outer3_info['qty'].", No. of Welds:".$outer3_info['no_of_weld_joints'].", Weld Joint Meters:".$outer3_info['weld_joint_meters'];
		label_row(_("Outer3 #"), $outer3_info_detatils);		
	}
	if($_POST['outer4']!=-1)
	{
		$outer4_info=get_spool_details($_POST['outer4'],$selected_id);
		$outer4_info_detatils="Length:".$outer4_info['produced_length'].", Weight:".$outer4_info['qty'].", No. of Welds:".$outer4_info['no_of_weld_joints'].", Weld Joint Meters:".$outer4_info['weld_joint_meters'];
		label_row(_("Outer4 #"), $outer4_info_detatils);		
	}
	if($_POST['outer5']!=-1)
	{
		$outer5_info=get_spool_details($_POST['outer5'],$selected_id);
		$outer5_info_detatils="Length:".$outer5_info['produced_length'].", Weight:".$outer5_info['qty'].", No. of Welds:".$outer5_info['no_of_weld_joints'].", Weld Joint Meters:".$outer5_info['weld_joint_meters'];
		label_row(_("Outer5 #"), $outer5_info_detatils);		
	}
	if($_POST['outer6']!=-1)
	{
		$outer6_info=get_spool_details($_POST['outer6'],$selected_id);
		$outer6_info_detatils="Length:".$outer6_info['produced_length'].", Weight:".$outer6_info['qty'].", No. of Welds:".$outer6_info['no_of_weld_joints'].", Weld Joint Meters:".$outer6_info['weld_joint_meters'];
		label_row(_("Outer6 #"), $outer6_info_detatils);		
	}
	
	
	end_outer_table(1);
	if (!isset($_POST['ProcessLrpc']))
	submit_center('ProcessLrpc', _("Add LRPC Entry"), true, '', 'default');
    else
	{
	display_warning(_("Are you sure you want to save this transaction ? This action cannot be undone."), 0, 1);
   			br();
    		submit_center_first('ConfirmLrpc', _("Proceed"), '', true);
    		submit_center_last('CancelLrpc', _("Cancel"), '', 'cancel');
	}
	end_form();
}
end_page();

