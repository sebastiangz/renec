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
echo html_writer::tag('li', get_string('workflow_step3', 'local_renec'));
echo html_writer::end_tag('ol');
echo html_writer::end_div();

echo $OUTPUT->footer();
