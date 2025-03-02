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
 * Funciones de biblioteca para el plugin RENEC
 *
 * @package    local_renec
 * @copyright  2025 educon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/competency/classes/competency_framework.php');
require_once($CFG->dirroot . '/competency/classes/competency.php');
require_once($CFG->libdir . '/csvlib.class.php');

/**
 * Función para añadir ítems de navegación
 *
 * @param global_navigation $navigation Objeto de navegación global
 */
function local_renec_extend_navigation(global_navigation $navigation) {
    if (has_capability('local/renec:manage', context_system::instance())) {
        $node = $navigation->add(
            get_string('pluginname', 'local_renec'),
            new moodle_url('/local/renec/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'renec',
            new pix_icon('i/competencies', '')
        );
        $node->showinflatnavigation = true;
    }
}

/**
 * Función para extender la navegación de administración
 *
 * @param navigation_node $adminnode Nodo de administración
 */
function local_renec_extend_settings_navigation(navigation_node $adminnode) {
    if (has_capability('local/renec:manage', context_system::instance())) {
        if ($settingsnode = $adminnode->find('competencies', navigation_node::TYPE_CONTAINER)) {
            $settingsnode->add(
                get_string('pluginname', 'local_renec'),
                new moodle_url('/local/renec/index.php'),
                navigation_node::TYPE_CUSTOM,
                null,
                'renec',
                new pix_icon('i/competencies', '')
            );
        }
    }
}

/**
 * Crea niveles de competencia predeterminados
 * 
 * @param int $frameworkid ID del marco de competencias
 * @param bool $createalllevels Si se deben crear todos los niveles predeterminados
 * @return array Mapa de niveles creados [id_nivel => id_competencia]
 */
function local_renec_create_default_levels($frameworkid, $createalllevels = true) {
    global $USER;
    
    $levelids = [];
    $results = [
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
        'messages' => []
    ];
    
    // Definir niveles predeterminados
    $level_descriptions = [
        0 => "Competencias sin nivel asignado",
        1 => "Competencias de nivel básico - Desempeño programado y rutinario",
        2 => "Competencias de nivel intermedio - Desempeño programado con capacidad de responder a imprevistos",
        3 => "Competencias de nivel avanzado - Desempeño variable, con supervisión de otros",
        4 => "Competencias de nivel superior - Desempeño variable, con responsabilidad por el trabajo de otros y recursos",
        5 => "Competencias de nivel especializado - Competencias de alto nivel de especialización"
    ];
    
    $levels_to_create = $createalllevels ? array_keys($level_descriptions) : [0];
    
    foreach ($levels_to_create as $level) {
        $name = ($level > 0) ? "RENEC Nivel $level" : "RENEC Sin Nivel";
        $idnumber = ($level > 0) ? "RENEC-NIVEL-$level" : "RENEC-SIN-NIVEL";
        
        try {
            // Verificar si ya existe
            $existing = \core_competency\competency::get_records([
                'competencyframeworkid' => $frameworkid,
                'idnumber' => $idnumber
            ]);
            
            if (!empty($existing)) {
                $levelid = reset($existing)->get('id');
                $levelids[$idnumber] = $levelid;
                $results['skipped']++;
                $results['messages'][] = "Nivel $name ya existe, omitido.";
                continue;
            }
            
            $levelid = local_renec_create_level($frameworkid, $name, $level_descriptions[$level], $idnumber);
            $levelids[$idnumber] = $levelid;
            $results['created']++;
            $results['messages'][] = "Nivel $name creado con ID: $levelid";
            
        } catch (Exception $e) {
            $results['errors']++;
            $results['messages'][] = "Error al crear nivel $name: " . $e->getMessage();
        }
    }
    
    return [$levelids, $results];
}

/**
 * Crea un nivel de competencia dentro de un marco
 * 
 * @param int $frameworkid ID del marco padre
 * @param string $name Nombre del nivel
 * @param string $description Descripción del nivel
 * @param string $idnumber Identificador único
 * @return int ID de la competencia creada (nivel)
 */
function local_renec_create_level($frameworkid, $name, $description, $idnumber) {
    global $USER;

    // Verificar si ya existe un nivel con este ID
    $existing = \core_competency\competency::get_records([
        'competencyframeworkid' => $frameworkid,
        'idnumber' => $idnumber
    ]);
    if (!empty($existing)) {
        return reset($existing)->get('id');
    }

    try {
        $level = new \core_competency\competency();
        $level->set('competencyframeworkid', $frameworkid);
        $level->set('shortname', $name);
        $level->set('description', $description);
        $level->set('descriptionformat', FORMAT_HTML);
        $level->set('idnumber', $idnumber);
        $level->set('parentid', 0); // Es un nivel principal
        $level->set('path', '/0/'); // Se actualizará automáticamente
        $level->set('sortorder', 0); // Se actualizará automáticamente
        $level->set('usermodified', $USER->id);
        $level->create();
        
        return $level->get('id');
    } catch (Exception $e) {
        error_log('Error al crear nivel de competencia: ' . $e->getMessage());
        throw new \moodle_exception('Error al crear nivel de competencia: ' . $e->getMessage());
    }
}

/**
 * Importa niveles de competencia desde un archivo CSV
 * 
 * @param string $filepath Ruta al archivo CSV de niveles
 * @param int $frameworkid ID del marco de competencias
 * @param string $encoding Codificación del archivo
 * @param string $delimiter Delimitador CSV
 * @return array Mapa de niveles creados [id_nivel => id_competencia]
 */
function local_renec_import_levels($filepath, $frameworkid, $encoding = 'UTF-8', $delimiter = 'comma') {
    global $USER;
    
    $levelids = [];
    $results = [
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
        'messages' => []
    ];
    
    // Verificar que el archivo existe
    if (!file_exists($filepath)) {
        $results['messages'][] = "Error: El archivo de niveles no existe: " . $filepath;
        $results['errors'] = 1;
        return [$levelids, $results];
    }
    
    // Convertir tipo de delimitador a carácter
    $delimiterChar = ',';
    switch ($delimiter) {
        case 'semicolon':
            $delimiterChar = ';';
            break;
        case 'tab':
            $delimiterChar = "\t";
            break;
        case 'colon':
            $delimiterChar = ':';
            break;
    }
    
    try {
        // Leer el contenido del archivo
        $content = file_get_contents($filepath);
        if ($content === false) {
            $results['messages'][] = "Error: No se pudo leer el archivo de niveles";
            $results['errors'] = 1;
            return [$levelids, $results];
        }
        
        // Convertir codificación si es necesario
        if ($encoding != 'UTF-8') {
            $content = iconv($encoding, 'UTF-8//IGNORE', $content);
        }
        
        // Procesar CSV
        $importid = csv_import_reader::get_new_iid('renec_levels');
        $csv = new csv_import_reader($importid, 'renec_levels');
        $csv->load_csv_content($content, $encoding, $delimiterChar);
        $csv->init();
        
        $columnnames = $csv->get_columns();
        
        // Verificar columnas necesarias
        $required_columns = [
            'Número ID paterno',
            'Número ID',
            'Nombre_corto',
            'Descripción',
            'Es estructura'
        ];
        
        $missing_columns = [];
        foreach ($required_columns as $column) {
            if (!in_array($column, $columnnames)) {
                $missing_columns[] = $column;
            }
        }
        
        if (!empty($missing_columns)) {
            $results['messages'][] = "Error: Columnas requeridas no encontradas en archivo de niveles: " . implode(', ', $missing_columns);
            $results['errors'] = 1;
            return [$levelids, $results];
        }
        
        // Procesar cada línea
        while ($line = $csv->next()) {
            $record = array_combine($columnnames, $line);
            
            // Solo procesar registros que son estructuras (niveles)
            if (!isset($record['Es estructura']) || $record['Es estructura'] != '1') {
                continue;
            }
            
            $parentid = trim($record['Número ID paterno']);
            $idnumber = trim($record['Número ID']);
            $shortname = trim($record['Nombre_corto']);
            $description = trim($record['Descripción']);
            
            // Solo procesar niveles que pertenecen al marco principal
            if ($parentid != 'RENEC-PRINCIPAL') {
                continue;
            }
            
            try {
                // Verificar si ya existe
                $existing = \core_competency\competency::get_records([
                    'competencyframeworkid' => $frameworkid,
                    'idnumber' => $idnumber
                ]);
                
                if (!empty($existing)) {
                    $levelid = reset($existing)->get('id');
                    $levelids[$idnumber] = $levelid;
                    $results['skipped']++;
                    $results['messages'][] = "Nivel $shortname ya existe, omitido.";
                    continue;
                }
                
                // Crear el nivel
                $level = new \core_competency\competency();
                $level->set('competencyframeworkid', $frameworkid);
                $level->set('shortname', $shortname);
                $level->set('description', $description);
                $level->set('descriptionformat', FORMAT_HTML);
                $level->set('idnumber', $idnumber);
                $level->set('parentid', 0); // Es un nivel principal dentro del marco
                $level->set('path', '/0/'); // Se actualizará automáticamente
                $level->set('sortorder', 0); // Se actualizará automáticamente
                $level->set('usermodified', $USER->id);
                $level->create();
                
                $levelid = $level->get('id');
                $levelids[$idnumber] = $levelid;
                $results['created']++;
                $results['messages'][] = "Nivel $shortname creado con ID: $levelid";
                
            } catch (Exception $e) {
                $results['errors']++;
                $results['messages'][] = "Error al crear nivel $shortname: " . $e->getMessage();
            }
        }
        
        $csv->close();
        csv_import_reader::cleanup_import_area('renec_levels');
        
    } catch (Exception $e) {
        $results['messages'][] = "Error al procesar archivo de niveles: " . $e->getMessage();
        $results['errors'] = 1;
    }
    
    return [$levelids, $results];
}

/**
 * Crea una competencia individual
 * 
 * @param int $frameworkid ID del marco
 * @param int $parentid ID del nivel padre
 * @param string $shortname Nombre corto
 * @param string $description Descripción
 * @param string $idnumber Identificador único
 * @param bool $overwrite Sobrescribir si ya existe
 * @return int ID de la competencia creada
 */
function local_renec_create_competency($frameworkid, $parentid, $shortname, $description, $idnumber, $overwrite = false) {
    global $USER;

    // Verificar si ya existe una competencia con este ID
    $existing = \core_competency\competency::get_records([
        'competencyframeworkid' => $frameworkid,
        'idnumber' => $idnumber
    ]);
    
    if (!empty($existing)) {
        $existingComp = reset($existing);
        
        // Si no se debe sobrescribir, devolver el ID existente
        if (!$overwrite) {
            return $existingComp->get('id');
        }
        
        // Actualizar la competencia existente
        try {
            $existingComp->set('shortname', $shortname);
            $existingComp->set('description', $description);
            $existingComp->set('parentid', $parentid);
            $existingComp->set('usermodified', $USER->id);
            $existingComp->update();
            return $existingComp->get('id');
        } catch (Exception $e) {
            error_log('Error al actualizar competencia: ' . $e->getMessage());
            throw new \moodle_exception('Error al actualizar competencia: ' . $e->getMessage());
        }
    }

    // Crear nueva competencia
    try {
        $competency = new \core_competency\competency();
        $competency->set('competencyframeworkid', $frameworkid);
        $competency->set('shortname', $shortname);
        $competency->set('description', $description);
        $competency->set('descriptionformat', FORMAT_HTML);
        $competency->set('idnumber', $idnumber);
        $competency->set('parentid', $parentid);
        $competency->set('path', '/0/'); // Se actualizará automáticamente
        $competency->set('sortorder', 0); // Se actualizará automáticamente
        $competency->set('usermodified', $USER->id);
        $competency->create();
        
        return $competency->get('id');
    } catch (Exception $e) {
        error_log('Error al crear competencia: ' . $e->getMessage());
        throw new \moodle_exception('Error al crear competencia: ' . $e->getMessage());
    }
}

/**
 * Importa competencias RENEC desde un archivo CSV
 * 
 * @param string $filepath Ruta al archivo CSV
 * @param int $frameworkid ID del marco de competencias
 * @param array $levelids Mapa de IDs de niveles [idnumber => id]
 * @param string $encoding Codificación del archivo
 * @param string $delimiter Delimitador del CSV
 * @param bool $overwrite Sobrescribir competencias existentes
 * @param bool $create_missing_levels Crear niveles faltantes
 * @return array Estadísticas y resultados de la importación
 */
function local_renec_import_competencies_csv($filepath, $frameworkid, $levelids = [], $encoding = 'UTF-8', 
                                         $delimiter = 'comma', $overwrite = false, $create_missing_levels = true) {
    global $DB;
    
    $results = [
        'competencies' => [
            'total' => 0,
            'by_level' => [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            'created' => 0,
            'skipped' => 0,
            'errors' => 0
        ],
        'messages' => []
    ];
    
    // Verificar que el archivo existe
    if (!file_exists($filepath)) {
        $results['messages'][] = "Error: El archivo no existe: " . $filepath;
        $results['error'] = true;
        return $results;
    }
    
    // Convertir tipo de delimitador a carácter
    $delimiterChar = ',';
    switch ($delimiter) {
        case 'semicolon':
            $delimiterChar = ';';
            break;
        case 'tab':
            $delimiterChar = "\t";
            break;
        case 'colon':
            $delimiterChar = ':';
            break;
    }
    
    // Procesar el archivo CSV
    try {
        // Leer el contenido del archivo
        $content = file_get_contents($filepath);
        if ($content === false) {
            $results['messages'][] = "Error: No se pudo leer el archivo CSV";
            $results['error'] = true;
            return $results;
        }
        
        // Convertir codificación si es necesario
        if ($encoding != 'UTF-8') {
            $content = iconv($encoding, 'UTF-8//IGNORE', $content);
        }
        
        // Procesar CSV
        $importid = csv_import_reader::get_new_iid('renec_comp');
        $csv = new csv_import_reader($importid, 'renec_comp');
        $csv->load_csv_content($content, $encoding, $delimiterChar);
        $csv->init();
        
        $columnnames = $csv->get_columns();
        
        // Verificar que el CSV tiene las columnas requeridas
        $required_columns = [
            'Número ID paterno',
            'Número ID',
            'Nombre_corto',
            'Descripción',
            'Es estructura'
        ];
        
        $missing_columns = [];
        foreach ($required_columns as $column) {
            if (!in_array($column, $columnnames)) {
                $missing_columns[] = $column;
            }
        }
        
        if (!empty($missing_columns)) {
            $results['messages'][] = "Error: Columnas requeridas no encontradas: " . implode(', ', $missing_columns);
            $results['error'] = true;
            return $results;
        }
        
        // Niveles encontrados en el CSV
        $found_levels = [];
        
        // Procesar cada línea
        while ($line = $csv->next()) {
            $record = array_combine($columnnames, $line);
            
            // No procesar registros que son estructuras (niveles)
            if (isset($record['Es estructura']) && $record['Es estructura'] == '1') {
                continue;
            }
            
            $results['competencies']['total']++;
            
            $parent_idnumber = trim($record['Número ID paterno']);
            $idnumber = trim($record['Número ID']);
            $shortname = trim($record['Nombre_corto']);
            $description = trim($record['Descripción']);
            
            // Normalizar textos
            $shortname = local_renec_normalize_text($shortname);
            $description = local_renec_normalize_text($description);
            
            // Determinar nivel desde el ID paterno (Ej: RENEC-NIVEL-1 → nivel 1)
            $nivel = 0;
            if (strpos($parent_idnumber, 'RENEC-NIVEL-') === 0) {
                $nivel_str = substr($parent_idnumber, 12); // Obtener número después de "RENEC-NIVEL-"
                if (is_numeric($nivel_str)) {
                    $nivel = intval($nivel_str);
                }
            }
            
            // Incrementar contador por nivel
            if (!isset($results['competencies']['by_level'][$nivel])) {
                $results['competencies']['by_level'][$nivel] = 0;
            }
            $results['competencies']['by_level'][$nivel]++;
            
            // Añadir a niveles encontrados
            if (!in_array($nivel, $found_levels)) {
                $found_levels[] = $nivel;
            }
            
            // Verificar si existe el nivel padre y obtener su ID
            if (!isset($levelids[$parent_idnumber])) {
                // Si no existe y está habilitada la creación automática, crear el nivel
                if ($create_missing_levels) {
                    try {
                        $level_name = "";
                        $level_desc = "";
                        
                        if (strpos($parent_idnumber, 'RENEC-NIVEL-') === 0) {
                            $nivel_num = substr($parent_idnumber, 12);
                            $level_name = "RENEC Nivel $nivel_num";
                            $level_desc = "Competencias de nivel " . 
                                         ($nivel_num == 1 ? "básico" : 
                                          ($nivel_num == 2 ? "intermedio" : 
                                           ($nivel_num == 3 ? "avanzado" : 
                                            ($nivel_num == 4 ? "superior" : "especializado"))));
                        } else if ($parent_idnumber === 'RENEC-SIN-NIVEL') {
                            $level_name = "RENEC Sin Nivel";
                            $level_desc = "Competencias sin nivel asignado";
                        } else {
                            // Otro tipo de nivel no reconocido
                            $results['messages'][] = "Error: Nivel no reconocido para la competencia $idnumber: $parent_idnumber";
                            $results['competencies']['errors']++;
                            continue;
                        }
                        
                        $levelid = local_renec_create_level($frameworkid, $level_name, $level_desc, $parent_idnumber);
                        $levelids[$parent_idnumber] = $levelid;
                        $results['messages'][] = "Nivel $level_name creado automáticamente con ID: $levelid";
                    } catch (Exception $e) {
                        $results['messages'][] = "Error al crear nivel para la competencia $idnumber: " . $e->getMessage();
                        $results['competencies']['errors']++;
                        continue;
                    }
                } else {
                    $results['messages'][] = "Error: Nivel padre no disponible para competencia $idnumber: $parent_idnumber";
                    $results['competencies']['errors']++;
                    continue;
                }
            }
            
            try {
                // Crear la competencia
                $competencyid = local_renec_create_competency(
                    $frameworkid, 
                    $levelids[$parent_idnumber], 
                    $shortname, 
                    $description, 
                    $idnumber,
                    $overwrite
                );
                
                if ($competencyid) {
                    $results['competencies']['created']++;
                } else {
                    $results['competencies']['skipped']++;
                }
                
            } catch (Exception $e) {
                $results['messages'][] = "Error al crear competencia $idnumber: " . $e->getMessage();
                $results['competencies']['errors']++;
            }
        }
        
        $csv->close();
        csv_import_reader::cleanup_import_area('renec_comp');
        
        $results['messages'][] = "Importación finalizada. Total: " . $results['competencies']['total'] . 
                                " | Creadas: " . $results['competencies']['created'] . 
                                " | Omitidas: " . $results['competencies']['skipped'] . 
                                " | Errores: " . $results['competencies']['errors'];
        
    } catch (Exception $e) {
        $results['messages'][] = "Error al procesar el archivo CSV: " . $e->getMessage();
        $results['error'] = true;
    }
    
    return $results;
}

/**
 * Normaliza texto eliminando caracteres problemáticos y acentos
 * 
 * @param string $text Texto a normalizar
 * @return string Texto normalizado
 */
function local_renec_normalize_text($text) {
    if (empty($text)) {
        return '';
    }
    
    // Primero intentamos convertir caracteres especiales a sus equivalentes
    $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Eliminar caracteres problemáticos
    $text = str_replace('"', "'", $text);
    
    return $text;
}

/**
 * Obtener la lista de escalas disponibles
 * 
 * @return array Lista de escalas [id => nombre]
 */
function local_renec_get_scales() {
    global $DB;
    
    $scales = $DB->get_records('scale', [], 'id', 'id, name');
    $result = [];
    
    foreach ($scales as $scale) {
        $result[$scale->id] = $scale->name;
    }
    
    return $result;
}

/**
 * Asocia una competencia a un curso
 * 
 * @param int $courseid ID del curso
 * @param int $competencyid ID de la competencia
 * @param int $ratingrequired Calificación mínima requerida (1-5)
 * @return bool Éxito de la operación
 */
function local_renec_associate_competency_to_course($courseid, $competencyid, $ratingrequired = 3) {
    global $USER;
    
    try {
        // Verificar si ya existe la asociación
        $existing = \core_competency\course_competency::get_records([
            'courseid' => $courseid,
            'competencyid' => $competencyid
        ]);
        
        if (!empty($existing)) {
            return true;
        }
        
        $coursecomp = new \core_competency\course_competency();
        $coursecomp->set('courseid', $courseid);
        $coursecomp->set('competencyid', $competencyid);
        $coursecomp->set('sortorder', 0); // Se actualizará automáticamente
        $coursecomp->set('ruleoutcome', \core_competency\course_competency::OUTCOME_RECOMMEND);
        $coursecomp->set('ruleconfig', '');
        $coursecomp->set('usermodified', $USER->id);
        $coursecomp->create();
        
        return true;
    } catch (Exception $e) {
        error_log('Error al asociar competencia ' . $competencyid . ' al curso ' . $courseid . ': ' . $e->getMessage());
        return false;
    }
}
