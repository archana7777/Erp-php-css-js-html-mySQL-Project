<?php
// ----------------------------------------------------------------
// Creator: Kvvaradha
// email:   admin@kvcodes.com
// Title:   HRM Extention for Frontaccounting
// website: http://www.kvcodes.com
// ----------------------------------------------------------------
global $kv_empl_gender, $hrm_empl_status, $hrm_empl_salutation, $kv_empl_mop; 

$hrm_empl_status = array(
    1 => _('Active'),  
    2 => _('Suspened'), 
    3 => _('Deceased'), 
    4 => _('Retired'), 
    5 => _('Terminated'), 
);

$hrm_empl_salutation = array(
    1 => _('Mr.'),  
    2 => _('Ms.'), 
    3 => _('Mrs.'), 
    4 => _('Dr.'), 
    5 => _('Prof.'), 
    6 => _('Other.'), 
);
$kv_empl_gender = array(
    1 => _('Male'),  
    2 => _('Female'), 
    3 => _('Transgender'), 

);

$kv_empl_mop = array(
    1 => _('Cash'),  
    2 => _('Bank'), 
    3 => _('Cheque'), 

);

function kv_empl_gender_list_row($label, $name, $selected_id=null, $submit_on_change=false) {
	global $kv_empl_gender;
		
	echo "<tr><td class='label'>$label</td><td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $kv_empl_gender, $options);
	echo "</td></tr>\n";
}

function  kv_empl_salutation_list_row($label, $name, $selected_id=null, $submit_on_change=false) {
	global $hrm_empl_salutation;
		
	echo "<tr><td class='label'>$label</td><td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $hrm_empl_salutation, $options);
	echo "</td></tr>\n";
}


function  hrm_empl_status_list($label, $name, $selected_id=null, $submit_on_change=false) {
	global $hrm_empl_status;
		
	echo "<tr><td class='label'>$label</td><td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $hrm_empl_status, $options);
	echo "</td></tr>\n";
}

function  hrm_empl_mop_list($label, $name, $selected_id=null, $submit_on_change=false) {
	global $kv_empl_mop;
		
	echo "<tr><td class='label'>$label</td><td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $kv_empl_mop, $options);
	echo "</td></tr>\n";
}

//-----------------------------------------------------------------------------------------------

function country_list($name, $selected_id=null){

	$sql = "SELECT id, local_name FROM ".TB_PREF."kv_country";
	return combo_input($name, $selected_id, $sql, 'id', 'local_name', array());
}

function country_list_cells($label, $name, $selected_id=null){
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo country_list($name, $selected_id);
	echo "</td>\n";
}

function country_list_row($label, $name, $selected_id=null){
	echo "<tr><td class='label'>$label</td>";
	country_list_cells(null, $name, $selected_id);
	echo "</tr>\n";
}

//------------------------------------------------------------
$hrm_empl_workings_days = array(
    'Sun' => _('Sunday'),  
    'Mon' => _('Monday'), 
    'Tue' => _('Tuesday'), 
    'Wed' => _('Wednesday'), 
    'Thu' => _('Thursday'), 
    'Fri' => _('Friday'), 
    'Sat' => _('Saturday')
);

function  hrm_empl_workings_days($label, $name, $selected_id=null, $submit_on_change=false) {
	global $hrm_empl_workings_days;
		
	echo "<tr><td class='label'>$label</td><td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $hrm_empl_workings_days, $options);
	echo "</td></tr>\n";
}

//------------------------------------------------------------
$hrm_empl_ref_type = array(
    0 => _('Manual'),  
    1 => _('Automatic')    
);

function  hrm_empl_ref_type($label, $name, $selected_id=null, $submit_on_change=false) {
	global $hrm_empl_ref_type;
		
	echo "<tr><td class='label'>$label</td><td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $hrm_empl_ref_type, $options);
	echo "</td></tr>\n";
}

function kv_text_rows($label, $name, $size, $max=null, $submit_on_change=false, $title=null, $init=null, $labparams=null, $post_label=null){
  	global $Ajax;

	default_focus($name);
	if (!isset($_POST[$name]) || $_POST[$name] == "")	{
		if ($init)
			$_POST[$name] = $init;
		else
			$_POST[$name] = "";
	}
	if ($label != null){
		echo '<tr>';
		label_cell($label, $labparams);		
	}

	if (!isset($max))
		$max = $size;

	echo "<td>";
	$class = $submit_on_change ? 'class="searchbox"' : '';
	echo "<input $class type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"" . $_POST[$name]. "\""
	 .($title ? " title='$title'": '')." >";

	if ($post_label)
		echo " " . $post_label;

	echo "</td>\n";
	if ($label != null)
	echo '</tr>';
	$Ajax->activate('_page_body');
}

//-----------------------------------------------------------------------------------
function kv_radio($label, $name, $value, $selected=null, $submit_on_change=false, $disabled=''){
	if (!isset($selected))
		$selected = get_post($name) == $value;

	if ($submit_on_change === true)
		$submit_on_change = 
			"JsHttpRequest.request(\"_{$name}_update\", this.form);";

	return "<input type='radio' {$disabled} name=$name value='$value' ".($selected ? "checked":'')
	    .($submit_on_change ? " onclick='$submit_on_change'" : '')
		.">".($label ? $label : '');
}

function kv_basic_row($label, $name, $size, $max=null, $init=null, $submit_on_change=false){
  	global $Ajax;

  	echo "<tr>";
	default_focus($name);
	if (!isset($_POST[$name]) || $_POST[$name] == ""){
		if ($init)
			$_POST[$name] = $init;
		else
			$_POST[$name] = "";
	}
	if ($label != null)
		label_cell($label, null);

	if (!isset($max))
		$max = $size;

	echo "<td>";
	$class = $submit_on_change ? 'class="searchbox"' : '';
	
		echo "<input $class type=\"text\"  name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"" . $_POST[$name]. "\""
	 .($label ? " title='$label'": '')."  > &nbsp;&nbsp;";
	submit('RefreshInquiry', _("Calculate"),_('Show Results'), 'default', true);
	echo "</td>\n";
	$Ajax->addUpdate($name, $name, $_POST[$name]);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------

function kv_text_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null, $submit_on_change=false,$disabled=false){
	echo "<tr><td class='label'>$label</td>";
	kv_text_cells_ex(null, $name, $size, $max, $value, $title, $params, $post_label, false, $disabled);

	echo "</tr>\n";
}


function kv_text_cells_ex($label, $name, $size, $max=null, $init=null, $title=null,
	$labparams=null, $post_label=null, $submit_on_change=false, $disabled= false){
  	global $Ajax;

	default_focus($name);
	if (!isset($_POST[$name]) || $_POST[$name] == "")
	{
		if ($init)
			$_POST[$name] = $init;
		else
			$_POST[$name] = "";
	}
	if ($label != null)
		label_cell($label, $labparams);

	if (!isset($max))
		$max = $size;

	echo "<td>";
	$class = $submit_on_change ? 'class="searchbox"' : '';
	if($disabled){
		echo "<input disabled $class type=\"text\"  name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"" . $_POST[$name]. "\""
	 .($title ? " title='$title'": '')."  >";
	}else{
		echo "<input $class type=\"text\"  name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"" . $_POST[$name]. "\""
	 .($title ? " title='$title'": '')."  >";
	}	

	if ($post_label)
		echo " " . $post_label;

	echo "</td>\n";
	$Ajax->addUpdate($name, $name, $_POST[$name]);
}

function kv_checkbox($label, $name, $value=null, $submit_on_change=false, $title=false, $disabled =false)
{
  	global $Ajax;

	$str = '';	

	if ($label)
		$str .= $label . "  ";
	if ($submit_on_change !== false) {
		if ($submit_on_change === true)
			$submit_on_change = 
				"JsHttpRequest.request(\"_{$name}_update\", this.form);";
	}
	if ($value === null)
		$value = get_post($name,0);

	$str .= "<input"
	    .($value == 1 ? ' checked':'')
	    ." type='checkbox' name='$name' value='1'"
	    .($submit_on_change ? " onclick='$submit_on_change'" : '')
	    .($title ? " title='$title'" : '')
	    .($disabled ? "disabled" : '')
	    ." >\n";

	$Ajax->addUpdate($name, $name, $value);
	return $str;
}

function kv_check($label, $name, $value=null, $submit_on_change=false, $title=false, $disabled=false)
{
	echo kv_checkbox($label, $name, $value, $submit_on_change, $title, $disabled);
}

function kv_check_cells($label, $name, $value=null, $submit_on_change=false, $title=false,
	$params='', $disabled =false)
{
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td $params>";
	echo kv_check(null, $name, $value, $submit_on_change, $title, $disabled);
	echo "</td>";
}

function kv_check_row($label, $name, $value=null, $submit_on_change=false, $title=false, $disabled=false)
{
	echo "<tr><td class='label'>$label</td>";
	echo kv_check_cells(NULL, $name, $value, $submit_on_change, $title,'', $disabled);
	echo "</tr>\n";
}

function kv_button($name, $value, $title=false, $icon=false,  $aspect='', $disabled=false)
{
	// php silently changes dots,spaces,'[' and characters 128-159
	// to underscore in POST names, to maintain compatibility with register_globals
	$rel = '';
	if ($aspect == 'selector') {
		$rel = " rel='$value'";
		$value = _("Select");
	}
	if($disabled != false)
		$disable = 'disabled';
	else
		$disable = '';
	if (user_graphic_links() && $icon)
	{
		if ($value == _("Delete")) // Helper during implementation
			$icon = ICON_DELETE;
		return "<button type='submit' $disable class='editbutton' name='"
			.htmlentities(strtr($name, array('.'=>'=2E', '='=>'=3D',// ' '=>'=20','['=>'=5B'
			)))
			."' value='1'" . ($title ? " title='$title'":" title='$value'")
			. ($aspect ? " aspect='$aspect'" : '')
			. $rel
			." >".set_icon($icon)."</button>\n";
	}
	else
		return "<input type='submit' $disable class='editbutton' name='"
			.htmlentities(strtr($name, array('.'=>'=2E', '='=>'=3D',// ' '=>'=20','['=>'=5B'
			)))
			."' value='$value'"
			.($title ? " title='$title'":'')
			. ($aspect ? " aspect='$aspect'" : '')
			. $rel
			." >\n";
}

function kv_button_cell($name, $value, $title=false, $icon=false, $aspect='', $disabled=false)
{
	echo "<td align='center'>";
	echo kv_button($name, $value, $title, $icon, $aspect, $disabled);
	echo "</td>";
}

function kv_delete_button_cell($name, $value, $title=false, $disabled=false)
{
	kv_button_cell($name, $value, $title, ICON_DELETE,'', $disabled);
}

?>