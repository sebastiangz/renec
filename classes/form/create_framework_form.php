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
 * Formulario para crear un marco de competencias RENEC
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
 * Formulario para crear un marco de competencias RENEC
 */
class create_framework_form extends \moodleform {

    /**
     * Definir formulario
     */
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;
        
        // Sección general
        $mform->addElement('header', 'general', get_string('create_framework', 'local_renec'));
        
        // Información
        $mform->addElement('static', 'description', '', get_string('create_framework_instructions', 'local_renec'));
        
        // Nombre del marco
        $mform->addElement('text', 'shortname', get_string('framework_name', 'local_renec'), ['size' => 50]);
        $mform->setType('shortname', PARAM_TEXT);
        $mform->setDefault('shortname', 'RENEC');
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('shortname', 'framework_name', 'local_renec');
        
        // ID del marco
        $mform->addElement('text', 'idnumber', get_string('framework_idnumber', 'local_renec'), ['size' => 30]);
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->setDefault('idnumber', 'RENEC-PRINCIPAL');
        $mform->addRule('idnumber', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('idnumber', 'framework_idnumber', 'local_renec');
        
        // Descripción del marco
        $mform->addElement('textarea', 'description', get_string('framework_description', 'local_renec'), 
                          ['rows' => 3, 'cols' => 50]);
        $mform->setType('description', PARAM_TEXT);
        $mform->setDefault('description', 'Marco Nacional de Estándares de Competencia (RENEC) - Catálogo oficial de competencias laborales reconocidas por CONOCER');
        $mform->addHelpButton('description', 'framework_description', 'local_renec');
        
        // Selección de escala
        $scales = local_renec_get_scales();
        $mform->addElement('select', 'scaleid', get_string('selectscale', 'local_renec'), $scales);
        $mform->setDefault('scaleid', 1); // Escala predeterminada
        $mform->addRule('scaleid', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('scaleid', 'selectscale', 'local_renec');
        
        // Botones
        $this->add_action_buttons(true, get_string('create_framework_submit', 'local_renec'));
    }
    
    /**
     * Validación personalizada del formulario
     * 
     * @param array $data Los datos del formulario
     * @param array $files Los archivos del formulario
     * @return array Errores de validación
     */
    public function validation($data, $files) {
        global $DB;
        
        $errors = parent::validation($data, $files);
        
        // Verificar que el ID no esté duplicado
        if (!empty($data['idnumber'])) {
            $existing = $DB->get_record('competency_framework', ['idnumber' => $data['idnumber']]);
            if ($existing) {
                $errors['idnumber'] = get_string('error_idnumber_exists', 'local_renec');
            }
        }
        
        // Verificar que la escala existe
        if (!empty($data['scaleid'])) {
            $scale = $DB->get_record('scale', ['id' => $data['scaleid']]);
            if (!$scale) {
                $errors['scaleid'] = get_string('error_scale_not_exists', 'local_renec');
            }
        }
        
        return $errors;
    }
}
