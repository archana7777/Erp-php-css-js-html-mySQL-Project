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
$page_security = 'SA_CHECK_LIST';
$path_to_root = "../..";
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");

include_once($path_to_root . "/maintenance/includes/db/check_list_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc"); 

/* $js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
if ($use_date_picker)
	$js .= get_js_date_picker(); */

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "Seperate Check List"), false, false, "", $js);



 /*if($_POST['SEARCH_ID']!=''){
	
	//$Ajax->activate($_POST['SEARCH_ID']);
	$Ajax->activate('_page_body');
	
} */
//-----------------------------------------------------------------------------------------------
if ($_POST['machine_id'] )
{
//display_error($_POST['to_date']);
$result=get_machine_details($_POST['machine_id'],$_POST['mac_fre'],$_POST["mc_problem_type"]);
}

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

machine_name_list_row(_("Machine Name:"), 'machine_id', null, false, true);
if(list_updated('machine_id')){
	
	$Ajax->activate('mac_fre');	
		$Ajax->activate('_page_body');
	
}
	maintenance_mc_analysis_type_row(_("Problem Type:"),'mc_problem_type',null,false);
	machine_frequency_name_list_row(_("Frequency Type:"), 'mac_fre',null, false, true,$_POST['machine_id']);




submit_cells('SEARCH_ID', _("Search"),'',_('Refresh Inquiry'), 'default');




end_row();
end_table();

//------------------------------------------------------------------------------------------------
div_start('trans_tbl');
start_table(TABLESTYLE);
if ($_POST['machine_id'] )
{
//display_error($_POST['to_date']);
$result=get_machine_details($_POST['machine_id'],$_POST['mac_fre'],$_POST["mc_problem_type"]);
}



$th = array(_("Sl No."),_("DETAILS"), _("VERIFIED"),
	_("REMARKS"));
table_header($th);


function get_trans($prevent_id,$type)
{

	$label = $prevent_id;
	$class ='';
	$id=$prevent_id;
	$icon = '';
	 $viewer = $path_to_root."testing/view/";
	if ($type == 'Prevent')
	hidden('Prevent_id',$prevent_id);
		$viewer .= "testing_summary_view.php?id=".$prevent_id;
	
	return viewer_link($label, $viewer, $class, $id,  $icon);
	
}




if( isset($_POST['SEARCH_ID'])){

$result=get_machine_details($_POST['machine_id'],$_POST['mac_fre'],$_POST["mc_problem_type"]);
$Ajax->activate('_page_body');
}

	
$k = 0; 
$i=1; //row colour counter

$counts = db_num_rows($result);

	if($counts > 0){

while ($myrow = db_fetch($result))
{

	alt_table_row_color($k);
    label_cell($i);

	label_cell($myrow["details"]);
	
	 ?>
	 <td align="center"><?php check_cells_td('', $row['check_id'].'_verified', 
			null, false, false); ?></td>
	 <?php
 label_cell(textarea_cells(null, "description"));

	
	end_row();
 
	$i++;
}
} else {
	echo "<td colspan='4'><center>No Records</center></td>";
	}
   textarea_row(_("Remarks:"), 'remarks', null, 40, 5);
	
	$machine_id = $_POST['machine_id'];
	$mac_fre = $_POST['mac_fre'];
	$mc_problem_type = $_POST['mc_problem_type'];
	
end_table(2);
echo '<center>';

label_cell(print_document_link($machine_id , _('Print'), true,'SA_CHECK_LIST',false,"printTable",$mac_fre,$mc_problem_type));

echo '</center>';
br();
div_end();
hidden('popup', @$_REQUEST['popup']);
end_form();
end_page();
?>

<link rel="stylesheet" href="<?php echo $path_to_root . "/testing/js/jquery-ui.css" ?>">
    <script src="<?php echo $path_to_root . "/testing/js/jquery-1.10.2.js" ?>"></script>
   <script src="<?php echo $path_to_root . "/testing/js/jquery-ui.js"?>"></script>
<script>

$('body').on('change','select[name=division_code]',function() { 
var division_code = $('select[name=division_code]').val();
//alert(division_code);

});
$("#printTable").click(function(){
	

	//alert("sdfds");
	var machine_id=$('[name=machine_id]').val();
	//alert(machine_id);
	var mac_fre=$('[name=mac_fre]').val();

//	alert(uom);
	$.ajax({
		type:"POST",
		url:'<?php echo $path_to_root . "/testing/view/ajax_get_summay_rail_report.php";?>',
		data: {machine_id : machine_id , mac_fre : mac_fre}
		//alert(data);
	}).done( function( data ){
		//$('#section_id').append(data);
	});
});

</script>
 
  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  </script>


