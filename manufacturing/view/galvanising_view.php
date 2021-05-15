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
$page_security = 'SA_MANUFTRANSVIEW';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
page(_($help_context = "View Work Order"), true, false, "", $js);

//-------------------------------------------------------------------------------------------------
$woid = 0;
if ($_GET['trans_no'] != "")
{
	$woid = $_GET['trans_no'];
	$coil_no=$_GET['coil_no'];
}

display_heading($systypes_array[ST_WORKORDER] . " # " . $woid);
display_wo_details($woid, true);
br();

$item=sr_galvanizing_veiw_display($woid,$coil_no);


br(1);

  br();
	start_outer_table(TABLESTYLE2);
	table_section(1);
	table_section_title("Incoming Material");
	label_row(_("Date"), $item['tran_date']);
	label_row(_("Shift"), $item['shift_id']);
	label_row(_("GL Machine Id"), $item['shift_id']);
	label_row(_("Operator"),$item['operator_at_site']);
	label_row(_("Supervisor"),$item['supervisor_at_site']);
	label_row(_("Coil/Spool Code"),$item['rm_coil_no']);
	
	label_row(_("Size"),$item['size1']);
	label_row(_("Length"),$item['length_in_mtr']);
	label_row(_("Weight"),$item['qty']);
	label_row(_("Coil In Time"), $item['coil_in_time']);
	label_row(_("Coil Out Time"), $item['coil_out_time']);

	

	
	table_section(2);
	table_section_title("Outgoing Spool Details");
	label_row(_("Final Coil No"),$item['coil_no']);
	label_row(_("Size"),$item['size2']);
	label_row(_("Produced Length"),$item['rm_length_in_mtr']);
    label_row(_("Calculated Weight In Kgs"),$item['rm_qty']);
	label_row(_("Coil In Time"),$item['rm_in_time']);
	label_row(_("Coil Out Time"),$item['rm_out_time']);
	label_row(_("Remarks  "),$item['remarks']);
	
		
	end_outer_table();


end_page(true, false, false, ST_WORKORDER, $woid);

