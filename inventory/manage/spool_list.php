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
$page_security = 'SA_SPOOLMASTER';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = "Spool List Master"));

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/inventory/includes/db/spool_list_master_db.inc");

simple_page_mode(false);
//----------------------------------------------------------------------------------
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
	//initialise no input errors assumed initially before we test
	
	$input_error = 0;
     $spool_number=is_unique_spool($_POST['spool_number'],$selected_id);

	if (strlen($_POST['spool_number']) == 0)
	{
		$input_error = 1;
		display_error(_("spool Number cannot be  empty."));
		set_focus('spool_number');
		return;
		
	}
	if($spool_number>0)
	{
		$input_error = 1;
		display_error(_("The Entered spool Number is Already defined."));
		set_focus('spool_number');
		return;
	}
	if ($input_error !=1) {
    	write_spool($selected_id, trim($_POST['spool_number']), $_POST['description']);
		if($selected_id != '')
			display_notification(_('Selected Spool Number has been updated'));
		else
			display_notification(_('New Spool Number has been added'));
		$Mode = 'RESET';
	}
	
}
//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
		delete_spool($selected_id);
		display_notification(_('Selected Spool Number has been deleted'));
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
$result = get_all_spool(check_value('show_inactive'));
start_form();
start_table(TABLESTYLE, "width='40%'");
$th = array(_('Spool No.'), _('Description'),"", "");
inactive_control_column($th);

table_header($th);
$k = 0; //row colour counter


while ($myrow = db_fetch($result))
{

	alt_table_row_color($k);
	label_cell($myrow["spool_number"]);
	label_cell($myrow["description"]);
	
	$id = html_specials_encode($myrow["id"]);
	inactive_control_cell($id, $myrow["inactive"], 'spool_list_master', 'id');
	
 	edit_button_cell("Edit".$id, _("Edit"));
 	delete_button_cell("Delete".$id, _("Delete"));
	
	
	end_row();
}

inactive_control_row($th);
end_table(1);

//----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != '') 
{
 	if ($Mode == 'Edit') {
		//editing an existing item category

		$myrow = get_spool($selected_id);

		$_POST['spool_number']  = $myrow["spool_number"];
		$_POST['description']  = $myrow["description"];
		
	}
	hidden('selected_id', $myrow["id"]);
}

    text_row(_("Spool No:"), 'spool_number', null, 20, 20);
	textarea_row(_("Description:"), 'description', null, 25, 3);

end_table(1);

submit_add_or_update_center($selected_id == '', '', 'both');

end_form();

end_page();

