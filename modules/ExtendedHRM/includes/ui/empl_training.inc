<?php
/**********************************************************************
 AuthoR : Kvvaradha
 
 Module : HRM 
 
 Developed to 1Stop WebSolution.com
 
***********************************************************************/
include_once($path_to_root. '/includes/ui/simple_crud_class.inc');
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_training_db.inc");

/*
	View/Edit class for training
*/
class training extends simple_crud {
	var $selected_id;
	var $entity;
	var $sub_class;
	var $class;
	
	function training($name, $id, $class, $subclass=null) {
		$fields = array('training_desc','course','cost', 'institute', 'start_date','end_date', 'notes', //'lang','notess', 
		'assgn' =>array('fld'=>'training')
						);

		$this->simple_crud($name, $fields);
		$this->class = $class;
		$this->subclass = $subclass;
		$this->entity = $id;
	}

	function list_view() {
		br();

			$training = get_person_trainings( $this->entity);

			start_table(TABLESTYLE, "width=80%");

			$th = array( _("Training Description"), _("Course"), _("Cost"), _("Institute"), _("Start Date"),	_("End Date"), 	_("Notes"), '',	"&nbsp;");


			table_header($th);
			foreach($training as $de) {
				
			label_cell($de[2]);					
			label_cell($de[3]);
			label_cell($de[4]);
			label_cell($de[5]);
			label_cell($de[6]);
			label_cell($de[7]);
			label_cell($de[8]);
			
					
			edit_button_cell("{$this->name}Edit[{$de[0]}]", _("Edit"));

			// delete button removes only category unless this is the last training for this training
			delete_button_cell("{$this->name}Delete[{$de[0]}]", _("Delete"));
			end_row();
					
		}
		end_table(1);
	}
	
	function editor_view(){
		br();
		
		if($this->selected_id != null || $this->selected_id != "") {
			$degre = get_empl_training_single($this->selected_id);
		
			foreach($degre as $de) {
				start_outer_table(TABLESTYLE);
				$dat = date('d-m-Y', strtotime($de[6])); 
				table_section(1);
				table_section_title(_("Training"));
				
				textarea_row(_("Training Description:"), 'training_desc', $de[2], 30, 4);
				text_row(_("Course :"), 'course', $de[3], 35, 40);
				text_row(_("Cost:"), 'cost', $de[4], 35, 40);
				text_row(_("Institute:"), 'institute', $de[5], 35, 40);
				
				date_row(_("Start Date:"), 'start_date', $de[6]);		

				date_row(_("End Date:"), 'end_date', $de[7]);
				
				textarea_row(_("Notes:"), 'notes', $de[8], 30, 4);
				end_outer_table(1);
			}
		}
		else {
			start_outer_table(TABLESTYLE);
			table_section(1);
			table_section_title(_("Training"));
				
			textarea_row(_("Training Description:"), 'training_desc', $de[2], 30, 4);
			text_row(_("Course :"), 'course', $de[3], 35, 40);
			text_row(_("Cost:"), 'cost', $de[4], 35, 40);
			text_row(_("Institute:"), 'institute', $de[5], 35, 40);
				
			date_row(_("Start Date:"), 'start_date', $de[6]);		

			date_row(_("End Date:"), 'end_date', $de[7]);
				
			textarea_row(_("Notes:"), 'notes', $de[8], 30, 4);
			end_outer_table(1);
			
		}
			
	}
	function _bottom_controls()	{
		$title=false;
		$async='both';
		$base=$this->name;

		$cancel = $async;

		if ($async === 'both') {
			$async = 'default'; $cancel = 'cancel';
		} 
		else if ($async === 'default')
			$cancel = true;
		else if ($async === 'cancel')
			$async = true;
		echo "<center>";

		if ($this->Mode == '' || $this->Mode == 'RESET')
			submit("{$base}NEW", _("Add new"), true, $title, $async);
		else {
			if ($this->Mode == 'NEW' || $this->selected_id==$this->_none)
				
				submit("{$base}ADD", _("Add"), true, $title, $async);
			else {
				submit("{$base}UPDATE[{$this->selected_id}]", _("Update"), true, _('Submit changes'), $async);				
			}
			submit("{$base}RESET", _("Cancel"), true, _('Cancel edition'), $cancel);
		}
		echo "</center>";
	}
	

	function db_insert() {
			$dat = $this->data;
			return add_empl_training($this->entity, $dat['training_desc'], $dat['course'], $dat['cost'], $dat['institute'], $dat['start_date'], $dat['end_date'], $dat['notes']);
	}

	function db_update() {
			return update_empl_training($this->entity, $_POST['training_desc'], $_POST['course'], $_POST['cost'], $_POST['institute'], $_POST['start_date'], $_POST['end_date'], $_POST['notes'], $this->selected_id, $this->class);
	}

	function insert_check() {
		if (strlen($_POST['training_desc']) == 0) {
			display_error(_("The training Description cannot be empty."));
			set_focus('training_desc');
			return false;
		}
		if (strlen($_POST['course']) == 0) {
			display_error(_("Course Name cannot be empty."));
			set_focus('course');
			return false;
		}
		
		return true; 
	}

	function db_read() {
		return get_empl_training($this->entity);
	}

	function delete_check() {
		return true;
	}
	
	function db_delete() {
		if (delete_empl_training($this->selected_id, $this->class, $this->entity)) {
			
		}
		return false;
	}
}

?>