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
 * Página para crear un marco de competencias RENEC
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/renec/lib.php');
require_once($CFG->dirroot . '/local/renec/classes/form/create_framework_form.php');

// Verificar contexto y permisos
$context = context_system::instance();
require_login();
require_capability('local/renec:manage', $context);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/renec/create_framework.php'));
$PAGE->set_title(get_string('create_framework', 'local_renec'));
$PAGE->set_heading(get_string('create_framework', 'local_renec'));
$PAGE->set_pagelayout('admin');

// Crear formulario
$mform = new \local_renec\form\create_framework_form();

// Inicializar resultado
$result = null;
$framework_id = null;

// Procesar formulario
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/renec/index.php'));
} else if ($data = $mform->get_data()) {
    try {
        // Crear marco directamente usando SQL - evitar problemas de escala
        global $DB, $USER;
        
        // Verificar si ya existe un marco con este idnumber
        if ($DB->record_exists('competency_framework', array('idnumber' => $data->idnumber))) {
            throw new \moodle_exception(get_string('error_idnumber_exists', 'local_renec'));
        }
        
        // Verificar que la escala existe
        $scale = $DB->get_record('scale', array('id' => $data->scaleid));
        if (!$scale) {
            throw new \moodle_exception(get_string('error_scale_not_exists', 'local_renec'));
        }
        
        // Obtener valores de la escala
        $scalevalues = explode(',', $scale->scale);
        $scalecount = count($scalevalues);
        
        // Asegurar que hay suficientes valores para configurar por defecto y dominado
        if ($scalecount < 2) {
            throw new \moodle_exception('Error: La escala debe tener al menos 2 valores');
        }
        
        // Configurar por defecto y dominado
        $defaultvalue = min(3, $scalecount); // Por defecto el tercero o el último si hay menos de 3
        $proficientvalue = $scalecount; // Dominado siempre el último
        
        // Construir configuración de escala
        $scaleconfig = [
            'scaleid' => $data->scaleid,
            'minproficiencyid' => $defaultvalue,
            'defaultid' => $defaultvalue
        ];
        
        // Crear registro en la base de datos
        $now = time();
        $framework = new \stdClass();
        $framework->shortname = $data->shortname;
        $framework->idnumber = $data->idnumber;
        $framework->description = $data->description;
        $framework->descriptionformat = FORMAT_HTML;
        $framework->scaleid = $data->scaleid;
        $framework->scaleconfiguration = json_encode($scaleconfig);
        $framework->visible = 1;
        $framework->contextid = $context->id;
        $framework->timemodified = $now;
        $framework->timecreated = $now;
        $framework->usermodified = $USER->id;
        
        // Insertar en la base de datos
        $framework_id = $DB->insert_record('competency_framework', $framework);
        
        $result = [
            'success' => true,
            'message' => get_string('framework_created_success', 'local_renec', $framework_id),
            'framework_id' => $framework_id
        ];
        
        // Después de crear el marco exitosamente, redirigir a la página de importación de niveles
        redirect(
            new moodle_url('/local/renec/import_levels.php', ['frameworkid' => $framework_id]),
            get_string('framework_created_success', 'local_renec', $framework_id),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        
    } catch (Exception $e) {
        $result = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Mostrar página
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('create_framework', 'local_renec'));

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
echo html_writer::tag('p', get_string('create_framework_info', 'local_renec'));
echo html_writer::end_div();

// Mostrar resultado si existe
if ($result) {
    $notification_type = $result['success'] ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_ERROR;
    echo $OUTPUT->notification($result['message'], $notification_type);
    
    if ($result['success'] && !empty($result['framework_id'])) {
        echo html_writer::start_div('buttons');
        echo html_writer::link(
            new moodle_url('/local/renec/import_levels.php', ['frameworkid' => $result['framework_id']]),
            get_string('continue_to_import_levels', 'local_renec'),
            ['class' => 'btn btn-primary']
        );
        echo html_writer::end_div();
    }
}

// Mostrar formulario
$mform->display();

echo $OUTPUT->footer();