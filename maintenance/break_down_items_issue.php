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
$page_security = 'SA_INVENTORYBRKDREQ';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/db/inventory_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/maintenance/includes/db/breakdown_items_request_db.inc");
include($path_to_root . "/includes/ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
simple_page_mode(true);
page(_($help_context = "Breakdown Maintenance Items Issue"));
//-----------------------------------------------------------------------------------
if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
	$_SESSION['ind_no'] = $trans_no;
}
if (!isset($_POST['date_']))
{
	$_POST['date_'] = new_doc_date();
	if (!is_date_in_fiscalyear($_POST['date_']))
		$_POST['date_'] = end_fiscalyear();
}

//--------------------------------------------------------------------------------------------------
function get_tot_stock($stock_id,$location){
$sql = "SELECT sum(qty) FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)." AND loc_code=".db_escape($location)."";
display_error($sql);
$res = db_query($sql);
$result = db_fetch_row($res);
if($result['0'] == ''){
return '0';
}else{
return $result['0'];
}
}

function display_breakdown_items_process($trans_no)
{
	
	div_start('grn_items');
    start_table(TABLESTYLE, "colspan=7 width='100%'");
    $th = array(_("Item Code"), _("Description"), _("Quantity"), _("Units"),_("Pending Quantity"),_("From Location"),_("Batch Code"), _("Issued Quantity"));
    table_header($th);
	$transfer_items = get_breakdown_req_items(ST_BREAKMAINTENTRY, $trans_no);
     $k = 0;
    /*show the line items on the order with the quantity being received for modification */
		while ($item = db_fetch($transfer_items))
		{
			
			
			
				$ind_result[] = $item;
				alt_table_row_color($k);

				//label_cell($item['stock_id']);
				label_cell(view_stock_status($item['stock_id'],'',false));
				
				//$_SESSION['ind_item'] = $item['stock_id'];
				//$_SESSION['IND']['ind_item'] = $item['stock_id'];
				hidden('trans_no',$trans_no);
				hidden('stock_id',$item['stock_id']);
				hidden('ref',$item['ref']);
				
				hidden('loc_code',$item['loc_code']);
				label_cell($item['description']);
				// label_cell($item['supp_name']);
				qty_cell($item['qty'], false, get_qty_dec($item['stock_id']));

			
				label_cell($item['units']);
				qty_cell($item['rem_qty'], false, get_qty_dec($item['stock_id']));
				
				locations_group_list_cells(null, 'from_location'.$item['stock_id'], null, false, false, false);
			
				text_cells_ex(null,'batch_code'.$item['stock_id'], 20,40,null,null,null,null,false);
			if($item['rem_qty']==0)
			{
			label_cell('0');	
			}
			else{
				 qty_cells(null,'qty_rec'.$item['stock_id'], get_qty_dec($item['stock_id']));
			}
			//hidden('qty_rec',$_POST['qty_rec']);
				end_row();
				
			
			
			//}
		$k++; }
   $_SESSION['BREAK'] = $ind_result;
   
     end_table();
	 div_end();
}
//--------------------------------------------------------------------------------------------------

if ( (isset($_POST['Indent']))|| isset($_POST['close']))
{
	
if (isset($_POST['close']))
{
	
		$close_indent = 1;
		$indent_status=1;
	/* foreach ($_SESSION['IND'] as $k => $items)
	{
		if($_POST['qty_rec'.$items['stock_id']]==0)
		{
		display_error("The issued quantity should not be  zero Please enter the quantity");
			
			return false;	
		}
	} */
}
	//display_error(json_encode($_SESSION['PREV']));die;
	foreach ($_SESSION['BREAK'] as $k => $items)
	{
		
	$batch_req=get_batch_req($items['stock_id']);
	
		if(($batch_req==1) &&($_POST['batch_code'.$items['stock_id']]==''))
		{
			display_error("batch code cannot empty");
			
			return false;
		}
		$av_qty=check_quantity_available_inventory_break($items['stock_id'],$_POST['batch_code'.$items['stock_id']],$_POST['from_location'.$items['stock_id']]);
		
	if($av_qty=='')
	{
		$av_qty=0;
	}
	
	
		if(($av_qty<input_num('qty_rec'.$items['stock_id'])))
		{
			display_error($items['stock_id']."----available quantity in stock----".$av_qty);
			
			return false;
		}
		$qt_data=get_breakdown_requested_qty($items[item_id]);
		$qty_rec=$qt_data+input_num('qty_rec'.$items['stock_id']);

		if($qty_rec>$items[qty])
		{
		display_error("Delivery quantity exceeds actuval quantity excceds");
			
			return false;	
		}
if($items[qty]==$qty_rec)
{
	$status=1;
}
else
{
	$status=0;
}
		$issue_id=add_breakdown_issue_entry(ST_BREAKMAINTISSUES,$_POST['trans_no'],$_POST['reference'],$_POST['issue_date'],$_POST['taken_by'],$_POST['remarks']);
	$today = Today();
if($_POST['qty_rec'.$items['stock_id']]>0){
add_stock_move(ST_BREAKMAINTISSUES,$items[stock_id],$issue_id,$_POST['from_location'.$items['stock_id']],$today,$_POST['reference'],-input_num('qty_rec'.$items['stock_id']),'','',$_POST['batch_code'.$items['stock_id']]);
$consumable_status=get_is_category_consumable($items[stock_id]);
	if($consumable_status==1)
	{
		$consumed_qty=input_num('qty_rec'.$items['stock_id']);
		$quantity=0;
	}else{
		$quantity=input_num('qty_rec'.$items['stock_id']);
	}
add_stock_move(ST_BREAKMAINTISSUES,$items[stock_id],$issue_id,$items['loc_code'],$today,$_POST['reference'],$quantity,'','',$_POST['batch_code'.$items['stock_id']],0,0,0,0,'',0,0,0,0,0,0,$consumed_qty);
}
		update_berak_down_issue_quantity($items[item_id],$items[stock_id],$qty_rec,$status,$close_indent,$_POST['from_location'.$items['stock_id']]);

	if (!isset($_POST['close']))
{

	$row=get_break_down_items_status($_POST['trans_no']);
	
	$flag=0;
	while($result=db_fetch($row))
	{
	
		if($result['status']==0)
		{
			$flag++;
			
		}
	}
	if($flag==0)
	{
		$indent_status=1;
	}
}
	update_break_down_items_issue_status($_POST['trans_no'],$indent_status,$_POST['remarks']);		
}


meta_forward($path_to_root . "/inventory/inquiry/breakdown_request_inquiry.php?type=1104");
}

//--------------------------------------------------------------------------------------------------

start_form(true);

hidden('trans_no',$trans_no);
$trans = get_breakdown_items_issues_from($trans_no);



echo "<br>";
start_table(TABLESTYLE2, "width='90%'");

start_row();
label_cells(_("Requested Reference"), $trans['ref'], "class='tableheader2'");
hidden('ref',$trans['ref']);
label_cells(_("Requested Date"), sql2date($trans['req_date']), "class='tableheader2'");
end_row();
start_row();
label_cells(_("Requested Location Name"), $trans['loc_name'], "class='tableheader2'");
	
end_row();

comments_display_row(ST_PREVENTITEM, $trans_no);

end_table();
start_table(TABLESTYLE2, "width='90%'");
ref_row(_("Issue Reference No:"), 'reference', '', $Refs->get_next(ST_BREAKMAINTISSUES, null, get_post('date_')), false, ST_BREAKMAINTISSUES);
date_row(_("Issue Date:"), 'issue_date', '', true);
//text_row(_("Taken By :"), 'taken_by', true, 34, 3);
text_row(_("Taken By:"), 'taken_by', null, 30, 60);

textarea_row(_("Remarks:"), 'remarks', null, 34, 3);
end_table();
display_heading(_("Items"));
display_breakdown_items_process($trans_no);
//Individual assign weight(addmore)
//display_coil_receive_items();

echo '<br>';
submit_center_first('Indent', _("Process "), '', 'default');
submit_center_last('close', _("Process And Close request "), '', 'default');
end_form();

//--------------------------------------------------------------------------------------------------
end_page();
