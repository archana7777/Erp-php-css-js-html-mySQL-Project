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
$page_security = 'SA_MANUFRECEIVE';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/inventory.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/gl/includes/db/gl_db_bank_trans.inc");

include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Produce Semi Finished / Finished Items From Work Order"), false, false, "", $js);

if (isset($_GET['trans_no']) && $_GET['trans_no'] != "")
{
	$_POST['selected_id'] = $_GET['trans_no'];
}

//--------------------------------------------------------------------------------------------------


$wo_details = get_work_order($_POST['selected_id'], true);

if ($wo_details === false)
{
	display_error(_("The order number sent is not valid."));
	exit;
}


display_wo_details($_POST['selected_id']);

br();br();
//-------------------------------------------------------------------------------------

start_form();

hidden('selected_id', $_POST['selected_id']);

div_start('details');

$work_order_no = get_post('selected_id');
if (!$work_order_no)
	unset($_POST['_tabs_sel']); // force settings tab for new customer

$product_type=get_product_type_from_work_order($work_order_no);
if($product_type==1)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'lrpc_stranding' => array(_('&LRPC / Stranding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'layer_winding' => array(_('Layer &Winding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'oiling_packing' => array(_('Oiling and &Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==2)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'lrpc_stranding' => array(_('&LRPC / Stranding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'layer_winding' => array(_('Layer &Winding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'extruder' => array(_('&Extruder'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'extruder_coiling' => array(_('Extruder &Coiling'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'no_oiling_only_packing' => array(_('Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==3)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'oiling_packing_spring_wire' => array(_('Oiling and &Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==4)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'srfurnace' => array(_('S&R Furnace'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'no_oiling_only_packing' => array(_('Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==5)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'lrpc_stranding' => array(_('&LRPC / Stranding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'srfurnace_concrete' => array(_('S&R Furnace'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'no_oiling_only_packing' => array(_('No Oiling and & Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==6)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'galvanising' => array(_('G&alvanising'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	//	'galvanised_packing' => array(_('No Oiling & Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==7)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'galvanising' => array(_('G&alvanising'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'lrpc_stranding_earth_stay' => array(_('&LRPC / Stranding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'no_oiling_only_packing' => array(_('No Oiling & Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==8)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'galvanising' => array(_('G&alvanising'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'lrpc_stranding_earth_stay' => array(_('&LRPC / Stranding'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'no_oiling_only_packing' => array(_('No Oiling & Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
if($product_type==9)
{
	$tabs = 
	array(
		'pickling' => array(_('P&ickling'), (user_check_access('SA_MANUF_PICKLING') ? $work_order_no : null)),
		'drawing' => array(_('D&rawing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
		'galvanising' => array(_('G&alvanising'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	//	'galvanised_packing' => array(_('No Oiling & Only Packing'), (user_check_access('SA_SALESPRICE') ? $work_order_no : null)),
	);
}
	
tabbed_content_start('tabs', $tabs);

	switch (get_post('_tabs_sel')) {
		case 'pickling':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/pickling.php");
			break;
		case 'drawing':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/drawing.php");
			break;
		case 'lrpc_stranding':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/lrpc_stranding.php");
			break;
		case 'lrpc_stranding_earth_stay':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/lrpc_stranding_earth_stay.php");
			break;	
		case 'layer_winding':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/layer_winding.php");
			break;
		case 'oiling_packing':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/oiling_packing.php");
			break;
		case 'oiling_packing_spring_wire':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/oiling_packing_spring_wire.php");
			break;			
		case 'srfurnace':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/srfurnace.php");
			break;
		case 'srfurnace_concrete':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/srfurnace_concrete.php");
			break;	
		case 'galvanising':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/galvanising.php");
			break;
		case 'galvanised_packing':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/galvanised_packing.php");
			break;	
		case 'no_oiling_only_packing':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/no_oiling_only_packing.php");
			break;
		case 'extruder':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/extruder.php");
			break;
		case 'extruder_coiling':
			$_GET['trans_no'] = $work_order_no;
			$_GET['page_level'] = 1;
			include_once($path_to_root."/manufacturing/extruder_coiling.php");
			break;				
	};

br();
tabbed_content_end();

div_end();

end_form();

end_page();

