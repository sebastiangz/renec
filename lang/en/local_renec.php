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

$string['pluginname'] = 'Importador de competencias RENEC/CONOCER';
$string['plugininfo'] = 'Este plugin permite importar competencias del Registro Nacional de Estándares de Competencia (RENEC) a Moodle. Sigue el proceso paso a paso: primero crea un marco de competencias, luego importa los niveles y finalmente las competencias individuales.';

// Permisos
$string['renec:manage'] = 'Administrar competencias RENEC';

// Menú principal
$string['menu_options'] = 'Opciones disponibles';
$string['create_framework'] = 'Paso 1: Crear marco de competencias';
$string['create_framework_desc'] = 'Crea un nuevo marco de competencias RENEC para organizar tus estándares.';
$string['import_levels'] = 'Paso 2: Importar niveles';
$string['import_levels_desc'] = 'Importa los niveles de competencia (del 1 al 5) a un marco existente.';
$string['import_competencies'] = 'Paso 3: Importar competencias';
$string['import_competencies_desc'] = 'Importa las competencias individuales organizadas por niveles.';
$string['view_frameworks'] = 'Ver marcos de competencias';
$string['view_frameworks_desc'] = 'Visualiza los marcos de competencias existentes y su estructura.';

// Flujo recomendado
$string['recommended_workflow'] = 'Flujo de trabajo recomendado';
$string['recommended_workflow_desc'] = 'Para una correcta importación, se recomienda seguir estos pasos en orden:';
$string['workflow_step1'] = 'Crear un nuevo marco de competencias RENEC';
$string['workflow_step2'] = 'Importar los niveles desde archivo o crear los niveles predeterminados';
$string['workflow_step3'] = 'Importar las competencias individuales desde el archivo CSV de RENEC';

// Formato de archivos CSV
$string['csv_format'] = 'Formato de archivos CSV';
$string['csv_format_desc'] = 'Los archivos CSV deben tener el siguiente formato para ser importados correctamente:';
$string['csv_purpose'] = 'Propósito';
$string['csv_columns'] = 'Columnas requeridas';
$string['csv_example'] = 'Ejemplo';
$string['csv_framework'] = 'Marco de competencias';
$string['csv_levels'] = 'Niveles';
$string['csv_competencies'] = 'Competencias';

// Formulario de creación de marco
$string['create_framework_info'] = 'Crea un nuevo marco de competencias para organizar los estándares RENEC. Este es el primer paso del proceso de importación.';
$string['create_framework_instructions'] = 'Complete los siguientes campos para crear un nuevo marco de competencias RENEC. Asegúrese de seleccionar una escala adecuada.';
$string['framework_name'] = 'Nombre del marco';
$string['framework_name_help'] = 'Nombre corto que identifica el marco de competencias. Recomendamos usar "RENEC" o similar.';
$string['framework_description'] = 'Descripción del marco';
$string['framework_description_help'] = 'Descripción detallada del propósito del marco de competencias.';
$string['framework_idnumber'] = 'ID del marco';
$string['framework_idnumber_help'] = 'Identificador único para el marco. Recomendamos usar "RENEC-PRINCIPAL" o similar.';
$string['selectscale'] = 'Escala de competencias';
$string['selectscale_help'] = 'Seleccione la escala que se utilizará para evaluar las competencias. Se recomienda usar una escala de 5 niveles.';
$string['create_framework_submit'] = 'Crear marco';
$string['framework_created_success'] = 'Marco de competencias creado exitosamente con ID: {$a}';
$string['continue_to_import_levels'] = 'Continuar con la importación de niveles';
$string['back_to_menu'] = 'Volver al menú principal';

// Formulario de importación de niveles
$string['import_levels_info'] = 'Importa los niveles de competencia a un marco existente. Este es el segundo paso del proceso de importación.';
$string['import_levels_instructions'] = 'Seleccione un marco de competencias existente y cargue un archivo CSV con los niveles de competencia o cree los niveles predeterminados.';
$string['select_framework'] = 'Marco de competencias';
$string['select_framework_help'] = 'Seleccione el marco de competencias donde desea importar los niveles o competencias.';
$string['create_default_levels'] = 'Crear niveles predeterminados';
$string['create_default_levels_help'] = 'Si marca esta opción, se crearán automáticamente los niveles del 1 al 5 y el nivel "Sin asignar", sin necesidad de cargar un archivo CSV.';
$string['levelsfile'] = 'Archivo CSV de niveles';
$string['levelsfile_help'] = 'Seleccione el archivo CSV con los niveles de competencia. Este archivo debe incluir columnas como "Número ID paterno", "Número ID", "Nombre_corto", "Descripción" y "Es estructura".';
$string['import_levels_submit'] = 'Importar niveles';
$string['levels_imported_success'] = '{$a} niveles importados exitosamente';
$string['continue_to_import_competencies'] = 'Continuar con la importación de competencias';

// Formulario de importación de competencias
$string['import_competencies_info'] = 'Importa las competencias individuales a un marco existente. Este es el tercer y último paso del proceso de importación.';
$string['import_competencies_instructions'] = 'Seleccione un marco de competencias existente y cargue un archivo CSV con las competencias RENEC.';
$string['competenciesfile'] = 'Archivo CSV de competencias';
$string['competenciesfile_help'] = 'Seleccione el archivo CSV con las competencias RENEC. Este archivo debe incluir columnas como "Número ID paterno", "Número ID", "Nombre_corto", "Descripción" y "Es estructura".';
$string['create_missing_levels'] = 'Crear niveles faltantes';
$string['create_missing_levels_help'] = 'Si marca esta opción, se crearán automáticamente los niveles que no existan en el marco pero que sean requeridos por las competencias en el archivo CSV.';
$string['import_competencies_submit'] = 'Importar competencias';

// Opciones avanzadas y campos comunes
$string['advancedoptions'] = 'Opciones avanzadas';
$string['overwrite'] = 'Sobrescribir competencias existentes';
$string['overwrite_help'] = 'Si se marca, las competencias existentes con el mismo ID serán actualizadas. Si no se marca, se omitirán.';
$string['encoding'] = 'Codificación del archivo';
$string['encoding_help'] = 'Seleccione la codificación del archivo CSV. Si ve caracteres extraños, pruebe con diferentes codificaciones.';
$string['delimiter'] = 'Delimitador CSV';
$string['delimiter_help'] = 'Seleccione el carácter que separa las columnas en el archivo CSV.';
$string['comma'] = 'Coma (,)';
$string['semicolon'] = 'Punto y coma (;)';
$string['tab'] = 'Tabulador';
$string['colon'] = 'Dos puntos (:)';

// Resultados de importación
$string['importresults'] = 'Resultados de la importación';
$string['levelscreated'] = '{$a} niveles creados';
$string['competenciestotal'] = 'Total de competencias en el archivo: {$a}';
$string['competenciescreated'] = 'Competencias creadas: {$a}';
$string['competenciesskipped'] = 'Competencias omitidas (ya existentes): {$a}';
$string['competencieserrors'] = 'Errores al crear competencias: {$a}';
$string['competenciesbylevel'] = 'Competencias por nivel';
$string['levelnumber'] = 'Nivel {$a}';
$string['levelunassigned'] = 'Sin nivel asignado';
$string['detailedlog'] = 'Registro detallado';
$string['viewframeworks'] = 'Ver marcos de competencias';

// Errores
$string['invalidfileextension'] = 'El archivo debe tener extensión .csv';
$string['missingrequiredcolumns'] = 'Faltan columnas requeridas en el archivo CSV';
$string['errorreadingfile'] = 'Error al leer el archivo CSV';
$string['error_framework_not_exists'] = 'El marco de competencias seleccionado no existe';
$string['error_idnumber_exists'] = 'Ya existe un marco de competencias con este ID';
$string['error_scale_not_exists'] = 'La escala seleccionada no existe';
