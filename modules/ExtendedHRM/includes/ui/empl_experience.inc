<?php
/**********************************************************************
 AuthoR : Kvvaradha
 
 Module : HRM 
 
 Developed to 1Stop WebSolution.com
 
***********************************************************************/
include_once($path_to_root. '/includes/ui/simple_crud_class.inc');
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_experience_db.inc");


class experience extends simple_crud {
	var $selected_id;
	var $entity;
	var $sub_class;
	var $class;
	
	function experience($name, $id, $class, $subclass=null) {
		$fields = array('name_of_org','job_role','start_date', 'end_date');

		$this->simple_crud($name, $fields);
		$this->class = $class;
		$this->subclass = $subclass;
		$this->entity = $id;
		
	}

	function list_view() {
		br();

		$experience = get_person_experiences($this->entity);
			
		start_table(TABLESTYLE, "width=80%");

		$th = array( _("Organization"), _("Job Role"), _("Start Date"), _("End Date"), "", "");	

		table_header($th);
		
		foreach($experience as $de) {
				
			label_cell($de[2]);					
			label_cell($de[3]);
			label_cell($de[4]);
			label_cell($de[5]);
			
					
			edit_button_cell("{$this->name}Edit[{$de[0]}]", _("Edit"));

			// delete button removes only category unless this is the last experience for this experience
			delete_button_cell("{$this->name}Delete[{$de[0]}]", _("Delete"));
			end_row();
					
		}
		end_table(1);
	}
	
	function editor_view(){
		br();
		
		if($this->selected_id != null || $this->selected_id != "") {
			$degre = get_empl_experience_single($this->selected_id);
		
			foreach($degre as $de) {
				start_outer_table(TABLESTYLE);
				$dat = date('d-m-Y', strtotime($de[6])); 
				table_section(1);
				table_section_title(_("Experience Data"));
				text_row(_("Organization :"), 'name_of_org', $de[2], 35, 40);
				text_row(_("Job Role:"), 'job_role', $de[3], 35, 40);
				date_row(_("Start Date:"), 'start_date', $de[4]);
				date_row(_("End Date:"), 'end_date', $de[5]);
					
				end_outer_table(1);	
			
			}
		}
		else {
			start_outer_table(TABLESTYLE);

			table_section(1);
			table_section_title(_("experience data"));

			text_row(_("Organization Name :"), 'name_of_org', $degre[2], 35, 40);
			text_row(_("Job Role:"), 'job_role', $degre[3], 35, 40);
			date_row(_("Start Date:"), 'start_date', @$this->data['university'], 35, 40);
			date_row(_("End Date:"), 'end_date', @$this->data['grade'], 32, 30);
			
			
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
			
		return add_empl_experience($this->entity, $dat['name_of_org'], $dat['job_role'], $dat['start_date'], $dat['end_date']);
	}

	function db_update() {	
		return update_empl_experience($this->entity, $_POST['name_of_org'], $_POST['job_role'], $_POST['start_date'], $_POST['end_date'],  $this->selected_id, $this->class);
	}

	function insert_check() {
		if (strlen($_POST['name_of_org']) == 0) {
			display_error(_("The Organization cannot be empty."));
			set_focus('name_of_org');
			return false;
		}
		if (strlen($_POST['job_role']) == 0) {
			display_error(_("Employee Job role cannot be empty."));
			set_focus('job_role');
			return false;
		}
		if (count(@$_POST['start_date']) == 0) {
			display_error(_("Select Employee career start up date."));
			set_focus('start_date');
			return false;
		}
		return true; 
	}

	function db_read() {
		return get_empl_experience($this->entity);
	}

	function delete_check() {
		return true;
	}
	
	function db_delete() {
		if (delete_empl_experience($this->selected_id, $this->class, $this->entity)) {
			
		}
		return false;
	}
}

?>