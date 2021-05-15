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
$page_security = 'SA_MACHINE_ATTACH_DOC';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");

page(_($help_context = "View Machine Master"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/tico/includes/db/tico_test_item_issue_inquiry_db.inc");

display_heading("Machine Master");

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

br(1);

function get_all_machine_information_view($selected_id)
{
	$sql = "SELECT 
   location.location_name as location,lc_grp.loc_group_name as location_group,
machine.*,eqp.mac_eqp as mac_eqp,capacity.mac_cap aS mac_cap,make.mac_make as mac_make,GROUP_CONCAT(fre.mac_fre) AS mac_fre,if(machine.warranty_exp_date='0000-00-00','',DATE_FORMAT(machine.warranty_exp_date,'%d/%m/%Y')) AS warranty_exp_date
FROM
   ".TB_PREF."machine machine
LEFT JOIN
    ".TB_PREF."location_groups lc_grp ON lc_grp.id=machine.loc_group_id
LEFT JOIN
    ".TB_PREF."locations location ON location.loc_code=machine.file_location
INNER JOIN
    ".TB_PREF."machine_capacity capacity ON machine.mac_cap=capacity.id
INNER JOIN
    ".TB_PREF."machine_equipment eqp ON machine.mac_eqp=eqp.id
INNER JOIN
    ".TB_PREF."machine_frequency fre ON FIND_IN_SET(fre.id,machine.mac_fre)
INNER JOIN
   ".TB_PREF."machine_make make ON machine.mac_make=make.id WHERE machine.id=".db_escape($selected_id);

	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}


$test_items = get_all_machine_information_view($trans_no);

$k = 0;

    start_table(TABLESTYLE, "width='90%'");
    $th = array(_('Machine Code'),_('Machine Model No'),_('Machine Equipment'),_('Machine Make'),_('Machine Capacity'),_('Machine Frequency'),_('Warranty Type'),_('Warranty Date'),_('Location Group'),_('Locations'), _('Description'));
    table_header($th);

while ($myrow = db_fetch($test_items))
{
	label_cell($myrow["mac_code"]);
	label_cell($myrow["mac_model_no"]);
	label_cell($myrow["mac_eqp"]);
	label_cell($myrow["mac_make"]);
	label_cell($myrow["mac_cap"]);
	label_cell($myrow["mac_fre"]);
	label_cell($wrarranty_type[$myrow["warranty_type"]]);
	label_cell($myrow["warranty_exp_date"]);
	label_cell($myrow["location_group"]);
	label_cell($myrow["location"]);
	label_cell($myrow["remarks"]);
   
}
end_table(1);
end_page(true, false, false, ST_MACHINE_INFO, $trans_no);
