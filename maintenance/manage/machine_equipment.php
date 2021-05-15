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
$page_security = 'SA_MACHINE_EQUIPMENT';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = "Machine Equipment Master"));

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/maintenance/includes/db/machine_equipment_db.inc");

simple_page_mode(false);

//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;
	
	  if (empty($_POST["mac_fre"]))
	{
		$input_error = 1;
		display_error(_("At least one frequency should to select."));
		set_focus('mac_fre');
	}

	if (strlen($_POST['mac_eqp']) == 0)
	{
		$input_error = 1;
		display_error(_("The machine equipment cannot be empty."));
		set_focus('mac_eqp');
	}
	
	/* if (strlen($_POST['description']) == 0)
	{
		$input_error = 1;
		display_error(_("The  description cannot be empty."));
		set_focus('description');
	} */

	if ($input_error !=1) {
		
		$mac_fre=implode(',',$_POST["mac_fre"]);
		
    	add_machine_equipment($selected_id, $_POST['mac_eqp'], $_POST['description'],$mac_fre);
		if($selected_id != '')
			display_notification(_('Selected machine equipment has been updated'));
		else
			display_notification(_('New machine equipment has been added'));
		$Mode = 'RESET';
	}
}

//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'

	if (key_in_foreign_table($selected_id, 'machine', 'mac_eqp'))
	{
		$cancel_delete = 1;
		display_error(_("This machine equipment cannot be deleted because there are machine master that refer to it."));
	}
	
	else
	{
		delete_machine_equipment_category($selected_id);
		display_notification(_('Selected machine equipment has been deleted'));
	}
	$Mode = 'RESET';
}



if ($Mode == 'RESET')
{
	$selected_id = '';
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//----------------------------------------------------------------------------------


$result = get_all_machine_equipment_categories(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='80%'");
$th = array(_('Machine Equipment name'), _('Description'), _('Frequency'), "", "");
inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result))
{
  $mac_fre = explode(",", $myrow["mac_fre"]);
	alt_table_row_color($k);
     $mac_frequency=get_machine_equipement_frequency($myrow["id"]);
	label_cell($myrow["mac_eqp"]);
	label_cell($myrow["description"]);
	label_cell($mac_frequency);
    $id = htmlentities($myrow["id"]);
	inactive_control_cell($id, $myrow["inactive"], 'machine_equipment', 'id');
	edit_button_cell("Edit".$myrow["id"], _("Edit"));
 	delete_button_cell("Delete".$myrow["id"], _("Delete"));
	end_row();
}
inactive_control_row($th);
end_table(1);

//----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != '') 
{
 	if ($Mode == 'Edit') {
		
		$myrow = get_machine_equipment_category($selected_id);
		$_POST['mac_eqp'] = $myrow["mac_eqp"];
		
		$mac_fre = explode(",", $myrow["mac_fre"]);
		$_POST['mac_fre'] = $mac_fre;
		
		$_POST['description']  = $myrow["description"];
	
	}
	hidden('selected_id', $myrow["id"]);
}
text_row(_("Machine Equipment Name: <b style='color:red'>*</b></b>"), 'mac_eqp', null, 20, 20);

multi_frequency_list_cells(_("Machine Frequency: <b style='color:red'>*</b>"), 'mac_fre', null, false, true, true,false);

textarea_row(_("Description:"), 'description', null, 40, 5);



end_table(1);

submit_add_or_update_center($selected_id == '', '', 'both');

end_form();

end_page();

