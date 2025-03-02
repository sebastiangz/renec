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
 * Formulario para importar niveles de competencia RENEC
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_renec\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/renec/lib.php');

/**
 * Formulario para importar niveles de competencia RENEC
 */
class import_levels_form extends \moodleform {

    /**
     * Definir formulario
     */
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;
        
        // Obtener marco preseleccionado si existe
        $frameworkid = optional_param('frameworkid', 0, PARAM_INT);
        
        // ID para archivos
        $mform->addElement('hidden', 'importid', time());
        $mform->setType('importid', PARAM_INT);
        
        // Sección general
        $mform->addElement('header', 'general', get_string('import_levels', 'local_renec'));
        
        // Información
        $mform->addElement('static', 'description', '', get_string('import_levels_instructions', 'local_renec'));
        
        // Selección de marcos de competencias existentes
        $frameworks = \core_competency\competency_framework::get_records([], 'shortname', 'ASC');
        $framework_options = array();
        foreach ($frameworks as $framework) {
            $framework_options[$framework->get('id')] = $framework->get('shortname') . ' (' . $framework->get('idnumber') . ')';
        }
        
        $mform->addElement('select', 'frameworkid', get_string('select_framework', 'local_renec'), $framework_options);
        if ($frameworkid > 0) {
            $mform->setDefault('frameworkid', $frameworkid);
        }
        $mform->addRule('frameworkid', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('frameworkid', 'select_framework', 'local_renec');
        
        // Opciones de importación
        $mform->addElement('advcheckbox', 'create_default_levels', get_string('create_default_levels', 'local_renec'));
        $mform->setDefault('create_default_levels', 0);
        $mform->addHelpButton('create_default_levels', 'create_default_levels', 'local_renec');
        
        // Archivo CSV
        $options = [
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['.csv']
        ];
        
        $mform->addElement('filepicker', 'levelsfile', get_string('levelsfile', 'local_renec'), null, $options);
        $mform->disabledIf('levelsfile', 'create_default_levels', 'checked');
        $mform->addHelpButton('levelsfile', 'levelsfile', 'local_renec');
        
        // Opciones avanzadas
        $mform->addElement('header', 'advancedoptions', get_string('advancedoptions', 'local_renec'));
        
        // Selección de formato de archivo
        $encodings = \core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'local_renec'), $encodings);
        $mform->setDefault('encoding', 'UTF-8');
        $mform->addHelpButton('encoding', 'encoding', 'local_renec');
        $mform->setAdvanced('encoding');
        
        // Selector de delimitador CSV
        $delimiters = array(
            'comma' => get_string('comma', 'local_renec'),
            'semicolon' => get_string('semicolon', 'local_renec'),
            'tab' => get_string('tab', 'local_renec'),
            'colon' => get_string('colon', 'local_renec')
        );
        $mform->addElement('select', 'delimiter', get_string('delimiter', 'local_renec'), $delimiters);
        $mform->setDefault('delimiter', 'comma');
        $mform->addHelpButton('delimiter', 'delimiter', 'local_renec');
        $mform->setAdvanced('delimiter');
        
        // Botones
        $this->add_action_buttons(true, get_string('import_levels_submit', 'local_renec'));
    }
    
    /**
     * Validación personalizada del formulario
     * 
     * @param array $data Los datos del formulario
     * @param array $files Los archivos del formulario
     * @return array Errores de validación
     */
    public function validation($data, $files) {
        global $USER, $DB;
        
        $errors = parent::validation($data, $files);
        
        // Verificar que el marco existe
        if (!empty($data['frameworkid'])) {
            $framework = $DB->get_record('competency_framework', ['id' => $data['frameworkid']]);
            if (!$framework) {
                $errors['frameworkid'] = get_string('error_framework_not_exists', 'local_renec');
            }
        }
        
        // Si no se marca crear niveles por defecto, se requiere archivo
        if (empty($data['create_default_levels'])) {
            // Validar archivo CSV
            $usercontext = \context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['levelsfile'], 'sortorder', false);
            
            if (count($files) < 1) {
                $errors['levelsfile'] = get_string('required');
            } else {
                $file = reset($files);
                $filename = $file->get_filename();
                
                // Verificar extensión
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ($extension !== 'csv') {
                    $errors['levelsfile'] = get_string('invalidfileextension', 'local_renec');
                }
            }
        }
        
        return $errors;
    }
}