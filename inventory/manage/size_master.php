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
$page_security = 'SA_SIZEMASTER';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

page(_($help_context = "Size Master"));

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/inventory/includes/db/size_master_db.inc");

simple_page_mode(false);
//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;
    $size=is_unique_size($_POST['size'],$selected_id);
	if($size>0)
	{
		$input_error = 1;
		display_error(_("The Size exists."));
		set_focus('size');
		return;
		
	}	
	if (strlen($_POST['size']) == 0)
	{
		$input_error = 1;
		display_error(_("Size cannot be  empty."));
		set_focus('size');
		return;
		
	}
	
	if (strlen($_POST['size']) >= 10)
	{
		$input_error = 1;
		display_error(_("The entered Size should be below 10 characters."));
		set_focus('size');
		return;
		
	}
	if (strlen(db_escape($_POST['size']))>(20+2))
	{
		$input_error = 1;
		display_error(_("The Size code is very large."));
		set_focus('size');
		return;
	}
	
		


	if ($input_error !=1) {
    	write_size($selected_id, trim($_POST['size']), $_POST['description']);
		if($selected_id != '')
			display_notification(_('Selected Size has been updated'));
		else
			display_notification(_('New unit Size been added'));
		$Mode = 'RESET';
	}
	
}

//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	if (key_in_foreign_table($selected_id, 'stock_master', 'size_id'))
	{
		display_error(_("Cannot delete this Size because Items master have been created referring to it."));
		return false;
	}

	else
	{	
		delete_size($selected_id);
		display_notification(_('Selected Size has been deleted'));
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

$result = get_all_size(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='40%'");
$th = array(_('Size'), _('Description'),"", "");
inactive_control_column($th);

table_header($th);
$k = 0; //row colour counter


while ($myrow = db_fetch($result))
{

	alt_table_row_color($k);
	label_cell($myrow["size"]);
	label_cell($myrow["description"]);
	
	$id = html_specials_encode($myrow["id"]);
	inactive_control_cell($id, $myrow["inactive"], 'size_master', 'id');
	if($myrow['id']!=1)
	{	
 	edit_button_cell("Edit".$id, _("Edit"));
 	delete_button_cell("Delete".$id, _("Delete"));
	}
	if($myrow['id']==1)
	{
			label_cell("");
			label_cell("");


	}
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

		$myrow = get_size($selected_id);

		$_POST['size']  = $myrow["size"];
		$_POST['description']  = $myrow["description"];
		
	}
	hidden('selected_id', $myrow["id"]);
}

    qty_row(_("Size:"), 'size', null, null,null,2);
	textarea_row(_("Description:"), 'description', null, 15, 3);

end_table(1);

submit_add_or_update_center($selected_id == '', '', 'both');

end_form();

end_page();

