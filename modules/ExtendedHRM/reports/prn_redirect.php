<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/
$path_to_root = "../../..";
global $page_security, $save_report_selections;
$page_security = 'SA_OPEN';	// this level is later overriden in rep file
include_once($path_to_root . "/includes/session.inc");

if (isset($save_report_selections) && $save_report_selections > 0 && isset($_POST['REP_ID'])) {	// save parameters from Report Center
	for($i=0; $i<12; $i++) { // 2013-01-16 Joe Hunt
		if (isset($_POST['PARAM_'.$i]) && !is_array($_POST['PARAM_'.$i])) {
			$rep = $_POST['REP_ID'];
			setcookie("select[$rep][$i]", $_POST['PARAM_'.$i], time()+60*60*24*$save_report_selections); // days from $save_report_selections
		}	
	}
}	

if (isset($_GET['xls']))
{
	$filename = $_GET['filename'];
	$unique_name = preg_replace('/[^0-9a-z.]/i', '', $_GET['unique']);
	$path =  company_path(). '/pdf_files/';
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=$filename" );
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	header("Pragma: public");
	echo file_get_contents($path.$unique_name);
	exit();
}
elseif (isset($_GET['xml']))
{
	$filename = $_GET['filename'];
	$unique_name = preg_replace('/[^0-9a-z.]/i', '', $_GET['unique']);
	$path =  company_path(). '/pdf_files/';
	header("content-type: text/xml");
	header("Content-Disposition: attachment; filename=$filename");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
	header("Pragma: public");
	echo file_get_contents($path.$unique_name);
	exit();
}
	
if (!isset($_POST['REP_ID'])) {	// print link clicked
	$def_pars = array(0, 0, '', '', 0, '', '', 0); //default values
	$rep = $_POST['REP_ID'] = $_GET['REP_ID'];
	for($i=0; $i<8; $i++) {
		$_POST['PARAM_'.$i] = isset($_GET['PARAM_'.$i]) 
			? $_GET['PARAM_'.$i] : $def_pars[$i];
	}
}

$rep = preg_replace('/[^a-z_0-9]/i', '', $_POST['REP_ID']);
if($rep == 808 || $rep == 806 || $rep == 805 || $rep == 804  || $rep == 803 || $rep == 802 || $rep == 801){
	$rep_file = find_custom_file("/modules/ExtendedHRM/reports/rep$rep.php");

	if ($rep_file) {
		require($rep_file);
	} else
		display_error("Cannot find report  file '$rep'". $rep_file);
}else{
	$rep_file = find_custom_file("/modules/ExtendedHRM/reports/rep807.php");

	if ($rep_file) {
	$_POST['allowanc_id'] = $rep;
		require($rep_file);
	} else
		display_error("Cannot find report file '$rep'". $rep_file);
}
exit();

?>