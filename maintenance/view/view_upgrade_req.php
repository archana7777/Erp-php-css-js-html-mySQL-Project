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

page(_($help_context = "Upgrade Requst View"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");


if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
	
}
	
	//display_error($trans_no);
	
function get_all_upgrade_request_details($trans_no)
{
$sql="SELECT up_ent.*, CONCAT(meq.mac_eqp,'-',m.mac_code) as mac_eqp FROM ".TB_PREF."machine as m LEFT JOIN ".TB_PREF."machine_equipment as meq ON m.mac_eqp=meq.id
left join ".TB_PREF."upgrade_req_entry up_ent on up_ent.machine_id=m.id
where up_ent.id=".db_escape($trans_no)."";
return $res=db_query($sql);
	
}


function get_transaction_details($trans_no)

{ 
	$sql="select * from ".TB_PREF."upgrade_request_details where selected_id=".db_escape($trans_no)." ";
	
	return $res=db_query($sql);
	
}
function get_item_for_upgrade_maintenance_id($trans_no)
{
$sql="SELECT req.ref,stock.description,items.stock_id,items.qty,items.qty_rec FROM ".TB_PREF."upgrade_material_request req 
left join ".TB_PREF."upgrade_request_items items on items.mt_req_id=req.id
left join stock_master stock on stock.stock_id=items.stock_id 
left join upgrade_req_entry entry on entry.id=req.schedule_id 
WHERE req.schedule_id=".db_escape($trans_no);
$result=db_query($sql, "could not get Machine Upgrade");

	return $result;
}
function get_issued_entry_view($req_id)
{
	$sql="SELECT * FROM ".TB_PREF."upgrade_material_request as req INNER JOIN ".TB_PREF."upgrade_issue_entry as entry ON req.id=entry.req_id WHERE req.schedule_id=".db_escape($req_id)."";
	$result=db_query($sql, "could not get issue entry details!");
	return $result;
}
function get_issued_items_details_for_view($entry_id)
{
		$sql="SELECT sm.*,stm.description FROM ".TB_PREF."stock_moves as sm INNER JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE sm.trans_no=".db_escape($entry_id)." AND sm.type=1111 AND sm.qty>0";
	$result=db_query($sql, "could not get Machine issue item details!");

	return $result;
}

?>
<<center>
<table border="2" width="80%"  style="border-collapse:collapse;">
<tr>
<td align="center"><?php
echo "<img src='$path_to_root/company/0/images/".$company_logo."' style='float:center;' alt='Rishi Tech'/>";
//echo "<img src='$path_to_root/themes/exclusive_db/images/aspen.png'  style='float:center;' alt='Rishi Tech'/>";
?></td>
<td align='center'><p style="font-size:30px;text-align:center;"><?php echo $_SESSION['SysPrefs']->prefs['coy_name'];
 ?></p>

<p style="font-size:12px;text-align:center;"> <?php echo $_SESSION['SysPrefs']->prefs['postal_address'];
 ?></p>



</tr>
</table>

</center>
  
<?php
	echo "<br>";
echo "<br>";
echo"<center>";
echo "<font size='4' text-align:'center'>";
echo "Machine Upgrade Material Details.#$trans_no";
//display_heading("Blown Film Jobcard " . " #$trans_no","style=''");
echo "</font>";
echo"</center>";
echo "<br>";

br();
br();


	$res1=get_all_upgrade_request_details($trans_no);
		start_table(TABLESTYLE, "width='95%'");
		$th = array(_("Reference"),_("Machine Name"),_("Details"));
		table_header($th);
		 while($result1=db_fetch($res1))
 {
		label_cell($result1['ref']);
		label_cell($result1['mac_eqp']);
		label_cell($result1['details']);
		
		
		end_row();
 }

end_table(1);
		
		
		//table 2
		$res3=get_transaction_details($trans_no);
		start_table(TABLESTYLE, "width='95%'");
		$th = array(_("Start date"),_("End Date"),_("Materials Required"),_("Remarks"),);
		table_header($th);
		 while($result3=db_fetch($res3))
 {
	global $follow_up_status ;
	$status=$follow_up_status[$result3['status']];
		label_cell($result3['start_date'],'align=center');
		label_cell($result3['end_date'],'align=center');
		label_cell($result3['materials']);
		label_cell($result3['remarks']);
		
		end_row();
 }

end_table(1);
$preventive_data=get_item_for_upgrade_maintenance_id($trans_no);
display_heading("Requested Item Details For Machine Upgrade");
start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Reference"),_("Item Code"),_("Item Name"),_("Requested Qty"),_("Issued Qty"));
    table_header($th);
	while ($test = db_fetch($preventive_data))
   {
    alt_table_row_color($k);
	label_cell($test['ref'],'align="left"');
	label_cell($test['stock_id'],'align="left"');
   	label_cell($test['description'],'align="left"');
	qty_cell($test['qty'],'align="center"');
	qty_cell($test['qty_rec'],'align="center"');
	
   }
end_table();
br();
br(2);
display_heading("Issued Items Details");


$issue_det = get_issued_entry_view($trans_no);
start_table(TABLESTYLE, "width='70%'");
		$th = array(_("Reference"),_("Item Code"),_("Item Name"),_("Issued  Qty"),_("Taken By"),_("Issued Date"));
		table_header($th);
while ($entry_dt=db_fetch($issue_det)){
	
	$item_det = get_issued_items_details_for_view($entry_dt['issue_id']);
	$k = 0;
	
		
	while ($issue = db_fetch($item_det))
	{
		
		alt_table_row_color($k);
		label_cell($entry_dt['reference'],'align="left"');
		label_cell($issue['stock_id'],'align="left"');
		label_cell($issue['description'],'align="left"');
		qty_cell($issue['qty'],'align="center"');
		label_cell($entry_dt['taken_by'],'align="center"');
		label_cell(sql2date($entry_dt['issue_date']),'align="center"');
	}
}
	end_table();

?>
<center>
<input type="button" value="Print" id="tab" class="no-print" onclick="window.print();" style="background-color:#9ec4c2;"> 
</center>

 <style>
.sub{
	 border:1px solid black;
	 border-collapse:collapse;
	 padding:10px;
 }
 @media print
{    
    .no-print, .no-print *
    {
        display: none !important;
    }
}

 </style>

<?php

//end_page(true, false, false, ST_SAMPLE_TEST_INQ, $trans_no);
