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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

// No requiere login ya que usaremos un apikey interna.
header('Content-Type: application/json');

try {
    $studentemail = required_param('studentemail', PARAM_EMAIL);
    $apikey       = required_param('apikey', PARAM_RAW);
    $timestart    = optional_param('timestart', 0, PARAM_INT);
    $timeend      = optional_param('timeend', 0, PARAM_INT);

    // Obtener y validar API Key
    $stored_apikey = get_config('local_appcrueservices', 'apikey');
    if (empty($stored_apikey) || $apikey !== $stored_apikey) {
        throw new moodle_exception('invalidapikey', 'local_appcrueservices');
    }

    // Obtener el token desde la configuración del plugin.
    $wstoken = get_config('local_appcrueservices', 'wstoken');
    if (empty($wstoken)) {
        throw new moodle_exception('missingwstoken', 'local_appcrueservices');
    }

    // Construir la URL del web service.
    $functionname = 'local_appcrueservices_external_calendar_get_calendar';
    $serverurl = $CFG->wwwroot . '/webservice/rest/server.php';

    $params = [
        'studentemail' => $studentemail,
        'apikey' => $apikey,
        'timestart' => $timestart,
        'timeend' => $timeend,
        'moodlewsrestformat' => 'json',
        'wsfunction' => $functionname,
        'wstoken' => $wstoken
    ];

    // Llamar al servicio remoto usando curl.
    $curl = new curl();
    $response = $curl->get($serverurl, $params);

    // Intenta decodificar y validar la respuesta
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new moodle_exception('jsondecodeerror', 'local_appcrueservices', '', null, json_last_error_msg());
    }

    echo json_encode($decoded);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
