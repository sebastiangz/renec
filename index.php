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
 * Página principal (menú) para el plugin RENEC
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/renec/lib.php');

// Verificar contexto y permisos
$context = context_system::instance();
require_login();
require_capability('local/renec:manage', $context);

// Configurar página
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/renec/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_renec'));
$PAGE->set_heading(get_string('pluginname', 'local_renec'));
$PAGE->set_pagelayout('admin');

// Mostrar página
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_renec'));

// Mostrar información del plugin
echo html_writer::start_div('info');
echo html_writer::tag('p', get_string('plugininfo', 'local_renec'));
echo html_writer::end_div();

// Menú de opciones
echo $OUTPUT->heading(get_string('menu_options', 'local_renec'), 3);

echo html_writer::start_div('renec-menu');

// 1. Crear nuevo marco de competencias
echo html_writer::start_div('renec-menu-item');
echo html_writer::start_tag('h4');
echo html_writer::link(
    new moodle_url('/local/renec/create_framework.php'),
    get_string('create_framework', 'local_renec')
);
echo html_writer::end_tag('h4');
echo html_writer::tag('p', get_string('create_framework_desc', 'local_renec'));
echo html_writer::end_div();

// 2. Importar niveles a un marco existente
echo html_writer::start_div('renec-menu-item');
echo html_writer::start_tag('h4');
echo html_writer::link(
    new moodle_url('/local/renec/import_levels.php'),
    get_string('import_levels', 'local_renec')
);
echo html_writer::end_tag('h4');
echo html_writer::tag('p', get_string('import_levels_desc', 'local_renec'));
echo html_writer::end_div();

// 3. Importar competencias a un marco existente
echo html_writer::start_div('renec-menu-item');
echo html_writer::start_tag('h4');
echo html_writer::link(
    new moodle_url('/local/renec/import_competencies.php'),
    get_string('import_competencies', 'local_renec')
);
echo html_writer::end_tag('h4');
echo html_writer::tag('p', get_string('import_competencies_desc', 'local_renec'));
echo html_writer::end_div();

// 4. Ver marcos de competencias
echo html_writer::start_div('renec-menu-item');
echo html_writer::start_tag('h4');
echo html_writer::link(
    new moodle_url('/admin/tool/lp/competencyframeworks.php'),
    get_string('view_frameworks', 'local_renec')
);
echo html_writer::end_tag('h4');
echo html_writer::tag('p', get_string('view_frameworks_desc', 'local_renec'));
echo html_writer::end_div();

echo html_writer::end_div();

// Flujo recomendado
echo $OUTPUT->heading(get_string('recommended_workflow', 'local_renec'), 3);

echo html_writer::start_div('alert alert-info');
echo html_writer::tag('p', get_string('recommended_workflow_desc', 'local_renec'));
echo html_writer::start_tag('ol');
echo html_writer::tag('li', get_string('workflow_step1', 'local_renec'));
echo html_writer::tag('li', get_string('workflow_step2', 'local_renec'));
echo html_writer::tag('li', get_string('workflow_step3', 'local_renec') . 
                     '<br><small>Los archivos CSV deben seguir el formato con columnas: "Número ID paterno", "Número ID", "Nombre_corto", "Descripción", etc.</small>');
echo html_writer::end_tag('ol');
echo html_writer::end_div();

// Formato de archivos CSV
echo $OUTPUT->heading(get_string('csv_format', 'local_renec'), 3);

echo html_writer::start_div('alert alert-secondary');
echo html_writer::tag('p', get_string('csv_format_desc', 'local_renec'));

// Tabla con formato de archivos
echo html_writer::start_tag('table', ['class' => 'table table-sm table-bordered']);

// Encabezados
echo html_writer::start_tag('thead');
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('csv_purpose', 'local_renec'));
echo html_writer::tag('th', get_string('csv_columns', 'local_renec'));
echo html_writer::tag('th', get_string('csv_example', 'local_renec'));
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thead');

// Cuerpo de la tabla
echo html_writer::start_tag('tbody');

// Archivo de marco
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('csv_framework', 'local_renec'));
echo html_writer::tag('td', '"Número ID paterno", "Número ID", "Nombre_corto", "Descripción", "Es estructura"');
echo html_writer::tag('td', '0, RENEC-PRINCIPAL, "Marco RENEC", "Marco de competencias RENEC", 1');
echo html_writer::end_tag('tr');

// Archivo de niveles
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('csv_levels', 'local_renec'));
echo html_writer::tag('td', '"Número ID paterno", "Número ID", "Nombre_corto", "Descripción", "Es estructura"');
echo html_writer::tag('td', 'RENEC-PRINCIPAL, RENEC-NIVEL-1, "Nivel 1", "Competencias de nivel básico", 1');
echo html_writer::end_tag('tr');

// Archivo de competencias
echo html_writer::start_tag('tr');
echo html_writer::tag('td', get_string('csv_competencies', 'local_renec'));
echo html_writer::tag('td', '"Número ID paterno", "Número ID", "Nombre_corto", "Descripción", "Es estructura"');
echo html_writer::tag('td', 'RENEC-NIVEL-1, EC0001, "Atención al cliente", "Competencia de atención...", 0');
echo html_writer::end_tag('tr');

echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');

echo html_writer::end_div();

echo $OUTPUT->footer();