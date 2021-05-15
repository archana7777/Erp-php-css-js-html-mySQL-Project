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
$page_security = 'SA_MANUFWMTMUPDATE';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");


include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/db_pager.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
if (isset($_GET["trans_no"]))
{
	$selected_id = $_GET["trans_no"];
	$_SESSION['page_title'] = _("Batch Pickling Report Edit");
}else
$_SESSION['page_title'] = _("Batch Pickling Report Entry");
page($_SESSION['page_title'], false, false, "", $js);
		
		$date=$_POST["date"];
		$shift=$_POST["shift"];
		$acid_bath_1_hcl_conc_ov=$_POST["acid_bath_1_hcl_conc_ov"];
		$acid_bath_1_fe_ov=$_POST["acid_bath_1_fe_ov"];
		$acid_bath_2_hcl_conc_ov=$_POST["acid_bath_2_hcl_conc_ov"];
		$acid_bath_2_fe_ov=$_POST["acid_bath_2_fe_ov"];
		$acid_bath_3_hcl_conc_ov=$_POST["acid_bath_3_hcl_conc_ov"];
		$acid_bath_3_fe_ov=$_POST["acid_bath_3_fe_ov"];
		$acid_bath_1_hcl_conc_result=$_POST["acid_bath_1_hcl_conc_result"];
		$acid_bath_1_fe_result=$_POST["acid_bath_1_fe_result"];
		$acid_bath_2_hcl_conc_result=$_POST["acid_bath_2_hcl_conc_result"];
		$acid_bath_2_fe_result=$_POST["acid_bath_2_fe_result"];
		$acid_bath_3_hcl_conc_result=$_POST["acid_bath_3_hcl_conc_result"];
		$acid_bath_3_fe_result=$_POST["acid_bath_3_fe_result"];
		$acid_bath_1_hcl_conc_remarks=$_POST["acid_bath_1_hcl_conc_remarks"];
		$acid_bath_2_hcl_conc_remarks=$_POST["acid_bath_2_hcl_conc_remarks"];
		$acid_bath_3_hcl_conc_remarks=$_POST["acid_bath_3_hcl_conc_remarks"];
		$water_rinsing_bath_ph_ov=$_POST["water_rinsing_bath_ph_ov"];
		$water_rinsing_bath_ph_result=$_POST["water_rinsing_bath_ph_result"];
		$water_rinsing_bath_ph_remarks=$_POST["water_rinsing_bath_ph_remarks"];
		$acid_bath_1_fe_remarks=$_POST["acid_bath_1_fe_remarks"];
		$acid_bath_2_fe_remarks=$_POST["acid_bath_2_fe_remarks"];
		$acid_bath_3_fe_remarks=$_POST["acid_bath_3_fe_remarks"];
		$spray_water_washing_ph_ov=$_POST["spray_water_washing_ph_ov"];
		$spray_water_washing_ph_result=$_POST["spray_water_washing_ph_result"];
		$spray_water_washing_ph_remarks=$_POST["spray_water_washing_ph_remarks"];
		$phosphate_bath_phosphate_ta_ov=$_POST["phosphate_bath_phosphate_ta_ov"];
		$phosphate_bath_phosphate_ta_result=$_POST["phosphate_bath_phosphate_ta_result"];
		$phosphate_bath_phosphate_ta_remarks=$_POST["phosphate_bath_phosphate_ta_remarks"];
		$phosphate_bath_accelerator_ov=$_POST["phosphate_bath_accelerator_ov"];
		$phosphate_bath_accelerator_result=$_POST["phosphate_bath_accelerator_result"];
		$phosphate_bath_accelerator_remarks=$_POST["phosphate_bath_accelerator_remarks"];
		$phosphate_bath_temp_ov=$_POST["phosphate_bath_temp_ov"];
		$phosphate_bath_temp_result=$_POST["phosphate_bath_temp_result"];
		$phosphate_bath_temp_remarks=$_POST["phosphate_bath_temp_remarks"];
		$borax_bath_borax_conc_ov=$_POST["borax_bath_borax_conc_ov"];
		$borax_bath_borax_conc_result=$_POST["borax_bath_borax_conc_result"];
		$borax_bath_borax_conc_remarks=$_POST["borax_bath_borax_conc_remarks"];
		$borax_bath_temp_ov=$_POST["borax_bath_temp_ov"];
		$borax_bath_temp_result=$_POST["borax_bath_temp_result"];
		$borax_bath_temp_remarks=$_POST["borax_bath_temp_remarks"];
		
if(isset($_POST["UPDATE_ITEM"])||isset($_POST["add"]))	
{
	$count=date_shift_check($shift,$date);
	if($count>0)
	{
		display_error(_("The perticular Date and Shift Alredy exists."));
		set_focus('grade');
		return;
	}
}	


if(isset($_POST["UPDATE_ITEM"]))
{
	$selected_id=$_POST["selected_id"];
	$sql = "UPDATE ".TB_PREF."batch_picking_qc SET date=".db_escape($date).",shift=".db_escape($shift).",acid_bath_1_hcl_conc_ov=".db_escape($acid_bath_1_hcl_conc_ov).",acid_bath_1_fe_ov=".db_escape($acid_bath_1_fe_ov).",acid_bath_2_hcl_conc_ov=".db_escape($acid_bath_2_hcl_conc_ov).",acid_bath_2_fe_ov=".db_escape($acid_bath_2_fe_ov).",acid_bath_3_hcl_conc_ov=".db_escape($acid_bath_3_hcl_conc_ov).",acid_bath_3_fe_ov=".db_escape($acid_bath_3_fe_ov).",acid_bath_1_hcl_conc_result=".db_escape($acid_bath_1_hcl_conc_result).",acid_bath_1_fe_result=".db_escape($acid_bath_1_fe_result).",acid_bath_2_hcl_conc_result=".db_escape($acid_bath_2_hcl_conc_result).",acid_bath_2_fe_result=".db_escape($acid_bath_2_fe_result).",acid_bath_3_hcl_conc_result=".db_escape($acid_bath_3_hcl_conc_result).",acid_bath_3_fe_result=".db_escape($acid_bath_3_fe_result).",acid_bath_1_hcl_conc_remarks=".db_escape($acid_bath_1_hcl_conc_remarks).",acid_bath_2_hcl_conc_remarks=".db_escape($acid_bath_2_hcl_conc_remarks).",acid_bath_3_hcl_conc_remarks=".db_escape($acid_bath_3_hcl_conc_remarks).",water_rinsing_bath_ph_ov=".db_escape($water_rinsing_bath_ph_ov).",water_rinsing_bath_ph_result=".db_escape($water_rinsing_bath_ph_result).",water_rinsing_bath_ph_remarks=".db_escape($water_rinsing_bath_ph_remarks).",acid_bath_1_fe_remarks=".db_escape($acid_bath_1_fe_remarks).",acid_bath_2_fe_remarks=".db_escape($acid_bath_2_fe_remarks).",acid_bath_3_fe_remarks=".db_escape($acid_bath_3_fe_remarks).",spray_water_washing_ph_ov=".db_escape($spray_water_washing_ph_ov).",spray_water_washing_ph_result=".db_escape($spray_water_washing_ph_result).",spray_water_washing_ph_remarks=".db_escape($spray_water_washing_ph_remarks).",phosphate_bath_phosphate_ta_ov=".db_escape($phosphate_bath_phosphate_ta_ov).",phosphate_bath_phosphate_ta_result=".db_escape($phosphate_bath_phosphate_ta_result).",phosphate_bath_phosphate_ta_remarks=".db_escape($phosphate_bath_phosphate_ta_remarks).",phosphate_bath_accelerator_ov=".db_escape($phosphate_bath_accelerator_ov).",phosphate_bath_accelerator_result=".db_escape($phosphate_bath_accelerator_result).",phosphate_bath_accelerator_remarks=".db_escape($phosphate_bath_accelerator_remarks).",phosphate_bath_temp_ov=".db_escape($phosphate_bath_temp_ov).",phosphate_bath_temp_result=".db_escape($phosphate_bath_temp_result).",phosphate_bath_temp_remarks=".db_escape($phosphate_bath_temp_remarks).",borax_bath_borax_conc_ov=".db_escape($borax_bath_borax_conc_ov).",borax_bath_borax_conc_result=".db_escape($borax_bath_borax_conc_result).",borax_bath_borax_conc_remarks=".db_escape($borax_bath_borax_conc_remarks).",borax_bath_temp_ov=".db_escape($borax_bath_temp_ov).",borax_bath_temp_result=".db_escape($borax_bath_temp_result).",borax_bath_temp_remarks=".db_escape($borax_bath_temp_remarks)."
	WHERE id = ".db_escape($selected_id);

		
		  //display_error($sql);die;
	db_query($sql, "could not update test certificate!");
	display_notification(_("A new test certificate has been updated."));
		meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$selected_id");
		      $Mode = 'RESET';

} else if(isset($_POST["add"])) {
$date=date2sql($date);
$sql="INSERT INTO batch_picking_qc(date_,shift,acid_bath_1_hcl_conc_ov,acid_bath_1_fe_ov,acid_bath_2_hcl_conc_ov,acid_bath_2_fe_ov,acid_bath_3_fe_ov,acid_bath_1_hcl_conc_result,acid_bath_1_fe_result,acid_bath_2_hcl_conc_result,acid_bath_2_fe_result,acid_bath_3_hcl_conc_result,acid_bath_3_fe_result,acid_bath_1_hcl_conc_remarks,acid_bath_2_hcl_conc_remarks,acid_bath_3_hcl_conc_remarks,water_rinsing_bath_ph_ov,water_rinsing_bath_ph_result,water_rinsing_bath_ph_remarks,acid_bath_1_fe_remarks,acid_bath_2_fe_remarks,acid_bath_3_fe_remarks,spray_water_washing_ph_ov,spray_water_washing_ph_result,spray_water_washing_ph_remarks,phosphate_bath_phosphate_ta_ov,phosphate_bath_phosphate_ta_result,phosphate_bath_phosphate_ta_remarks,phosphate_bath_accelerator_ov,phosphate_bath_accelerator_result,phosphate_bath_accelerator_remarks,phosphate_bath_temp_ov,phosphate_bath_temp_result,phosphate_bath_temp_remarks,borax_bath_borax_conc_ov,borax_bath_borax_conc_result,borax_bath_borax_conc_remarks,borax_bath_temp_ov,borax_bath_temp_result,borax_bath_temp_remarks,acid_bath_3_hcl_conc_ov)VALUES(".db_escape($date_).",
".db_escape($shift).",
".db_escape($acid_bath_1_hcl_conc_ov).",
".db_escape($acid_bath_1_fe_ov).",
".db_escape($acid_bath_2_hcl_conc_ov).",
".db_escape($acid_bath_2_fe_ov).",
".db_escape($acid_bath_3_fe_ov).",
".db_escape($acid_bath_1_hcl_conc_result).",
".db_escape($acid_bath_1_fe_result).",
".db_escape($acid_bath_2_hcl_conc_result).",
".db_escape($acid_bath_2_fe_result).",
".db_escape($acid_bath_3_hcl_conc_result).",
".db_escape($acid_bath_3_fe_result).",
".db_escape($acid_bath_1_hcl_conc_remarks).",
".db_escape($acid_bath_2_hcl_conc_remarks).",
".db_escape($acid_bath_3_hcl_conc_remarks).",
".db_escape($water_rinsing_bath_ph_ov).",
".db_escape($water_rinsing_bath_ph_result).",
".db_escape($water_rinsing_bath_ph_remarks).",
".db_escape($acid_bath_1_fe_remarks).",
".db_escape($acid_bath_2_fe_remarks).",
".db_escape($acid_bath_3_fe_remarks).",
".db_escape($spray_water_washing_ph_ov).",
".db_escape($spray_water_washing_ph_result).",
".db_escape($spray_water_washing_ph_remarks).",
".db_escape($phosphate_bath_phosphate_ta_ov).",
".db_escape($phosphate_bath_phosphate_ta_result).",
".db_escape($phosphate_bath_phosphate_ta_remarks).",
".db_escape($phosphate_bath_accelerator_ov).",
".db_escape($phosphate_bath_accelerator_result).",
".db_escape($phosphate_bath_accelerator_remarks).",
".db_escape($phosphate_bath_temp_ov).",
".db_escape($phosphate_bath_temp_result).",
".db_escape($phosphate_bath_temp_remarks).",
".db_escape($borax_bath_borax_conc_ov).",
".db_escape($borax_bath_borax_conc_result).",
".db_escape($borax_bath_borax_conc_remarks).",
".db_escape($borax_bath_temp_ov).",
".db_escape($borax_bath_temp_result).",
".db_escape($borax_bath_temp_remarks).",
".db_escape($acid_bath_3_hcl_conc_ov).")";	
		

	db_query($sql,"Could not add TC!");
		display_notification(_("A new sample has been added."));
		meta_forward($_SERVER['PHP_SELF'], "AddedID=$selected_id");
		      $Mode = 'RESET';
}

function batch_picking_data_edit($trans_no)
{
	$sql="SELECT * FROM ".TB_PREF."batch_picking_qc WHERE id=".db_escape($trans_no)." ";
	$res = db_query($sql,"Could not get the sample data!");
	return $result = db_fetch($res);
}
function date_shift_check($shift,$date_)
{
	$date_=date2sql($date_);
	$sql="SELECT * FROM batch_picking_qc WHERE shift=".db_escape($shift)." AND date_=".db_escape($date_);
	$res = db_query($sql,"Could not get the sample data!");
	return db_num_rows($res);

}
if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

?>

<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

</head>
<body>
 

<form action="" method="POST">
<table align="center"  >
<?php 
if(isset($selected_id))
	{
		$result=batch_picking_data_edit($selected_id);
		$_POST["date"]=$result["date"];
		$_POST["shift"]=$result["shift"];
		$_POST["acid_bath_1_hcl_conc_ov"]=$result["acid_bath_1_hcl_conc_ov"];
		$_POST["acid_bath_1_fe_ov"]=$result["acid_bath_1_fe_ov"];
		$_POST["acid_bath_2_hcl_conc_ov"]=$result["acid_bath_2_hcl_conc_ov"];
		$_POST["acid_bath_2_fe_ov"]=$result["acid_bath_2_fe_ov"];
		$_POST["acid_bath_3_hcl_conc_ov"]=$result["acid_bath_3_hcl_conc_ov"];
		$_POST["acid_bath_3_fe_ov"]=$result["acid_bath_3_fe_ov"];
		$_POST["acid_bath_1_hcl_conc_result"]=$result["acid_bath_1_hcl_conc_result"];
		$_POST["acid_bath_1_fe_result"]=$result["acid_bath_1_fe_result"];
		$_POST["acid_bath_2_hcl_conc_result"]=$result["acid_bath_2_hcl_conc_result"];
		$_POST["acid_bath_2_fe_result"]=$result["acid_bath_2_fe_result"];
		$_POST["acid_bath_3_hcl_conc_result"]=$result["acid_bath_3_hcl_conc_result"];
		$_POST["acid_bath_3_fe_result"]=$result["acid_bath_3_fe_result"];
		$_POST["acid_bath_1_hcl_conc_remarks"]=$result["acid_bath_1_hcl_conc_remarks"];
		$_POST["acid_bath_2_hcl_conc_remarks"]=$result["acid_bath_2_hcl_conc_remarks"];
		$_POST["acid_bath_3_hcl_conc_remarks"]=$result["acid_bath_3_hcl_conc_remarks"];
		$_POST["water_rinsing_bath_ph_ov"]=$result["water_rinsing_bath_ph_ov"];
		$_POST["water_rinsing_bath_ph_result"]=$result["water_rinsing_bath_ph_result"];
		$_POST["water_rinsing_bath_ph_remarks"]=$result["water_rinsing_bath_ph_remarks"];
		$_POST["acid_bath_1_fe_remarks"]=$result["acid_bath_1_fe_remarks"];
		$_POST["acid_bath_2_fe_remarks"]=$result["acid_bath_2_fe_remarks"];
		$_POST["acid_bath_3_fe_remarks"]=$result["acid_bath_3_fe_remarks"];
		$_POST["spray_water_washing_ph_result"]=$result["spray_water_washing_ph_result"];
		$_POST["spray_water_washing_ph_remarks"]=$result["spray_water_washing_ph_remarks"];
		$_POST["phosphate_bath_phosphate_ta_ov"]=$result["phosphate_bath_phosphate_ta_ov"];
		$_POST["phosphate_bath_phosphate_ta_result"]=$result["phosphate_bath_phosphate_ta_result"];
		$_POST["phosphate_bath_phosphate_ta_remarks"]=$result["phosphate_bath_phosphate_ta_remarks"];
		$_POST["phosphate_bath_accelerator_ov"]=$result["phosphate_bath_accelerator_ov"];
		$_POST["phosphate_bath_accelerator_result"]=$result["phosphate_bath_accelerator_result"];
		$_POST["phosphate_bath_accelerator_remarks"]=$result["phosphate_bath_accelerator_remarks"];
		$_POST["phosphate_bath_temp_ov"]=$result["phosphate_bath_temp_ov"];
		$_POST["phosphate_bath_temp_result"]=$result["phosphate_bath_temp_result"];
		$_POST["phosphate_bath_temp_remarks"]=$result["phosphate_bath_temp_remarks"];
		$_POST["borax_bath_borax_conc_ov"]=$result["borax_bath_borax_conc_ov"];
		$_POST["borax_bath_borax_conc_result"]=$result["borax_bath_borax_conc_result"];
		$_POST["borax_bath_borax_conc_remarks"]=$result["borax_bath_borax_conc_remarks"];
		$_POST["borax_bath_temp_ov"]=$result["borax_bath_temp_ov"];
		$_POST["borax_bath_temp_result"]=$result["borax_bath_temp_result"];
		$_POST["borax_bath_temp_remarks"]=$result["borax_bath_temp_remarks"];

        		hidden('selected_id',$result["id"]);

		
	}
	

	start_row();
	date_cells(_("Date:"),'date');
	shift_types_list_row(_("Shift:"), 'shift');
	
     end_row();

	br();
?>
</table>
</br>
</br>
<table align="center">
<?start_outer_table(TABLESTYLE2, "width='100%'");?>

	<tr>
    <th style="background-color: #7895ac; text-align:center" rowspan="2" style="color:blue;">Chemical Bath</th>
    <th style="background-color: #7895ac; text-align:center" rowspan="2">Parameter</th>
    <th style="background-color: #7895ac; text-align:center" colspan="2">Specification</th>
	<th style="background-color: #7895ac; text-align:center " rowspan="2">Observed Values</th>
	<th style="background-color: #7895ac; text-align:center " colspan="1">Result</th>
	<th style="background-color: #7895ac; text-align:center " rowspan="2">Remark if any</th>
	 
	</tr>
	<tr>
	<td style="background-color: #7895ac; color:white; font-weight: bold; text-align:center"> Minimum</td>
	<td style="background-color: #7895ac; color:white; font-weight: bold;  text-align:center">Maximum</td>
	<td style="background-color: #7895ac; color:white; font-weight: bold;  text-align:center" >Pass or Fail</td>
	
	<!-- <td style="background-color: #7895ac;">Avg.</td> -->
	</tr>
  <tr> 
  <tr>
    <td style="font-weight: bold; text-align:center"rowspan="2">Acid Bath<br> <br> No.1</td>
    <td>% HCL Conc.</td> 
    <td>4%</td>
	<td>20%</td>
	
    <?php 
		text_cells(null, 'acid_bath_1_hcl_conc_ov', null,20,1000);
		qc_result_list_cell('acid_bath_1_hcl_conc_result',null,true);  
		 textarea_cells(null, 'acid_bath_1_hcl_conc_remarks', null, 20, 3); 
		?>
	
  </tr>
  </tr>
 
  <tr>
    <td>% Fe</td>
    <td>----</td>
    <td>12%</td>
    <?php text_cells(null, 'acid_bath_1_fe_ov', null,20, 1000);
	     qc_result_list_cell('acid_bath_1_fe_result',null,true); 
		 textarea_cells(null, 'acid_bath_1_fe_remarks', null, 20, 3);
		 ?>
  </tr>
   <tr> 
  <tr>
    <td style="font-weight: bold; text-align:center" rowspan="2">Acid Bath<br> <br> No.2</td>
    <td>% HCL Conc.</td> 
    <td>4%</td>
	<td>20%</td>
	
    <?php 
		text_cells(null, 'acid_bath_2_hcl_conc_ov', null,20,1000);
		qc_result_list_cell('acid_bath_2_hcl_conc_result',null,true);  
		 textarea_cells(null, 'acid_bath_2_hcl_conc_remarks', null, 20, 3);
		?>
	
  </tr>
  </tr>
 
  <tr>
    <td>% Fe</td>
    <td>----</td>
    <td>12%</td>
    <?php text_cells(null, 'acid_bath_2_fe_ov', null,20, 1000);
	      qc_result_list_cell('acid_bath_2_fe_result',null,true);  
		 textarea_cells(null, 'acid_bath_2_fe_remarks', null, 20, 3);
		 ?>
  </tr>
  
  
     <tr> 
  <tr>
    <td style="font-weight: bold; text-align:center" rowspan="2">Acid Bath<br> <br> No.3</td>
    <td>% HCL Conc.</td> 
    <td>4%</td>
	<td>20%</td>
	
    <?php 
		text_cells(null, 'acid_bath_3_hcl_conc_ov', null,20,1000);
		qc_result_list_cell('acid_bath_3_hcl_conc_result',null,true); 
		 textarea_cells(null, 'acid_bath_3_hcl_conc_remarks', null, 20, 3);
		?>
	
  </tr>
  </tr>
 
  <tr>
    <td>% Fe</td>
    <td>----</td>
    <td>12%</td>
    <?php text_cells(null, 'acid_bath_3_fe_ov', null,20, 1000);
	      qc_result_list_cell('acid_bath_3_fe_result',null,true); 
		  textarea_cells(null, 'acid_bath_3_fe_remarks', null, 20, 3); ?>
  </tr>
    <tr>
    <td style="font-weight: bold; text-align:center">Water Rinsing Bath </td>
    <td>PH Value</td>
    <td>2.0</td>
	<td>----</td>
    <?php text_cells(null, 'water_rinsing_bath_ph_ov', null,20, 1000);
	      qc_result_list_cell('water_rinsing_bath_ph_result',null,true); 
		 textarea_cells(null, 'water_rinsing_bath_ph_remarks', null, 20, 3);
		  ?>
  </tr>
      <tr>
    <td style="font-weight: bold; text-align:center">Spray Water Washing </td>
    <td>PH Value</td>
    <td>7.0</td>
	<td>----</td>
    <?php text_cells(null, 'spray_water_washing_ph_ov', null,20, 1000);
	      qc_result_list_cell('spray_water_washing_ph_result',null,true); 
		  textarea_cells(null, 'spray_water_washing_ph_remarks', null, 20, 3);
		  ?>
  </tr>
  
     <tr> 
  <tr>
    <td  style="font-weight: bold; text-align:center" rowspan="3">Phosphate Bath</td>
    <td>Phosphate TA</td> 
    <td>10</td>
	<td>25%</td>
	
    <?php 
		text_cells(null, 'phosphate_bath_phosphate_ta_ov', null,20,1000);
		qc_result_list_cell('phosphate_bath_phosphate_ta_result',null,true); 
		 textarea_cells(null, 'phosphate_bath_phosphate_ta_remarks', null, 20, 3);
		?>
	
  </tr>
  </tr>
 
  <tr>
    <td>Accelerator</td>
    <td colspan="2">Should be blue color by <br>Starch Iodide paper </td> 
    <?php text_cells(null, 'phosphate_bath_accelerator_ov', null,20, 1000);
	      qc_result_list_cell('phosphate_bath_accelerator_result',null,true); 
		  textarea_cells(null, 'phosphate_bath_accelerator_remarks', null, 20, 3);
		  ?>
  </tr>
    <tr>
    <td>Temperature C</td>
    <td>70<span style='font-size:16px;'>&#176;</span></td>
    <td>85<span style='font-size:16px;'>&#176;</span></td>
    <?php text_cells(null, 'phosphate_bath_temp_ov', null,20, 1000);
	      qc_result_list_cell('phosphate_bath_temp_result',null,true); 
		  textarea_cells(null, 'phosphate_bath_temp_remarks', null, 20, 3);
		  ?>
  </tr>
    <tr> 
  <tr>
    <td style="font-weight: bold; text-align:center" rowspan="2">Borax Bath</td>
    <td>% Borax Conc.</td> 
    <td>10%</td>
	<td>20%</td>
	
    <?php 
		text_cells(null, 'borax_bath_borax_conc_ov', null,20,1000);
		qc_result_list_cell('borax_bath_borax_conc_result',null,true); 
		 textarea_cells(null, 'borax_bath_borax_conc_remarks', null, 20, 3);
		?>
	
  </tr>
  </tr>
 
  <tr>
    <td>Temperature C</td>
    <td >80<span style='font-size:16px;'>&#176;</span></td>
    <td>95<span style='font-size:16px;'>&#176;</span></td>
    <?php text_cells(null, 'borax_bath_temp_ov', null,20, 1000);
	      qc_result_list_cell('borax_bath_temp_result',null,true); 
		 textarea_cells(null, 'borax_bath_temp_remarks', null, 20, 3);
		 ?>
  </tr>
 
</table>

<?php  
br();
end_table(1);
div_end();
if(isset($selected_id))
{
	submit_center_first('UPDATE_ITEM', _("Update"), '', _('Save changes Batch Pickling Report'), 'default');
}else {
submit_center_first('add', _("Add"), '',$page_nested ? true : 'default');
}
//end_form();
end_page();
?>
</form>
</body>
</html>
<style>
 

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;

}
#number{
float:right;
}
th{
	color:white;
}




</style>

