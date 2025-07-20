<?php
namespace local_appcrueservices\external;

use core_text;

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
 * Returns a WS token for a given user, always regenerating a new one.
 *
 * @package   local_appcrueservices
 * @author    Alberto Otero Mato
 * @copyright 2025 alberto.otero@altia.es
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/grade/lib.php');

class grades extends \external_api {

    public static function get_grades_parameters() {
        return new \external_function_parameters([
            'studentemail' => new \external_value(PARAM_EMAIL, 'Email del estudiante'),
            'apikey' => new \external_value(PARAM_RAW, 'API Key de autenticación')
        ]);
    }

    public static function get_grades($studentemail, $apikey) {
        global $DB, $CFG, $USER;

        self::validate_parameters(self::get_grades_parameters(), [
            'studentemail' => $studentemail,
            'apikey' => $apikey
        ]);

        // Normalizar email.
        $studentemail = trim(core_text::strtolower($studentemail));

        // Validar API Key.
        $stored_apikey = get_config('local_appcrueservices', 'apikey');
        if (empty($stored_apikey) || $apikey !== $stored_apikey) {
            throw new moodle_exception('invalidapikey', 'local_appcrueservices');
        }

        // Buscar usuario.
        $user = $DB->get_record('user', [
            'email' => $studentemail,
            'deleted' => 0,
            'suspended' => 0,
            'mnethostid' => $CFG->mnet_localhost_id
        ], '*', MUST_EXIST);

        // Validar que el usuario está enrolado en algún curso
        if (!enrol_get_users_courses($user->id, true)) {
            throw new \moodle_exception('usernotenrolled', 'local_appcrueservices');
        }

        // Guardar usuario original.
        $originaluser = $USER;

        try {
            // Impersonar al estudiante.
            \core\session\manager::set_user($user);

            $courses = enrol_get_users_courses($user->id, true);
            $grades = [];

            foreach ($courses as $course) {
                $items = \grade_item::fetch_all(['courseid' => $course->id]);

                if (!$items) {
                    continue;
                }

                foreach ($items as $item) {
                    // Solo incluir si el ítem está visible para el estudiante.
                    if ($item->is_hidden()) {
                        continue;
                    }

                    // Obtener nota final para este usuario.
                    $grade = new \grade_grade([
                        'itemid' => $item->id,
                        'userid' => $user->id
                    ]);
                    
                    if (is_null($grade->finalgrade)) {
                        continue; // No hay calificación aún.
                    }
                    
                    $grades[] = [
                        'courseid' => $course->id,
                        'coursename' => $course->fullname,
                        'itemname' => html_entity_decode(strip_tags($item->get_name()), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'itemtype' => $item->itemtype,
                        'graderaw' => $grade->rawgrade,
                        'finalgrade' => $grade->finalgrade,
                        'gradeformatted' => html_entity_decode(strip_tags(\grade_format_gradevalue($grade->finalgrade, $item)), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'gradeisoverridden' => $grade->overridden ? 'TRUE' : 'FALSE',
                        'gradedategraded' => $grade->timemodified ?? 0,
                        'feedback' => html_entity_decode(strip_tags($grade->feedback ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'userid' => $user->id
                    ];
                }
            }

        } finally {
            // Restaurar el usuario original.
            \core\session\manager::set_user($originaluser);
        }

        return [
            'grades' => $grades
        ];
    }
    
    public static function get_grades_returns() {
        return new \external_single_structure([
            'grades' => new \external_multiple_structure(
                new \external_single_structure([
                    'courseid' => new \external_value(PARAM_INT, 'ID del curso'),
                    'coursename' => new \external_value(PARAM_TEXT, 'Nombre del curso'),
                    'itemname' => new \external_value(PARAM_TEXT, 'Nombre del ítem'),
                    'itemtype' => new \external_value(PARAM_TEXT, 'Tipo de ítem'),
                    'graderaw' => new \external_value(PARAM_FLOAT, 'Nota sin procesar', VALUE_OPTIONAL),
                    'finalgrade' => new \external_value(PARAM_FLOAT, 'Nota final'),
                    'gradeformatted' => new \external_value(PARAM_TEXT, 'Nota formateada'),
                    'gradeisoverridden' => new \external_value(PARAM_TEXT, 'Sobrescrita', VALUE_OPTIONAL),
                    'gradedategraded' => new \external_value(PARAM_INT, 'Fecha de calificación', VALUE_OPTIONAL),
                    'feedback' => new \external_value(PARAM_TEXT, 'Comentarios', VALUE_OPTIONAL),
                    'userid' => new \external_value(PARAM_INT, 'ID del estudiante')
                ])
            )
        ]);
    }
}
