<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'local_renec', language 'en'
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'RENEC/CONOCER Competencies Importer';
$string['plugininfo'] = 'This plugin allows you to import competencies from the National Registry of Competency Standards (RENEC) into Moodle. Follow the step-by-step process: first create a competency framework, then import the levels, and finally import the individual competencies.';

// Permissions
$string['renec:manage'] = 'Manage RENEC competencies';

// Main menu
$string['menu_options'] = 'Available options';
$string['create_framework'] = 'Step 1: Create competency framework';
$string['create_framework_desc'] = 'Create a new RENEC competency framework to organize your standards.';
$string['import_levels'] = 'Step 2: Import levels';
$string['import_levels_desc'] = 'Import competency levels (1 to 5) into an existing framework.';
$string['import_competencies'] = 'Step 3: Import competencies';
$string['import_competencies_desc'] = 'Import individual competencies organized by levels.';
$string['view_frameworks'] = 'View competency frameworks';
$string['view_frameworks_desc'] = 'View existing competency frameworks and their structure.';

// Recommended workflow
$string['recommended_workflow'] = 'Recommended workflow';
$string['recommended_workflow_desc'] = 'For a correct import, it is recommended to follow these steps in order:';
$string['workflow_step1'] = 'Create a new RENEC competency framework';
$string['workflow_step2'] = 'Import levels from a file or create default levels';
$string['workflow_step3'] = 'Import individual competencies from the RENEC CSV file';

// CSV format
$string['csv_format'] = 'CSV file format';
$string['csv_format_desc'] = 'CSV files must have the following format to be imported correctly:';
$string['csv_purpose'] = 'Purpose';
$string['csv_columns'] = 'Required columns';
$string['csv_example'] = 'Example';
$string['csv_framework'] = 'Competency framework';
$string['csv_levels'] = 'Levels';
$string['csv_competencies'] = 'Competencies';

// Framework creation form
$string['create_framework_info'] = 'Create a new competency framework to organize RENEC standards. This is the first step of the import process.';
$string['create_framework_instructions'] = 'Complete the following fields to create a new RENEC competency framework. Make sure to select an appropriate scale.';
$string['framework_name'] = 'Framework name';
$string['framework_name_help'] = 'Short name that identifies the competency framework. We recommend using "RENEC" or similar.';
$string['framework_description'] = 'Framework description';
$string['framework_description_help'] = 'Detailed description of the purpose of the competency framework.';
$string['framework_idnumber'] = 'Framework ID';
$string['framework_idnumber_help'] = 'Unique identifier for the framework. We recommend using "RENEC-PRINCIPAL" or similar.';
$string['selectscale'] = 'Competency scale';
$string['selectscale_help'] = 'Select the scale that will be used to evaluate competencies. A 5-level scale is recommended.';
$string['create_framework_submit'] = 'Create framework';
$string['framework_created_success'] = 'Competency framework successfully created with ID: {$a}';
$string['continue_to_import_levels'] = 'Continue to level import';
$string['back_to_menu'] = 'Back to main menu';

// Level import form
$string['import_levels_info'] = 'Import competency levels into an existing framework. This is the second step of the import process.';
$string['import_levels_instructions'] = 'Select an existing competency framework and upload a CSV file with competency levels or create default levels.';
$string['select_framework'] = 'Competency framework';
$string['select_framework_help'] = 'Select the competency framework where you want to import levels or competencies.';
$string['create_default_levels'] = 'Create default levels';
$string['create_default_levels_help'] = 'If checked, levels 1 to 5 and the "Unassigned" level will be created automatically, without needing to upload a CSV file.';
$string['levelsfile'] = 'Levels CSV file';
$string['levelsfile_help'] = 'Select the CSV file with competency levels. This file must include columns such as "Número ID paterno", "Número ID", "Nombre_corto", "Descripción" and "Es estructura".';
$string['import_levels_submit'] = 'Import levels';
$string['levels_imported_success'] = '{$a} levels successfully imported';
$string['continue_to_import_competencies'] = 'Continue to competency import';

// Competency import form
$string['import_competencies_info'] = 'Import individual competencies into an existing framework. This is the third and final step of the import process.';
$string['import_competencies_instructions'] = 'Select an existing competency framework and upload a CSV file with RENEC competencies.';
$string['competenciesfile'] = 'Competencies CSV file';
$string['competenciesfile_help'] = 'Select the CSV file with RENEC competencies. This file must include columns such as "Número ID paterno", "Número ID", "Nombre_corto", "Descripción" and "Es estructura".';
$string['create_missing_levels'] = 'Create missing levels';
$string['create_missing_levels_help'] = 'If checked, levels that do not exist in the framework but are required by competencies in the CSV file will be created automatically.';
$string['import_competencies_submit'] = 'Import competencies';

// Advanced options and common fields
$string['advancedoptions'] = 'Advanced options';
$string['overwrite'] = 'Overwrite existing competencies';
$string['overwrite_help'] = 'If checked, existing competencies with the same ID will be updated. If not checked, they will be skipped.';
$string['encoding'] = 'File encoding';
$string['encoding_help'] = 'Select the encoding of the CSV file. If you see strange characters, try different encodings.';
$string['delimiter'] = 'CSV delimiter';
$string['delimiter_help'] = 'Select the character that separates columns in the CSV file.';
$string['comma'] = 'Comma (,)';
$string['semicolon'] = 'Semicolon (;)';
$string['tab'] = 'Tab';
$string['colon'] = 'Colon (:)';

// Import results
$string['importresults'] = 'Import results';
$string['levelscreated'] = '{$a} levels created';
$string['competenciestotal'] = 'Total competencies in file: {$a}';
$string['competenciescreated'] = 'Competencies created: {$a}';
$string['competenciesskipped'] = 'Competencies skipped (already existing): {$a}';
$string['competencieserrors'] = 'Errors creating competencies: {$a}';
$string['competenciesbylevel'] = 'Competencies by level';
$string['levelnumber'] = 'Level {$a}';
$string['levelunassigned'] = 'Unassigned';
$string['detailedlog'] = 'Detailed log';
$string['viewframeworks'] = 'View competency frameworks';

// Errors
$string['invalidfileextension'] = 'The file must have a .csv extension';
$string['missingrequiredcolumns'] = 'Required columns are missing in the CSV file';
$string['errorreadingfile'] = 'Error reading CSV file';
$string['error_framework_not_exists'] = 'The selected competency framework does not exist';
$string['error_idnumber_exists'] = 'A competency framework with this ID already exists';
$string['error_scale_not_exists'] = 'The selected scale does not exist';
