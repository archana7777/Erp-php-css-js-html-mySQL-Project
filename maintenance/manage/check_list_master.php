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
$page_security = 'SA_MACHINE_CHECKLIST';
$path_to_root = "../..";
$path_to_root1 = "../../..";

include_once($path_to_root . "/includes/session.inc");

page(_($help_context = "Maintenance Checklist"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/maintenance/includes/db/check_list_db.inc");


check_db_has_machines(("There are no Machines defined in the system."));
check_db_has_machine_frequency(_("There are no Machine Frequency's defined in the system."));

simple_page_mode(true);
$selected_component = $selected_id;
//--------------------------------------------------------------------------------------------------

if (isset($_GET['equipment_id']))
{
	$_POST['equipment_id'] = $_GET['equipment_id'];
	$selected_parent =  $_GET['equipment_id'];
}

function display_bom_items($selected_parent,$selected_fre,$selected_p_type)
{
	div_start('boms');
	
	$result = get_check_lists($selected_parent,$selected_fre,$selected_p_type);
	
	start_table(TABLESTYLE, "width='80%'");
	$th = array(_("Order No"), _("Details"),'','');
	table_header($th);

	$k = 0;
	while ($myrow = db_fetch($result))
	{

		alt_table_row_color($k);

		label_cell($myrow["order_no"],'&nowrap align="center"');
		label_cell($myrow["details"]);
        /* if($myrow["input_req"]==1){
			label_cell("Yes");
		}else {
			label_cell("No");
		} */
        edit_button_cell("Edit".$myrow['id'], _("Edit"));
 		delete_button_cell("Delete".$myrow['id'], _("Delete"));
        end_row();

	} //END WHILE LIST LOOP
	end_table();
	div_end();
}


//--------------------------------------------------------------------------------------------------

function on_submit($selected_parent, $selected_component=-1,$Mode,$selected_id,$selected_p_type)
{
	
	if ($_POST['order_no']=='')
	{
		display_error(_("The order should not be empty."));
		set_focus('order_no');
		return;
	}	
	if (!check_num('order_no', 0))
	{
		display_error(_("The order entered must be numeric (like 1 or 1.1 ) or positive numbers."));
		set_focus('order_no');
		return;
	}
	
	if ($_POST['details']=='')
	{
		display_error(_("The Detais should not be empty."));
		set_focus('details');
		return;
	}
	
	 $duplicate_no=getvalid_order_no($selected_parent,$_POST["mac_fre_id"],$_POST["order_no"],$_POST['mc_problem_type']);
	 
	if($duplicate_no >=1)
	{    
   // Sandeep
        if($Mode=="UPDATE_ITEM")
		{
			$myrow=get_check_list_edit($selected_id);
			$order=$myrow["order_no"];
			if($_POST['order_no']!=$order)
			{
				display_error(_("The order number is duplicate please enter differnt values!."));
		        set_focus('order_no');
		        return;
			}
		}
		else if($Mode=="ADD_ITEM")
		{
		display_error(_("The order number is duplicate please enter differnt values!."));
		set_focus('order_no');
		return;
		}
	} 
	if ($selected_component != -1)
	{
			//display_error($selected_component);
		update_check_lists($selected_component,$selected_parent,$_POST["mac_fre_id"], $_POST['details'], $_POST['order_no'],$_POST['input_req'],$_POST["mc_problem_type"]);
		display_notification(_('Selected details has been updated for this machine!'));
		//$Mode == 'RESET';
	//	meta_forward($path_to_root);	
		
		//meta_forward($path_to_root.'/maintenance/manage/check_list_master.php');
	}
	else
	{
		add_checklists($selected_parent,$_POST["mac_fre_id"], $_POST['details'], $_POST['order_no'],$_POST['input_req'],$_POST["mc_problem_type"]);
		display_notification(_("A new details has been added for this machine!."));
		unset($_POST['details']);
		unset($_POST['order_no']);
		
	}
}

//--------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	delete_check_list($selected_id);

	display_notification(_("The details  has been deleted from study type!"));
	//$Mode = 'RESET';
}

if ($Mode == 'RESET')
{  
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	
	$_POST['show_inactive'] = $sav;
}


//--------------------------------------------------------------------------------------------------
start_form();
start_table(TABLESTYLE_NOBORDER);
start_row();
machine_equipment_list_cells(_("Machine Equipment Name:"), 'equipment_id', null, false, true);
maintenance_mc_analysis_type_row(_("Problem Type:"),'mc_problem_type',null,false);
frequency_name_list_row(_("Frequency Type:"), 'mac_fre_id',null, false, true,$_POST['equipment_id']);

end_row();
if (list_updated('equipment_id'))
{   
	$selected_id = -1;
	$Ajax->activate('mac_fre_id');
	$Ajax->activate('_page_body');
	//$Mode = 'RESET';
}

end_table();
br();

  if ($_POST['mac_fre_id'])
  {   
  $Ajax->activate('boms');
  }
//--------------------------------------------------------------------------------------------------
	if (get_post('equipment_id') != '')
	{ 
	$selected_parent = $_POST['equipment_id'];
	$selected_fre = $_POST['mac_fre_id'];
	$selected_p_type=$_POST["mc_problem_type"];
	if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
		on_submit($selected_parent, $selected_id,$Mode,$selected_id,$selected_p_type);
         
	//--------------------------------------------------------------------------------------
 
  display_bom_items($selected_parent,$selected_fre,$selected_p_type);
	//--------------------------------------------------------------------------------------
	echo '<br>';
  
	start_table(TABLESTYLE2);

	if ($selected_id != -1)
	{
 		if ($Mode == 'Edit') {
			//editing a selected component from the link to the line item
			$myrow = get_check_list_edit($selected_id);
			
			$_POST['machine_id'] = $myrow["machine_id"];
			$_POST['mac_fre_id'] = $myrow["machine_fre_id"];
			$_POST['details'] = $myrow["details"];
			$_POST['order_no'] = $myrow["order_no"]; // by Tom Moulton
			$_POST['input_req']  = $myrow["input_req"];	
			$_POST['mc_problem_type']  = $myrow["mc_problem_type"];	
		}
		hidden('selected_id', $selected_id);
	}
	textarea_row(_("Details:"), 'details',null,35, 4);
	text_row_ex(_("Order No: <b style='color:red'>*</b>"), 'order_no', null, null, null);
  // check_row(_("Input Required:"), 'input_req',$_POST['input_req']);
   end_table(1);
   submit_add_or_update_center($selected_id == -1, '', 'both');
}


end_form();
// ----------------------------------------------------------------------------------
//div_end();
end_page();

