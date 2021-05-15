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
$layer_winder=layer_winder_veiw_display($woid,$coil_no);


br(1);

  br();
   	start_table(TABLESTYLE, "width='80%'");
	
	table_section_title("Layer Winder Coil Details");
while ($item = db_fetch($layer_winder))
{
	label_row(_(" Date"), sql2date($item['tran_date']));
	label_row(_(" Shift "), $shift_types[$item['shift_id']]);
	label_row(_(" Layer Winder No "), $item['layer_winder_no']);
	label_row(_(" Operator"), $item['operator_at_site']);
	label_row(_(" Supervisor"), $item['supervisor_at_site']);
	label_row(_(" SET No"), $item['set_no']);
	label_row(_(" Take Up No"), $item['take_up_no']);
	label_row(_(" Size in mm"), $item['size']);
	label_row(_(" Remaining Length"), 0);
	label_row(_(" Start Time"),($item['start_time']));
	label_row(_(" End Time"),($item['stop_time']));
	label_row(_(" Coil No"), $item['winder_coil_no']);
	label_row(_(" Length In Meter"), $item['length_in_mtr']);
	label_row(_(" Calculated Weight In Kgs"), $item['qty']);
    label_row(_(" Coil Status"), $layer_winder_coil_status[$item['coil_status']]);
	label_row(_(" Remarks"), $item['remarks']);
}	
	end_table();


end_page(true, false, false, ST_WORKORDER, $woid);

