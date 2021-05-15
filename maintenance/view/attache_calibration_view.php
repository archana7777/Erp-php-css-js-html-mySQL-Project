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
$page_security = 'SA_MACHINE_CALIB_ATTACH_DOC';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");

page(_($help_context = "View Machine Calibration"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/tico/includes/db/tico_test_item_issue_inquiry_db.inc");

display_heading("Machine Calibration Entry");

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

br(1);
// Sandeep

function get_all_machine_information_view($trans_no)
{
	$sql = "SELECT mce.* ,if(mce.calibration_date='0000-00-00','',DATE_FORMAT(mce.calibration_date,'%d/%m/%Y')) as calibration_date,if(mce.validity_start_date='0000-00-00','',DATE_FORMAT(mce.validity_start_date,'%d/%m/%Y')) as validity_start_date,if(mce.validity_end_date='0000-00-00','',DATE_FORMAT(mce.validity_end_date,'%d/%m/%Y')) as validity_end_date,meq.mac_eqp,m.mac_code FROM ".TB_PREF."machine_calibration as mce LEFT JOIN ".TB_PREF."machine as m ON m.id=mce.machine_id JOIN ".TB_PREF."machine_equipment as meq ON meq.id=m.mac_eqp  WHERE mce.id=".db_escape($trans_no);
	
	$result=db_query($sql, "could not get Machine Calibration");

	return $result;
}


$test_items = get_all_machine_information_view($trans_no);

$k = 0;

    start_table(TABLESTYLE, "width='90%'");
    $th = array(_('Machine name'),_('Machine Code'),_('Calibration Date'), _('Validity Start Date'), _('Validity End Date'), _('Remarks'));
    table_header($th);

while ($myrow = db_fetch($test_items))
{
	label_cell($myrow["mac_eqp"]);
	label_cell($myrow["mac_code"]);
	label_cell($myrow["calibration_date"]);
	label_cell($myrow["validity_start_date"]);
	label_cell($myrow["validity_end_date"]);
	label_cell($myrow["remarks"]);
   
}
end_table(1);
end_page(true, false, false, ST_MACHINE_INFO, $trans_no);
