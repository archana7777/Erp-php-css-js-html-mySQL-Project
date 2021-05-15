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
$oiling_packing=sr_furnace_veiw_display($woid,$coil_no);


br(1);

  br();
   	start_table(TABLESTYLE, "width='80%'");
	
	table_section_title("Oiling And Packing Details");
while ($item = db_fetch($oiling_packing))
{
	label_row(_(" Date"), sql2date($item['tran_date']));
	label_row(_(" Shift "), $shift_types[$item['shift_id']]);
	label_row(_(" Oil Tank No "), $item['oil_tank_no']);
	label_row(_(" Operator"), $item['operator_at_site']);
	label_row(_(" Supervisor"), $item['supervisor_at_site']);
	label_row(_(" Coil No"), $item['coil_no']);
	label_row(_(" Size"), $item['size']);
	label_row(_(" Class"), $item['product_class']);
	label_row(_(" Oiling Status"), $oiling_status[$item['oiling_status']]);
	label_row(_(" Coil Surface Condition"),($coil_suface_condition_status[$item['coil_surface_condition_status']]));
	label_row(_(" Final Packing Status"),($final_packing_status[$item['final_packing_status']]));
	label_row(_(" Net Weight of Coil In kgs"), $item['net_qty']);
	label_row(_(" Gross Weight In kgs"), $item['gross_qty']);
	
	label_row(_(" Remarks"), $item['remarks']);
}	
	end_table();


end_page(true, false, false, ST_WORKORDER, $woid);

