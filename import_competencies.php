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
 * Página para importar competencias RENEC
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/renec/lib.php');
require_once($CFG->dirroot . '/local/renec/classes/form/import_competencies_form.php');

// Verificar contexto y permisos
$context = context_system::instance();
require_login();
require_capability('local/renec:manage', $context);

// Obtener marco preseleccionado si existe
$frameworkid = optional_param('frameworkid', 0, PARAM_INT);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/renec/import_competencies.php', ['frameworkid' => $frameworkid]));
$PAGE->set_title(get_string('import_competencies', 'local_renec'));
$PAGE->set_heading(get_string('import_competencies', 'local_renec'));
$PAGE->set_pagelayout('admin');

// Crear formulario
$mform = new \local_renec\form\import_competencies_form(null, ['frameworkid' => $frameworkid]);

// Inicializar resultado
$importresults = null;

// Procesar formulario
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/renec/index.php'));
} else if ($data = $mform->get_data()) {
    // Obtener marco
    $framework_id = $data->frameworkid;
    
    // Verificar que el marco existe
    $framework = \core_competency\competency_framework::get_record(['id' => $framework_id]);
    if (!$framework) {
        redirect(
            new moodle_url('/local/renec/import_competencies.php'),
            get_string('error_framework_not_exists', 'local_renec'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
    
    // Obtener niveles existentes en el marco
    $existing_levels = [];
    $competencies = \core_competency\competency::get_records(['competencyframeworkid' => $framework_id, 'parentid' => 0]);
    foreach ($competencies as $comp) {
        $idnumber = $comp->get('idnumber');
        // Detectar si es un nivel RENEC
        if (strpos($idnumber, 'RENEC-NIVEL-') === 0 || $idnumber === 'RENEC-SIN-NIVEL') {
            $existing_levels[$idnumber] = $comp->get('id');
        }
    }

    // Importar competencias desde archivo CSV
    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();
    $draftitemid = $data->competenciesfile;
    $files = $fs->get_area_files(
        $usercontext->id,
        'user',
        'draft',
        $draftitemid,
        'sortorder',
        false
    );
    
    if (!empty($files)) {
        $file = reset($files);
        $filename = $file->get_filename();
        $filepath = $CFG->tempdir . '/local_renec_comp_' . time() . '_' . $filename;
        
        try {
            $file->copy_content_to($filepath);
            
            // Opciones adicionales
            $encoding = isset($data->encoding) ? $data->encoding : 'UTF-8';
            $delimiter = isset($data->delimiter) ? $data->delimiter : 'comma';
            $overwrite = isset($data->overwrite) ? $data->overwrite : 0;
            $create_missing_levels = isset($data->create_missing_levels) ? $data->create_missing_levels : 1;
            
            // Importar competencias
            $results = local_renec_import_competencies_csv(
                $filepath, 
                $framework_id, 
                $existing_levels, 
                $encoding, 
                $delimiter, 
                $overwrite, 
                $create_missing_levels
            );
            
            // Limpiar archivo temporal
            unlink($filepath);
            
            $importresults = [
                'success' => (!empty($results['competencies']['created']) && empty($results['error'])),
                'competencies' => $results['competencies'],
                'messages' => $results['messages'],
                'framework_id' => $framework_id
            ];
            
        } catch (Exception $e) {
            $importresults = [
                'success' => false,
                'messages' => ["Error al procesar el archivo de competencias: " . $e->getMessage()],
                'framework_id' => $framework_id
            ];
        }
    } else {
        $importresults = [
            'success' => false,
            'messages' => ["Error: No se encontró ningún archivo de competencias"],
            'framework_id' => $framework_id
        ];
    }
}

// Mostrar página
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('import_competencies', 'local_renec'));

// Mostrar enlaces de navegación
echo html_writer::start_div('renec-navigation');
echo html_writer::link(
    new moodle_url('/local/renec/index.php'),
    get_string('back_to_menu', 'local_renec'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

// Mostrar información del plugin
echo html_writer::start_div('info');
echo html_writer::tag('p', get_string('import_competencies_info', 'local_renec'));
echo html_writer::end_div();

// Mostrar resultados de la importación si los hay
if ($importresults) {
    echo $OUTPUT->heading(get_string('importresults', 'local_renec'), 3);
    
    echo html_writer::start_div('import-results alert ' . ($importresults['success'] ? 'alert-success' : 'alert-danger'));
    
    // Competencias
    if (!empty($importresults['competencies'])) {
        echo html_writer::tag('p', get_string('competenciestotal', 'local_renec', $importresults['competencies']['total']));
        echo html_writer::tag('p', get_string('competenciescreated', 'local_renec', $importresults['competencies']['created']));
        echo html_writer::tag('p', get_string('competenciesskipped', 'local_renec', $importresults['competencies']['skipped']));
        echo html_writer::tag('p', get_string('competencieserrors', 'local_renec', $importresults['competencies']['errors']));
        
        // Competencias por nivel
        echo html_writer::tag('h4', get_string('competenciesbylevel', 'local_renec'));
        echo html_writer::start_tag('ul');
        foreach ($importresults['competencies']['by_level'] as $level => $count) {
            if ($count > 0) {  // Solo mostrar niveles con competencias
                if ($level == 0) {
                    $levelname = get_string('levelunassigned', 'local_renec');
                } else {
                    $levelname = get_string('levelnumber', 'local_renec', $level);
                }
                echo html_writer::tag('li', $levelname . ': ' . $count);
            }
        }
        echo html_writer::end_tag('ul');
    }
    
    // Mensajes
    if (!empty($importresults['messages'])) {
        echo html_writer::tag('h4', get_string('detailedlog', 'local_renec'));
        echo html_writer::start_tag('ul', ['class' => 'list-unstyled']);
        foreach ($importresults['messages'] as $message) {
            echo html_writer::tag('li', $message);
        }
        echo html_writer::end_tag('ul');
    }
    
    echo html_writer::end_div();
    
    // Enlace para ver el marco de competencias
    if ($importresults['success'] && !empty($importresults['framework_id'])) {
        echo html_writer::start_div('buttons');
        echo html_writer::link(
            new moodle_url('/admin/tool/lp/competencyframeworks.php'),
            get_string('viewframeworks', 'local_renec'),
            ['class' => 'btn btn-primary']
        );
        echo html_writer::end_div();
    }
}

// Mostrar formulario
$mform->display();

echo $OUTPUT->footer();