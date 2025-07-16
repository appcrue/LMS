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
 * Returns a WS token for a given user, always regenerating a new one.
 *
 * @package   local_appcrueservices
 * @author    Alberto Otero Mato
 * @copyright 2025 alberto.otero@altia.es
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

class local_appcrueservices_calendar_external extends external_api {

    public static function get_user_calendar_parameters() {
        return new external_function_parameters([
            'studentemail' => new external_value(PARAM_EMAIL, 'Email del estudiante'),
            'apikey' => new external_value(PARAM_RAW, 'API Key de autenticación'),
            'timestart' => new external_value(PARAM_INT, 'Timestamp de inicio', VALUE_DEFAULT, 0),
            'timeend' => new external_value(PARAM_INT, 'Timestamp de fin', VALUE_DEFAULT, 0)
        ]);
    }

    public static function get_user_calendar($studentemail, $apikey, $timestart = 0, $timeend = 0) {
        global $DB, $CFG, $USER;

        self::validate_parameters(self::get_user_calendar_parameters(), [
            'studentemail' => $studentemail,
            'apikey' => $apikey,
            'timestart' => $timestart,
            'timeend' => $timeend
        ]);

        $studentemail = trim(core_text::strtolower($studentemail));
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

        // Impersonar al estudiante.
        $originaluser = $USER;
        \core\session\manager::set_user($user);
        $events = [];

        try {
            $now = time();
            $from = $timestart > 0 ? $timestart : $now;
            $to = $timeend > 0 ? $timeend : ($now + (30 * DAYSECS));

            // Verificar que la función existe
            if (!function_exists('calendar_get_events')) {
                throw new moodle_exception('functionnotavailable', 'local_appcrueservices', '', null, 'calendar_get_events() no existe');
            }

            // Obtener eventos visibles para el usuario
            $eventlist = calendar_get_events($from, $to, [$user->id], false, true);

            foreach ($eventlist as $event) {
                $course = ($event->courseid) ? get_course($event->courseid) : null;

                // Asegura que el evento tenga URL
                $eventurl = method_exists($event, 'get_url') ? $event->get_url()->out(false) : '';

                // Obtener el autor si existe
                $nameauthor = '';
                if (!empty($event->userid)) {
                    $creator = $DB->get_record('user', ['id' => $event->userid], 'firstname, lastname', IGNORE_MISSING);
                    if ($creator) {
                        $nameauthor = $creator->firstname . ' ' . $creator->lastname;
                    } else {
                        $nameauthor = '';
                    }
                }

                $events[] = [
                    'name'          => $event->name,
                    'type'          => $event->eventtype ?? '',
                    'modulename'    => $event->modulename ?? '',
                    'timestart'     => $event->timestart,
                    'timesort'      => $event->timestart + ($event->timeduration ?? 0),
                    'description'   => html_entity_decode(strip_tags($event->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'fullname'      => $course ? $course->fullname : '',
                    'location'      => $event->location ?? '',
                    'url'           => $eventurl,
                    'nameauthor'    => $nameauthor
                ];
            }

        } finally {
            \core\session\manager::set_user($originaluser);
        }

        return [
            'events' => $events
        ];
    }

    public static function get_user_calendar_returns() {
        return new external_single_structure([
            'events' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Nombre del evento'),
                    'type' => new external_value(PARAM_TEXT, 'Tipo de evento'),
                    'modulename' => new external_value(PARAM_TEXT, 'Tipo de módulo', VALUE_OPTIONAL),
                    'timestart' => new external_value(PARAM_INT, 'Inicio (timestamp)'),
                    'timesort' => new external_value(PARAM_INT, 'Duration (timestamp)'),
                    'description' => new external_value(PARAM_RAW, 'Descripción del evento'),
                    'fullname' => new external_value(PARAM_TEXT, 'Course name'),
                    'location' => new external_value(PARAM_TEXT, 'Ubicación', VALUE_OPTIONAL),
                    'url' => new external_value(PARAM_TEXT, 'URL del evento', VALUE_OPTIONAL),
                    'nameauthor' => new external_value(PARAM_TEXT, 'Nombre del autor del evento', VALUE_OPTIONAL)
                ])
            )
        ]);
    }
}
