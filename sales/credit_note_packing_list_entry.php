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
$page_security = 'SA_SALESCREDIT_PACKING';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = "Packing List Against Customer Credit Note"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/sales/includes/db/credit_note_packing_list_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

check_db_has_sales_credit_note_pending_packing_items(_("There are no pending packing list for credit note items in the system."));

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
	
	$result = get_credit_packing_list_coil_nos($selected_parent);
	div_start('bom');
	start_table(TABLESTYLE, "width='60%'");
	$th = array(_("Coil Number"),_("No of Packages"), _("Remarks"),'','');
	table_header($th);

	$k = 0;
	while ($myrow = db_fetch($result))
	{

		alt_table_row_color($k);

		label_cell($myrow["coil_number"]);
		qty_cell($myrow["no_of_packages"], false, 2);
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
		
	$grn_sqm=get_total_no_of_packages_in_credit_note($selected_parent); //grn quantity 

	$grn_det=get_credit_packing_list_item_code($selected_parent);
	
	$sql="select coil_no_req from ".TB_PREF."stock_master where stock_id=".db_escape($grn_det["stock_id"]);
	
	$result = db_query($sql, "Could not retreive item type");
	$row = db_fetch_row($result);
	$packing_list_required=$row[0];
	
	
	
	if ($packing_list_required==1)
	{
		
		$packing_weight=get_credit_packing_list_total_weight($selected_parent);
		
		if($grn_sqm<=$packing_weight){
		
			update_final_submit_sales_credit_packing($selected_parent);
			display_notification(_('Selected packing list has been updated'));
			$Mode = 'RESET';
		}else
			{
				
			display_error("Packing list no of packages and credit note no of packages are not equal! credit note no of packages is ".$grn_sqm." and packing no of packages is ".$packing_weight);
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
	
	
	if (!check_num('no_of_packages', 0))
	{
		display_error(_("The number of packages entered must be numeric and greater than zero."));
		set_focus('no_of_packages');
		return;
	}
	
	elseif (strlen($_POST['coil_number']) == 0) 
	{
		display_error( _('The coil number cannot be empty'));
		set_focus('coil_number');
		return;
	}
	

	 $stock_coil_pass_no_check=check_exist_credit_packing_list_Coil_no_in_stock($_POST['coil_number']);
   if($stock_coil_pass_no_check ==0)
   {
	display_error("Coil Number does not exists in stock!");
	set_focus('coil_number');
	return false;
   }
  
	
	$packing_coil_no_check=check_exist_sales_credit_packing_list_coil_no($_POST['coil_number']);
   if($packing_coil_no_check >0)
   {
	display_error("Coil Number already exists in packing list!");
	set_focus('coil_number');
	return false;
   }
	
	$grn_sqm=get_total_no_of_packages_in_credit_note($selected_parent);  //grn quantity  either sqm or 
	$grn_det=get_credit_packing_list_item_code($selected_parent);
	
	$sql1="SELECT coil_no_req FROM ".TB_PREF."stock_master WHERE stock_id=".db_escape($grn_det["stock_id"]);
	
	$result1 = db_query($sql1, "Could not retreive item type");
	$row1 = db_fetch_row($result1);
	$packing_list_required=$row1[0];
	
	
	
	if ($packing_list_required==1)
	{
		$packing_weight=get_credit_packing_list_total_weight($selected_parent);
		$packing_weight1=$packing_weight+$_POST['no_of_packages'];
		
		if($packing_weight1>$grn_sqm)
		{
		display_error(_("The total no of packages is more than the credite note no of packages And the credit note no of packages is ".$grn_sqm));
		set_focus('no_of_packages');
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
		date_default_timezone_set('Asia/Kolkata');
		$process_date = date('Y-m-d');
		
		update_credit_packing_list_coil_number($selected_roll_number,$_POST['coil_number'], $_POST['no_of_packages'],$_POST['remarks'],$process_date);
		display_notification(_('Selected coil no has been updated'));
		$Mode = 'RESET';
	}
	else
	{
		
		date_default_timezone_set('Asia/Kolkata');
		$process_date = date('Y-m-d');

		add_credit_packing_list_coil_number($selected_parent, $_POST['coil_number'], $_POST['no_of_packages'],$_POST['remarks'],$process_date);
				display_notification(_("A new coil number has been added to the packing list."));
				$Mode = 'RESET';
	}
	
			
}

//--------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	delete_credit_packing_list_coil_no($selected_id);

	display_notification(_("The coil number has been deleted from packing list"));
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

credit_note_pending_packing_list_items_cells(_("Select a Packing List item:"), 'stock_id', null, false, true);


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
			$myrow = get_credit_packing_list_item_coil_no($selected_id);

			$_POST['coil_number'] = $myrow["coil_number"];
			$_POST['remarks'] = $myrow["remarks"]; // by Tom Moulton
			$_POST['no_of_packages'] = $myrow["no_of_packages"];
			
		}
		hidden('selected_id', $selected_id);
	}
	
	ref_cells(_("Coil Number:<b style='color:red;'>*</b>"), 'coil_number',null,null,null,true);
    //text_row(_("Coil Number:<b style='color:red;'>*</b>"), "coil_number", $_POST['coil_number'], 20, 50);
	
	if(!empty($_POST['coil_number'])){
		
		$roll_info=get_credit_coil_number_info_from_stock_moves($_POST["coil_number"]);
		$_POST['heat_no']=$roll_info["heat_no"];
		$_POST['length']=$roll_info["length"];
		$_POST['qty']=$roll_info["qty"];
		$_POST['width_dia']=$roll_info["width_dia"];
		$Ajax->activate('heat_no');
		$Ajax->activate('width_dia');
		$Ajax->activate('length');
		$Ajax->activate('qty');
		$Ajax->activate('_page_body');
	}
	
	start_row();
		label_cells(_("Qunatity:"),$_POST['qty'],'class="label"');
		hidden('qty',$_POST['qty']);
	end_row();
	
	start_row();
		label_cells(_("Heat No.:"),$_POST['heat_no'],'class="label"');
		hidden('heat_no',$_POST['heat_no']);
	end_row();
	
	start_row();
		label_cells(_("Width:"),$_POST['width_dia'],'class="label"');
		hidden('width_dia',$_POST['width_dia']);
	end_row();
	
	start_row();
		label_cells(_("Length:"),$_POST['length'],'class="label"');
		hidden('length',$_POST['length']);
	end_row();
	
	label_cells(_("No of Packages "), '1','','', 'no_of_packages');
	hidden('no_of_packages', 1);
	
	//qty_cells(_("No of Packages"), 'no_of_packages', null, null, null, $dec);
	
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

