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
$page_security = 'SA_INDENTREQUEST';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/db/inventory_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");


include($path_to_root . "/includes/ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
simple_page_mode(true);
page(_($help_context = "Indent Request Process"));
//-----------------------------------------------------------------------------------
if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
	$_SESSION['ind_no'] = $trans_no;
}

//--------------------------------------------------------------------------------------------------

function display_indent_items_process($trans_no)
{
	
	div_start('grn_items');
    start_table(TABLESTYLE, "colspan=7 width='100%'");
    $th = array(_("Item Code"), _("Description"),_("Supplier"), _("Quantity"), _("Units"),_("Remaining Quantity"),_("Issued Quantity"));
    table_header($th);
	$transfer_items = get_indent_dept_issues(ST_INDENTREQUEST, $trans_no);
     $k = 0;
    /*show the line items on the order with the quantity being received for modification */
		while ($item = db_fetch($transfer_items))
		{
			
			
			
				$ind_result[] = $item;
				alt_table_row_color($k);

				label_cell($item['stock_id']);
				
				//$_SESSION['ind_item'] = $item['stock_id'];
				//$_SESSION['IND']['ind_item'] = $item['stock_id'];
				hidden('trans_no',$trans_no);
				hidden('stock_id',$item['stock_id']);
				
				hidden('loc_code',$item['loc_code']);
				label_cell($item['description']);
				label_cell($item['supp_name']);
				qty_cell($item['qty'], false, get_qty_dec($item['stock_id']));

			
				label_cell($item['units']);
				qty_cell($item['rem_qty'], false, get_qty_dec($item['stock_id']));
				//locations_group_list_cells(null, 'from_location'.$item['stock_id'], null, false, false, false);
			//text_cells_ex(null,'batch_code'.$item['stock_id'], 20,40,null,null,null,null,false);
			//if($item['rem_qty']==0)
			//{
			//label_cell('0');	
			//}
			//else{
				// qty_cells(null,'qty_rec'.$item['stock_id'], get_qty_dec($item['stock_id']));
			//}
			//hidden('qty_rec',$_POST['qty_rec']);
qty_cell($item['qty_rec'], false, get_qty_dec($item['stock_id']));
				end_row();
				
			
			
			//}
		$k++; }
   $_SESSION['IND'] = $ind_result;
   
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
}
	//display_error(json_encode($_SESSION['IND']));die;
	foreach ($_SESSION['IND'] as $k => $items)
	{
		
	$batch_req=get_batch_req($items['stock_id']);
	
		
		if(($batch_req==1) &&($_POST['batch_code'.$items['stock_id']]==''))
		{
			display_error("batch code cannot empty");
			
			return false;
		}
		$av_qty=check_quantity_available($items['stock_id'],$_POST['batch_code'.$items['stock_id']],$_POST['from_location'.$items['stock_id']]);
		
	if($av_qty=='')
	{
		$av_qty=0;
	}
	
	
		if(($av_qty<$_POST['qty_rec'.$items['stock_id']]))
		{
			display_error($items['stock_id']."----available quantity in stock----".$av_qty);
			
			return false;
		}
		$qt_data=get_qty($items[trans_id]);
$qty_rec=$qt_data+$_POST['qty_rec'.$items['stock_id']];

		if($qty_rec>$items[qty])
		{
		display_error("elivery quantity exceeds actuval quantity excceds");
			
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
	$today = Today();
	
add_stock_move(ST_INDENTREQUEST,$items[stock_id],$items[trans_no],$_POST['from_location'.$items['stock_id']],$today,$items[reference],-$_POST['qty_rec'.$items['stock_id']],'','',$_POST['batch_code'.$items['stock_id']]);
add_stock_move(ST_INDENTREQUEST,$items[stock_id],$items[trans_no],$items['loc_code'],$today,$items[reference],$_POST['qty_rec'.$items['stock_id']],'','',$_POST['batch_code'.$items['stock_id']]);

		update_indent_quantity($items[trans_id],$items[stock_id],$qty_rec,$status,$close_indent,$_POST['from_location'.$items['stock_id']]);

	if (!isset($_POST['close']))
{

	$row=get_items_status($_POST['trans_no']);
	
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
	update_indent_status($_POST['trans_no'],$indent_status);		
}


meta_forward($path_to_root . "/sales/inquiry/indent_requests_inquiry.php?type=95");
}

//--------------------------------------------------------------------------------------------------

start_form();

hidden('trans_no',$trans_no);
$trans = get_indent_issues_from($trans_no);



echo "<br>";
start_table(TABLESTYLE2, "width='90%'");

start_row();
label_cells(_("Reference"), $trans['reference'], "class='tableheader2'");
label_cells(_("Date"), sql2date($trans['tran_date']), "class='tableheader2'");
end_row();
start_row();
label_cells(_("Request Location Name"), $trans['loc_name'], "class='tableheader2'");

end_row();

comments_display_row(ST_INDENTREQUEST, $trans_no);

end_table();

display_heading(_("Items"));
display_indent_items_process($trans_no);
//Individual assign weight(addmore)
//display_coil_receive_items();

echo '<br>';
//submit_center_first('Indent', _("Process "), '', 'default');
//submit_center_last('close', _("Process And Close Indent "), '', 'default');
end_form();

//--------------------------------------------------------------------------------------------------
end_page();
