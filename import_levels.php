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
 * Página para importar niveles de competencia RENEC
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/renec/lib.php');
require_once($CFG->dirroot . '/local/renec/classes/form/import_levels_form.php');

// Verificar contexto y permisos
$context = context_system::instance();
require_login();
require_capability('local/renec:manage', $context);

// Obtener marco preseleccionado si existe
$frameworkid = optional_param('frameworkid', 0, PARAM_INT);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/renec/import_levels.php', ['frameworkid' => $frameworkid]));
$PAGE->set_title(get_string('import_levels', 'local_renec'));
$PAGE->set_heading(get_string('import_levels', 'local_renec'));
$PAGE->set_pagelayout('admin');

// Crear formulario
$mform = new \local_renec\form\import_levels_form(null, ['frameworkid' => $frameworkid]);

// Inicializar resultado
$importresults = null;

// Procesar formulario
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/renec/index.php'));
} else if ($data = $mform->get_data()) {
    // Obtener marcos y niveles
    $framework_id = $data->frameworkid;
    
    // Verificar que el marco existe
    $framework = \core_competency\competency_framework::get_record(['id' => $framework_id]);
    if (!$framework) {
        redirect(
            new moodle_url('/local/renec/import_levels.php'),
            get_string('error_framework_not_exists', 'local_renec'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
    
    // Determinar si se crean niveles por defecto o se importan desde CSV
    if (!empty($data->create_default_levels)) {
        // Crear niveles por defecto
        list($levelids, $results) = local_renec_create_default_levels($framework_id, true);
        $importresults = [
            'success' => (!empty($levelids) && empty($results['errors'])),
            'levels' => $levelids,
            'messages' => $results['messages'],
            'framework_id' => $framework_id
        ];
    } else {
        // Importar niveles desde archivo CSV
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $draftitemid = file_get_submitted_draft_itemid('levelsfile');
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
            $filepath = $CFG->tempdir . '/local_renec_levels_' . time() . '_' . $filename;
            
            try {
                $file->copy_content_to($filepath);
                
                // Opciones adicionales
                $encoding = isset($data->encoding) ? $data->encoding : 'UTF-8';
                $delimiter = isset($data->delimiter) ? $data->delimiter : 'comma';
                
                // Importar niveles
                list($levelids, $results) = local_renec_import_levels($filepath, $framework_id, $encoding, $delimiter);
                
                // Limpiar archivo temporal
                unlink($filepath);
                
                $importresults = [
                    'success' => (!empty($levelids) && empty($results['errors'])),
                    'levels' => $levelids,
                    'messages' => $results['messages'],
                    'framework_id' => $framework_id
                ];
                
            } catch (Exception $e) {
                $importresults = [
                    'success' => false,
                    'messages' => ["Error al procesar el archivo de niveles: " . $e->getMessage()],
                    'framework_id' => $framework_id
                ];
            }
        } else {
            $importresults = [
                'success' => false,
                'messages' => ["Error: No se encontró ningún archivo de niveles"],
                'framework_id' => $framework_id
            ];
        }
    }
    
    // Si la importación fue exitosa, redirigir a la página de importación de competencias
    if (!empty($importresults['success']) && !empty($importresults['framework_id'])) {
        redirect(
            new moodle_url('/local/renec/import_competencies.php', ['frameworkid' => $importresults['framework_id']]),
            get_string('levels_imported_success', 'local_renec', count($importresults['levels'])),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

// Mostrar página
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('import_levels', 'local_renec'));

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
echo html_writer::tag('p', get_string('import_levels_info', 'local_renec'));
echo html_writer::end_div();

// Mostrar resultados de la importación si los hay
if ($importresults) {
    echo $OUTPUT->heading(get_string('importresults', 'local_renec'), 3);
    
    echo html_writer::start_div('import-results alert ' . ($importresults['success'] ? 'alert-success' : 'alert-danger'));
    
    // Niveles
    if (!empty($importresults['levels'])) {
        echo html_writer::tag('p', get_string('levelscreated', 'local_renec', count($importresults['levels'])));
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
    
    // Enlace para continuar
    if ($importresults['success'] && !empty($importresults['framework_id'])) {
        echo html_writer::start_div('buttons');
        echo html_writer::link(
            new moodle_url('/local/renec/import_competencies.php', ['frameworkid' => $importresults['framework_id']]),
            get_string('continue_to_import_competencies', 'local_renec'),
            ['class' => 'btn btn-primary']
        );
        echo html_writer::end_div();
    }
}

// Mostrar formulario
$mform->display();

echo $OUTPUT->footer();