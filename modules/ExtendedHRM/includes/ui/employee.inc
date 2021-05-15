<?php
// ----------------------------------------------------------------
// Creator: Kvvaradha
// email:   admin@kvcodes.com
// Title:   HRM Extention for Frontaccounting
// website: http://www.kvcodes.com
// ----------------------------------------------------------------

//---------------------------------------------------------------------------------------------------

function employee_list($name, $selected_id=null, $all_option=false, $submit_on_change=false, $show_inactive=false, $editkey = false){
	global $all_items;

	$sql = "SELECT empl.empl_id, empl.empl_firstname, dept.description FROM ".TB_PREF."kv_empl_info empl,".TB_PREF."kv_empl_job job, ".TB_PREF."kv_departments dept WHERE empl.empl_id=job.empl_id AND job.department=dept.id";

	if ($editkey)
		set_editor('employee', $name, $editkey);
	$mode = 0;
	$ret = combo_input($name, $selected_id, $sql, 'empl.empl_id', 'empl.empl_firstname',
	array_merge(
	  array(
		'format' => '_format_empl',
		'spec_option' => $all_option===true ?  _("All Items") : $all_option,
		'spec_id' => $all_items,
		'search_box' => true,
		'type' => 1,
		'search' => array("empl.empl_id", "dept.description","empl.empl_firstname"),
		'search_submit' => get_company_pref('no_item_list')!=0,
		'size'=>20,
		'async' => false,
		'select_submit'=> $submit_on_change,
		'category' => 2,
		'order' => array('dept.description','empl.empl_firstname'),
		'sel_hint' => $mode ? _('Press Space tab to filter by name fragment; F2 - entry new employee') :	_('Select employee'),
		'show_inactive' => $show_inactive
	  )) );
	if ($editkey)
		$ret .= add_edit_combo('item');		
	return $ret;
	  
}

function _format_empl($row){
	return (user_show_codes() ?  ($row[0] . "&nbsp;-&nbsp;") : "") . $row[1];
}

function employee_list_cells($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false, $all=false, $editkey = false){
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td nowrap>";
	echo employee_list($name, $selected_id, $all_option, $submit_on_change,	array('cells'=>true, 'show_inactive'=>$all), $editkey);
	echo "</td>\n";
}

function empl_img_name($empl_id){
	$empl_id = strtr($empl_id, "><\\/:|*?", '________');
	return clean_file_name($empl_id);
}


//-----------------------------------------------------------------------------------
function kv_image_cells($label, $name, $id=""){
	if ($id != "")
		$id = "id='$id'";
	label_cells($label, "<input type='file' name='$name' $id  'image/*'/>");
}		
function kv_image_row($label, $name, $id = ""){
	echo "<tr><td class='label'>$label</td>";
	kv_image_cells(null, $name, $id);
	echo "</tr>\n";
}	


//-----------------------------------------------------------------------------------
function kv_doc_cells($label, $name, $id=""){
	if ($id != "")
		$id = "id='$id'";
	label_cells($label, "<input type='file' name='$name' $id  accept='.doc,.docx, .rtf, .txt'/>");
}		
function kv_doc_row($label, $name, $id = ""){
	echo "<tr><td class='label'>$label</td>";
	kv_doc_cells(null, $name, $id);
	echo "</tr>\n";
}
?>