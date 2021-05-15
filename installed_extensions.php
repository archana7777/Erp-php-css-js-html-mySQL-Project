<?php

/* List of installed additional extensions. If extensions are added to the list manually
	make sure they have unique and so far never used extension_ids as a keys,
	and $next_extension_id is also updated. More about format of this file yo will find in 
	FA extension system documentation.
*/

$next_extension_id = 10; // unique id for next installed extension

$installed_extensions = array (
  1 => 
  array (
    'name' => 'Theme Anterp',
    'package' => 'anterp',
    'version' => '2.4.0-1',
    'type' => 'theme',
    'active' => false,
    'path' => 'themes/anterp',
  ),
  2 => 
  array (
    'name' => 'Theme Bluecollar',
    'package' => 'bluecollar',
    'version' => '2.4.0-3',
    'type' => 'theme',
    'active' => false,
    'path' => 'themes/bluecollar',
  ),
  3 => 
  array (
    'name' => 'Theme Exclusive',
    'package' => 'exclusive',
    'version' => '2.4.0-1',
    'type' => 'theme',
    'active' => false,
    'path' => 'themes/exclusive',
  ),
  4 => 
  array (
    'name' => 'Theme Exclusive for Dashboard',
    'package' => 'exclusive_db',
    'version' => '2.4.0-1',
    'type' => 'theme',
    'active' => false,
    'path' => 'themes/exclusive_db',
  ),
  5 => 
  array (
    'name' => 'Theme Fancy',
    'package' => 'fancy',
    'version' => '2.4.0-1',
    'type' => 'theme',
    'active' => false,
    'path' => 'themes/fancy',
  ),
  7 => 
  array (
    'name' => 'Cash Flow Statement Report',
    'package' => 'rep_cash_flow_statement',
    'version' => '2.4.0-2',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_cash_flow_statement',
  ),
  8 => 
  array (
    'name' => 'Annual expense breakdown report',
    'package' => 'rep_annual_expense_breakdown',
    'version' => '2.4.0-2',
    'type' => 'extension',
    'active' => false,
    'path' => 'modules/rep_annual_expense_breakdown',
  ),
  9 => 
  array (
    'package' => 'ExtendedHRM',
    'name' => 'Payroll',
    'version' => '2.4',
    'available' => '',
    'type' => 'extension',
    'path' => 'modules/ExtendedHRM',
    'active' => false,
  ),
);
