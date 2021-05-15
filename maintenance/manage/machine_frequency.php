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
$page_security = 'SA_MACHINE_FREQUENCY';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = "Machine Frequency Master"));

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/maintenance/includes/db/machine_frequency_db.inc");

simple_page_mode(false);

//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['mac_fre']) == 0)
	{
		$input_error = 1;
		display_error(_("The machine frequency name cannot be empty."));
		set_focus('mac_fre');
	}
	
	/* if (strlen($_POST['description']) == 0)
	{
		$input_error = 1;
		display_error(_("The  description cannot be empty."));
		set_focus('description');
	} */

	if ($input_error !=1) {
    	add_machine_frequency($selected_id, $_POST['mac_fre'], $_POST['description'],input_num('days') );
		if($selected_id != '')
			display_notification(_('Selected machine frequency has been updated'));
		else
			display_notification(_('New machine frequency has been added'));
		$Mode = 'RESET';
	}
}


if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'

	if (key_in_foreign_table($selected_id, 'machine', 'mac_fre'))
	{
		$cancel_delete = 1;
		display_error(_("This machine frequncy cannot be deleted because there are machine master that refer to it."));
	}
	
	if (key_in_foreign_table($selected_id, 'machine_maintenance_schedule', 'mac_fre'))
	{
		$cancel_delete = 1;
		display_error(_("This machine frequncy cannot be deleted because there are machine maintenance schedule that refer to it."));
	}
	
	if (key_in_foreign_table($selected_id, 'machine_maintenance_checklists', 'machine_fre_id'))
	{
		$cancel_delete = 1;
		display_error(_("This machine frequncy cannot be deleted because there are machine maintenance check lists that refer to it."));
	}
	
	
	else
	{
		delete_machine_frequency_category($selected_id);
		display_notification(_('Selected machine frequncy has been deleted'));
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


$result = get_all_machine_frequency_categories(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='40%'");
$th = array(_('Machine Frequency name'), ("Days"),_('Description'), "", "");
inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result))
{

	alt_table_row_color($k);

	label_cell($myrow["mac_fre"]);
	label_cell($myrow["days"]);
	label_cell($myrow["description"]);
    $id = htmlentities($myrow["id"]);
	inactive_control_cell($id, $myrow["inactive"], 'machine_frequency', 'id');
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
		
		$myrow = get_machine_frequency_category($selected_id);
		$_POST['mac_fre'] = $myrow["mac_fre"];
		
		$_POST['description']  = $myrow["description"];
		$_POST['days']  = $myrow["days"];
	
	}
	hidden('selected_id', $myrow["id"]);
}

text_row(_("Machine Frequency Name: <b style='color:red'>*</b>"), 'mac_fre', null, 20, 20);

qty_row(_("Number of Days :<b style='color:red;'>*</b>"), 'days', null, null, null, 0);
textarea_row(_("Description:"), 'description', null, 40, 5);



end_table(1);

submit_add_or_update_center($selected_id == '', '', 'both');

end_form();

end_page();

