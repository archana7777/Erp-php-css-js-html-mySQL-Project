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
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");

//--------------------------------------------------------------------------------

function add_to_indent(&$order, $new_item, $new_item_qty, $available_qty,$required_date,$supplier_id)
{

    if ($order->find_cart_item($new_item))
         display_error(_("For Part :") . $new_item . " " . "This item is already on this document. You can change the quantity on the existing line if necessary.");

		 
	else
         $order->add_to_cart(count($order->line_items), $new_item, $new_item_qty, '','','',$available_qty,$required_date,$supplier_id);
}

function indent_display_order_header(&$order)
{
	global $Refs;
	global $Ajax;
	start_outer_table(TABLESTYLE2, "width='70%'"); // outer table
	table_section(1);
	//display_error(hie);die;
	//locations_group_list_row(_("Requested Location:"), 'StockLocation', null, false, true, false);
	//locations_group_list_row(_("Requested Location:"), 'StockLocation', null, false, true, $order->fixed_asset);
    date_row(_("Date:"), 'AdjDate', '', true);

	table_section(2, "50%");

	ref_row(_("Reference:"), 'ref', '', $Refs->get_next(ST_INDENTREQUEST, null, array('location'=>get_post('StockLocation'), 'date'=>get_post('AdjDate'))),
		 false, ST_INDENTREQUEST);

	end_outer_table(1); // outer table
}

function purchase_indent_display_order_header(&$order)
{
	global $Refs;
global $Ajax;
	start_outer_table(TABLESTYLE2, "width='70%'"); // outer table
	table_section(1);
	locations_group_list_row(_("Requested Location:"), 'StockLocation', null, false, true, false);
	//locations_group_list_row(_("Requested Location:"), 'StockLocation', null, false, true, $order->fixed_asset);
    date_row(_("Date:"), 'AdjDate', '', true);

	table_section(2, "50%");

	ref_row(_("Reference:"), 'ref', '', $Refs->get_next(ST_PURCHINDENTREQUEST, null, array('location'=>get_post('StockLocation'), 'date'=>get_post('AdjDate'))),
		 false, ST_INDENTREQUEST);

	end_outer_table(1); // outer table
}

function display_indent_items($title, &$order)
{
	global $path_to_root;
global $Ajax;
	display_heading($title);
    div_start('items_table');
	start_table(TABLESTYLE, "width='80%'");

	if ($order->fixed_asset) {
		$th = array(_("Item Code"), _("Item Description"),_("Unit"),_("Current Stock"), _("Required Quantity"),
		_("Required Date"), _("Supplier"));
	} else {
		$th = array(_("Item Code"), _("Item Description"),_("Unit"),_("Current Stock"), _("Required Quantity"),
		_("Required Date"),"");
		if ( count($order->line_items)) $th[] = '';
	}
	if ( count($order->line_items)) $th[] = '';

	table_header($th);
	$total = 0;
	$k = 0;  //row colour counter

	$low_stock = $order->check_qoh($_POST['StockLocation'], $_POST['AdjDate']);
	$id = find_submit('Edit');
	foreach ($order->line_items as $line_no=>$stock_item)
	{

		$total += ($stock_item->standard_cost * $stock_item->quantity);

		if ($id != $line_no)
		{
			if (in_array($stock_item->stock_id, $low_stock))
				start_row("class='stockmankobg'");	// notice low stock status
			else 
				alt_table_row_color($k);

    		if ($order->fixed_asset)
				label_cell($stock_item->stock_id);
      		else
				view_stock_status_cell($stock_item->stock_id);

			label_cell($stock_item->item_description);
			label_cell($stock_item->units);
			
			label_cell($stock_item->available_qty);
			qty_cell($stock_item->quantity, false, get_qty_dec($stock_item->stock_id));
			label_cell($stock_item->required_date);
			$supp_name=get_supplier_name_indent($stock_item->supplier_id);
			
			label_cell($supp_name);
    		
			//label_cell($stock_item->units);
			//amount_decimal_cell($stock_item->standard_cost);

			
      		if (!$order->fixed_asset) {
        		edit_button_cell("Edit$line_no", _("Edit"),	_('Edit document line'));
      		}
			delete_button_cell("Delete$line_no", _("Delete"), _('Remove line from document'));
			end_row();
		}
		else
		{
			indent_edit_item_controls($order, $line_no);
		}
	}

	if ($id == -1)
		indent_edit_item_controls($order);

	//label_row(_("Total"), number_format2($total,user_price_dec()), "align=right colspan=6", "align=right", 2);

    end_table();
	if ($low_stock)
		display_note(_("Marked items have insufficient quantities in stock as on day of adjustment."), 0, 1, "class='stockmankofg'");
	div_end();
}

function indent_edit_item_controls(&$order, $line_no=-1)
{
	global $Ajax;
	start_row();

	$dec2 = 0;
	$id = find_submit('Edit');
	if ($line_no != -1 && $line_no == $id)
	{
		$_POST['stock_id'] = $order->line_items[$id]->stock_id;
		$_POST['units'] = $order->line_items[$id]->units;
		$_POST['batch_code'] = $order->line_items[$id]->batch_code;
		$_POST['available_qty'] = $order->line_items[$id]->available_qty;
		$_POST['required_date'] = $order->line_items[$id]->required_date;
		$_POST['supplier_id'] = $order->line_items[$id]->supplier_id;
		$_POST['qty'] = qty_format($order->line_items[$id]->quantity, 
			$order->line_items[$id]->stock_id, $dec);
		//$_POST['std_cost'] = price_format($order->line_items[$id]->standard_cost);
		//$_POST['std_cost'] = price_decimal_format($order->line_items[$id]->standard_cost, $dec2);
		$_POST['units'] = $order->line_items[$id]->units;

		hidden('stock_id', $_POST['stock_id']);
		label_cell($_POST['stock_id']);
		label_cell($order->line_items[$id]->item_description, 'nowrap');
	    $Ajax->activate('items_table');
	}
	else
	{
		if ($order->fixed_asset)
			stock_disposable_fa_list_cells(null, 'stock_id', null, false, true, $order->line_items);
		else
			stock_costable_items_list_cells(null, 'stock_id', null, false, true);
			
			
			
			if (list_updated('stock_id')) {
			    $Ajax->activate('units');
			    $Ajax->activate('qty');
			    $Ajax->activate('available_qty');
				$Ajax->activate('_page_body');
		}
		

		
		
    	$item_info = get_item_edit_info($_POST['stock_id']);
		$dec = $item_info['decimals'];
   		$_POST['qty'] = number_format2(0, $dec);
		$_POST['std_cost'] = price_decimal_format($item_info["material_cost"], $dec2);
		$_POST['units'] = $item_info["units"];
		

	}
		label_cell($_POST['units'], '', 'units');
//text_cells_ex(null, "batch_code", 20,40,$_POST['batch_code'],null,null,null,true);
	if ($order->fixed_asset) {
		hidden('qty', -1);
		qty_cell(1, false, 0);
	} else
	
$available_qty=get_available_qty($_POST['stock_id'],$_POST['StockLocation']);
		$_POST['available_qty'] = $available_qty;
if($available_qty=='')
{
$available_qty=0;
}



label_cell($_POST['available_qty'], '', 'available_qty');
//batch_cells(null, 'available_qty',  $_POST['available_qty'],15, null, 2,null,'readonly');
//qty_cells(null, 'available_qty', $_POST['available_qty'], null, null, $dec);
//label_cells('',$available_qty, '', '','available_qty');
	//hidden('available_qty', $available_qty);
	
qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);
date_cells(null, 'required_date', '', null, 0, 0, 0);
//indent_supplier_list_cells(null, 'supplier_id', null, false, false);

	if ($id != -1)
	{
		button_cell('UpdateItem', _("Update"),
				_('Confirm changes'), ICON_UPDATE);
		button_cell('CancelItemChanges', _("Cancel"),
				_('Cancel changes'), ICON_CANCEL);
		hidden('LineNo', $line_no);
 		set_focus('qty');
	}
	else
	{
		submit_cells('AddItem', _("Add Item"), "colspan=2",
		    _('Add new item to document'), true);
	}

	end_row();
}

function indent_options_controls()
{
	  echo "<br>";
	  start_table();

	  textarea_row(_("Remarks"), 'memo_', null, 50, 3);

	  end_table(1);
}