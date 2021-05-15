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

$page_security = 'SA_UPGRADE_REQUEST_ENT';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include($path_to_root . "/maintenance/includes/db/upgrade_request_entry_db.inc");

include($path_to_root . "/includes/ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
simple_page_mode(true);
page(_($help_context = "Upgrade Request Entry"));
//-----------------------------------------------------------------------------------

	
if($Mode=='ADD_ITEM'){
	//initialise no input errors assumed initially before we test
	
	

	if (!check_reference($_POST['ref'], ST_UPGRADEREQENT))
    	{
			$input_error = 1;
			set_focus('ref');
    		return false;
    	}

	

	
	
	if ($input_error != 1) 
	{


    		add_upgrade_req_entry(trim($_POST['ref']),trim($_POST['machine_id']),$_POST['details']);
			
			display_notification(_('New upgrade request entry  has been added successfully'));
    	
		$Mode = 'RESET';
	
} 
}





if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//-----------------------------------------------------------------------------------


start_form();

//-----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

ref_row(_("Reference:"), 'ref', '', $Refs->get_next(ST_UPGRADEREQENT), false, ST_UPGRADEREQENT);
machine_name_list_row(_("Machine Name:"), 'machine_id', null, false, true);

textarea_row(_("Upgrade details:"), 'details', null, 40, 5);
end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');
echo "<br>";
echo "<br>";


//------------------------------------------------------------------------------------

end_page();

