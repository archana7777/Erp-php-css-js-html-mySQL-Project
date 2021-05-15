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
	
$page_security = 'SA_UPGRADE_REQUEST';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");


//page(_($help_context = "Test Master"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include($path_to_root . "/maintenance/includes/db/upgrade_request_entry_db.inc");


$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Upgrade Request"), false, false, "", $js);

simple_page_mode(false);
//----------------------------------------------------------------------------------

if (isset($_GET["id"]))
{
	$_POST["id"] = $_GET["id"];	
}



	  if( list_updated('is_visit'))
{
	//$Ajax->activate('_page_body');
	$Ajax->activate('_page_body');
}
		
	


if ($_POST['UPDATE_ITEM'] ) 
{           

	/*	if (strlen($_POST['materials']) == 0) 
	{
		$input_error = 1;
		display_error(_("The materials required cannot be empty."));
		set_focus('materials');
		return false;
	}  */
	//initialise no input errors assumed initially before we test
		
		$selected_id=$_POST['selected_id'];
add_upgrade_details($selected_id,$_POST['ref'],$_POST['start_date'],$_POST['end_date'],$_POST['materials'],$_POST['remarks'],$_POST['status']  );
					if($_POST['status']==1)
			{
				
				update_upgrade_details($selected_id,$_POST['status']  );
			}
			else
			{
			update_upgrade_details($selected_id,$_POST['status']  );
			}
    	
		
			
			//display_notification(_('Selected test request  has been updated'));
		
		meta_forward($path_to_root . "/maintenance/inquiry/upgrade_request.php?type=1109");
		
	
}

//----------------------------------------------------------------------------------

start_form(true);


start_table(TABLESTYLE2);
	if(isset($_GET['id']) || isset($_POST['selected_id']))
	{
		if(isset($_GET['id']))
		{
			$id=$_GET['id'];
		}	
	if(isset($_POST['selected_id']))
		{
			$id=$_POST['selected_id'];
		}		
		$myrow=get_upgrade_details($id);
		$ref=$myrow['ref'];
		$machine=$myrow['mac_eqp'];
		
		$details=$myrow['details'];
		$selected_id=$myrow['id'];
		hidden("selected_id",$selected_id);
		hidden("ref",$ref);
		
		hidden("id",$_POST["id"]);
	
	}
	//display_error($req_time);die;
	
	//display_error($sub);

   
   label_row(_("Reference :"), $ref);
   label_row(_("Machine Name :"), $machine);
   label_row(_("Details :"), $details);

       date_row(_("Start Date:"), 'start_date','',null);
       date_row(_("End Date:"), 'end_date','',null);
	
textarea_row(_("Materials required:"), 'materials', null, 40, 5);
textarea_row(_("Remarks:"), 'remarks', null, 40, 5);

follow_status_list_row(_("Status:"), 'status', null,true); 
	
	
	


end_table(1);

submit_center('UPDATE_ITEM', _("Submit"), true, '', 'default');
//submit_add_or_update_center($selected_id == '', '', 'both');

end_form();

end_page();

