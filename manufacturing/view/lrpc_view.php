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
$page_security = 'SA_MANUF_STRANDING';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
page(_($help_context = "View LRPC Spo0ls"), true, false, "", $js);

//-------------------------------------------------------------------------------------------------
$woid = 0;
if ($_GET['trans_no'] != "")
{
	$woid = $_GET['trans_no'];
	$set_no=$_GET['set_no'];
}

display_heading($systypes_array[ST_WORKORDER] . " # " . $woid);

display_wo_details($woid, true);
br();
display_heading("Set No (".$set_no.") Details");
start_table(TABLESTYLE, "width='90%'");


$th = array(_("S.No"), _("Date"), _("Shift"), _("Operator"), _("Supervisor"), _("LRPC Line No"), _("Take Up No"), _("Machine Start Time"),_("Machine Stop Time"),_('Core Wire'),_("Outer 1"),_("Outer 2"),("Outer 3"),("Outer 4"),_("Outer 5"),_("Outer 6"),_("Length In Mtr"),_("Qty"),_("Reason"));
table_header($th);
$total = $k = 0;
$lrpc_details=lrpc_veiw_display($woid,$set_no);
$i=1;
$total=0;
while ($item = db_fetch($lrpc_details))
{
	$units=get_pickling_uom($item['stock_id']);
	
        alt_table_row_color($k);
        label_cell($i,'align=center');
        label_cell(sql2date($item['tran_date']));
        label_cell($shift_types[$item['shift_id']]);
		label_cell($item['operator_at_site']);
		label_cell($item['supervisor_at_site']);
		label_cell($item['lrpc_line_no'],'align=center');
	    label_cell($item['take_up_no'],'align=center');
	    label_cell($item['machine_start_time']);
		label_cell($item['machine_stop_time']);
		label_cell($item['core_wire']);
		label_cell($item['outer1']);
		label_cell($item['outer2']);
		label_cell($item['outer3']);
		label_cell($item['outer4']);
		label_cell($item['outer5']);
		label_cell($item['outer6']);
		qty_cell($item['length_in_mtr']);
        qty_cell($item['qty']);
		$total=$total+$item['qty'];
	  
		label_cell($lrpc_machine_stoppage_status[$item['reason']]);

        
        end_row();
$i++;	
}
label_row(_("Total"), number_format2($total,2), "align=right colspan=17", "align=right", 1);
end_table(1);





end_page(true, false, false, ST_WORKORDER, $woid);

