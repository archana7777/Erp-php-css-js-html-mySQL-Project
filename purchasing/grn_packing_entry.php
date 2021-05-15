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
$page_security = 'SA_GRN_PACKING';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

//page(_($help_context = "GRN Packing List Entry"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/purchasing/includes/db/packing_list_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$js = '';

if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}

if (user_use_date_picker()) {
	$js .= get_js_date_picker();
}


$_SESSION['page_title'] = _($help_context = "Packing List Entry Against GRN");

page($_SESSION['page_title'], false, false, "", $js);

check_db_has_pending_packing_items(_("There are no pending packing list for grn items in the system."));


simple_page_mode(true);
$selected_component = $selected_id;
//--------------------------------------------------------------------------------------------------

if (isset($_GET['stock_id']))
{
	$_POST['stock_id'] = $_GET['stock_id'];
	$selected_parent =  $_GET['stock_id'];
}

//--------------------------------------------------------------------------------------------------

function display_pending_packing_list_items_roll_nos($selected_parent)
{
	
	$result = get_packing_list_grn_nos($selected_parent);
	div_start('bom');
	start_table(TABLESTYLE, "width='60%'");
	$th = array(_("Coil Number"), _("Weight"),_("Heat No"),_("Remarks"),'','');
	table_header($th);

	$k = 0;
	while ($myrow = db_fetch($result))
	{

		alt_table_row_color($k);

		label_cell($myrow["coil_number"]);
		
		qty_cell($myrow["weight"], false, 2);
		label_cell($myrow["heat_no"]);
		label_cell($myrow["remarks"]);
		
 		edit_button_cell("Edit".$myrow['id'], _("Edit"));
 		delete_button_cell("Delete".$myrow['id'], _("Delete"));
        end_row();

	} //END WHILE LIST LOOP
	
	end_table();
	div_end();
}

//--------------------------------------------------------------------------------------------------
if (isset($_POST['FINAL']))
{
	
	
	$selected_parent = $_POST['stock_id'];
	
		
	$grn_sqm=get_total_qty_in_grn($selected_parent); //grn quantity 
	
	$grn_qty = get_total_received_qty_in_grn($selected_parent);
	
	

	$grn_det=get_packing_list_item_code($selected_parent);
	//display_error($grn_det["item_code"]); die;
	$sql="select coil_no_req from ".TB_PREF."stock_master where stock_id=".db_escape($grn_det["item_code"]);
	
	$result = db_query($sql, "Could not retreive item type");
	$row = db_fetch_row($result);
	$packing_list_required=$row[0];
	
	
	
	if ($packing_list_required==1)
	{
		
		$packing_weight=get_packing_list_total_packs($selected_parent);
		
		$packing_list_weight=get_packing_list_total_weight($selected_parent);
		
		
		if($grn_sqm<=$packing_weight && $grn_qty<=$packing_list_weight){
		
			update_final_submit_purch_grn($selected_parent);
			display_notification(_('Selected coil number has been updated'));
			$Mode = 'RESET';
		}else
			{
				
			display_error("Packing list no of packages  and GRN no of packages are not equal! GRN no of packages is ".$grn_sqm." and packing list no of packages is ".$packing_weight);
			
			display_error("Packing list weight  and GRN weight are not equal! GRN weight is ".$grn_qty." and packing list weight is ".$packing_list_weight);
			
			return false;
		}
		
		
		
	}
	/*
	else{
		//$packing_sqm=get_packing_list_total_sqm($selected_parent);
		if($grn_sqm==$packing_sqm){
		//update_final_submit_purch_grn($selected_parent);
		display_notification(_('Selected roll/bale number has been updated'));
			$Mode = 'RESET';
		}else
			{
			display_error("packing list sqm  and delivery sqm are not equal! delivery sqm ".$grn_sqm."packing sqm is".$packing_sqm);
			return false;
		}
	}
	*/
}


function on_submit($selected_parent, $selected_roll_number=-1)
{
	
	
	
	 if (strlen($_POST['coil_number']) == 0) 
	{
		display_error( _('The coil number cannot be empty'));
		set_focus('coil_number');
		return;
	}
	elseif (strstr($_POST['coil_number'], " ") || strstr($_POST['coil_number'],"'") || 
		strstr($_POST['coil_number'], "+") || strstr($_POST['coil_number'], "\"")   || 
		strstr($_POST['coil_number'], "&") || strstr($_POST['coil_number'], "*")    || 
		strstr($_POST['coil_number'], "#")  || strstr($_POST['coil_number'], "@")    || 
		strstr($_POST['coil_number'], "$")  || strstr($_POST['coil_number'], "%")    || 
		strstr($_POST['coil_number'], "*") || strstr($_POST['coil_number'], "-")) 
	{
		
		display_error( _('The coil number cannot contain any of the following characters -  & + OR a space OR quotes'));
		set_focus('coil_number');
        return;
	}
	
	if (!check_num('weight', 1))
	{
		display_error(_("The weight entered must be numeric and greater than zero."));
		set_focus('weight');
		return;
	}
	
	$batch_code_stock_check=check_exist_grn_packing_list_batch_no_in_stock($_POST['coil_number']);
   if($batch_code_stock_check >0)
   {
	display_error("Coil Number already exists in stock!");
	set_focus('coil_number');
	return false;
   }
	
	$batch_code_grn_check=check_exist_grn_packing_list_batch_code($_POST['batch_code'],$selected_roll_number);
   if($batch_code_grn_check >0)
   {
	display_error("Coil Number already exists in packing list!");
	set_focus('coil_number');
	return false;
   }
	
	
	$grn_tot_qty=get_total_qty_in_grn($selected_parent);  //grn quantity  either sqm or weight
	$grn_det=get_packing_list_item_code($selected_parent);
	
	$grn_reced_qty = get_total_received_qty_in_grn($selected_parent);
	
	$sql1="SELECT coil_no_req FROM ".TB_PREF."stock_master WHERE stock_id=".db_escape($grn_det["item_code"]);
	
	$result1 = db_query($sql1, "Could not retreive item type");
	$row1 = db_fetch_row($result1);
	$packing_list_required=$row1[0];
	
	
	
	if ($packing_list_required==1)
	{
		$packing_weight=get_packing_list_total_packs($selected_parent);
		$packing_weight1=$packing_weight+$_POST['no_of_packs'];
		
		$packing_list_weight=get_packing_list_total_weight($selected_parent);
		$packing_list_weight1=$packing_list_weight+$_POST['weight'];
		
		
		
		if($packing_weight1>$grn_tot_qty)
		{
		display_error(_("The total no of packages is more than the GRN no of packages ".$grn_tot_qty));
		set_focus('qty');
		return;
		}
		
		if($packing_list_weight1>$grn_reced_qty)
		{
		display_error(_("The total weight is more than the GRN weight ".$grn_reced_qty));
		set_focus('qty');
		return;
		}
	
	}
	/*
	else{
		//$packing_sqm=get_packing_list_total_sqm($selected_parent);
		$packing_sqm1=$packing_sqm+(input_num('width')*input_num('length'));
		if($packing_sqm1>$grn_sqm)
		{
			display_error(_("The total sqm is more than the dispatched sqm.Total sqm is".$grn_sqm));
			set_focus('width');
			return;
		}
	}
	*/
	if ($selected_roll_number != -1)
	{
		
		
		update_packing_list_grn_no($selected_roll_number, $_POST['coil_number'], $_POST['length'],$_POST['heat_no'],$_POST['remarks'],$_POST['weight']);
		display_notification(_('Selected coil number has been updated'));
		$Mode = 'RESET';
	}
	else
	{

		date_default_timezone_set('Asia/Kolkata');
		$packing_date = date('Y-m-d');
				
				add_packing_list_grn_no($selected_parent, $_POST['coil_number'], $_POST['length'],$_POST['heat_no'],$_POST['remarks'],$_POST['no_of_packs'],$packing_date,$_POST['weight']);
				display_notification(_("A new coil number has been added to the packing list."));
				$Mode = 'RESET';
	}
	
			
}

//--------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	delete_packing_list($selected_id);

	display_notification(_("Thecoil number has been deleted from packing list"));
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST['length']);
}

//--------------------------------------------------------------------------------------------------

start_form();

start_form(false, true);
start_table(TABLESTYLE_NOBORDER);
start_row();

pending_purchasing_packing_list_items_cells(_("Select a Packing List item:"), 'stock_id', null, false, true);


end_row();
if (list_updated('stock_id'))
{
	$selected_id = -1;
	$Ajax->activate('_page_body');
}

end_table();
br();

end_form();
//--------------------------------------------------------------------------------------------------

if (get_post('stock_id') != '')
{ //Parent Item selected so display bom or edit component

	$selected_parent = $_POST['stock_id'];
	
	if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
		on_submit($selected_parent, $selected_id);
	//--------------------------------------------------------------------------------------

start_form();
	display_pending_packing_list_items_roll_nos($selected_parent);
	//--------------------------------------------------------------------------------------
	echo '<br>';

	start_table(TABLESTYLE2);

	if ($selected_id != -1)
	{
 		if ($Mode == 'Edit') {
			//editing a selected component from the link to the line item
			$myrow = get_packing_list_item_grn($selected_id);
            $_POST['coil_number'] = $myrow["coil_number"];
			$_POST['length'] = $myrow["length"];
			$_POST['heat_no'] = $myrow["heat_no"];
			$_POST['remarks'] = $myrow["remarks"];
			$_POST['weight'] = $myrow["weight"];
			
		}
		hidden('selected_id', $selected_id);
	}
    text_row(_("Coil Number:"), "coil_number", $_POST['coil_number'], 20, 50);
	hidden('no_of_packs', 1);
	
	//qty_row(_("Length"), 'length', null, null, null, null);
	
	qty_row(_("Weight"),'weight',0,null," in Kgs",0);
	
	text_row(_("Heat No:"), "heat_no", $_POST['heat_no'], 20, 50);
	
	textarea_row(_("Remarks:"), 'remarks', null, 25, 4);

	end_table(1);
	submit_add_or_update_center($selected_id == -1, '', 'both');
	br();
	br();
	submit_center('FINAL', _("Final Submit"), true, '', 'default');
	 submit_js_confirm("FINAL",sprintf(_("Are you sure want to final submit?"),true));
	end_form();
}
// ----------------------------------------------------------------------------------

end_page();

