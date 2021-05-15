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
$page_security = 'SA_MANUF_DRAWING';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
page(_($help_context = "View Drawing Coils"), true, false, "", $js);

//-------------------------------------------------------------------------------------------------
$woid = 0;
if ($_GET['trans_no'] != "")
{
	$woid = $_GET['trans_no'];
	$spool_code=$_GET['spool_code'];
}

display_heading($systypes_array[ST_WORKORDER] . " # " . $woid);

display_wo_details($woid, true);
br();
display_heading("Spool Code (".$spool_code.") Details");

start_table(TABLESTYLE, "width='90%'");


$th = array(_("S.No"), _("Date"), _("Shift"), _("Operator"), _("Supervisor"), _("Raw Material"), _("Coil No"), _("Heat No"),_("Quantity"),_("UOM"),_("Machine"),("Coil In Time"),("Coil Out Time"),_("Remarks"));
table_header($th);
$total = $k = 0;
$pickling_details = drawing_veiw_display($woid,$spool_code);
$i=1;
$total=0;
while ($item = db_fetch($pickling_details))
{
	$units=get_pickling_uom($item['stock_id']);
	
        alt_table_row_color($k);
        label_cell($i,'align=center');
        label_cell(sql2date($item['tran_date']));
        label_cell($shift_types[$item['shift_id']]);
		label_cell($item['operator_at_site']);
		label_cell($item['supervisor_at_site']);
		label_cell($item['description']);
	    label_cell($item['coil_no']);
	    label_cell($item['heat_no']);
        qty_cell($item['qty'], false, get_qty_dec($item['stock_id']));
		$total=$total+$item['qty'];
		label_cell($units[0]);
	    label_cell($item['mac_code']);
		label_cell($item['coil_in_time']);
		label_cell($item['coil_out_time']);
		label_cell($item['remarks']);

        
        end_row();
$i++;	
}
label_row(_("Total"), number_format2($total,2), "align=right colspan=8", "align=right", 1);
end_table(1);


br();
br();





end_page(true, false, false, ST_WORKORDER, $woid);

