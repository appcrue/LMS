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

// No requiere login ya que usamos API key interna
header('Content-Type: application/json');

try {
    $studentemail = required_param('studentemail', PARAM_EMAIL);
    $apikey = required_param('apikey', PARAM_RAW);

    // Validar API Key
    $storedapikey = get_config('local_appcrueservices', 'apikey');
    if ($storedapikey !== $apikey) {
        throw new moodle_exception('invalidapikey', 'local_appcrueservices');
    }

    // Obtener el token del plugin
    $wstoken = get_config('local_appcrueservices', 'wstoken');
    if (empty($wstoken)) {
        throw new moodle_exception('missingwstoken', 'local_appcrueservices');
    }

    $functionname = 'local_appcrueservices_get_user_forums';
    $serverurl = $CFG->wwwroot . '/webservice/rest/server.php';

    $params = [
        'studentemail' => $studentemail,
        'apikey' => $apikey,
        'moodlewsrestformat' => 'json',
        'wsfunction' => $functionname,
        'wstoken' => $wstoken
    ];

    $curl = new curl();
    $response = $curl->post($serverurl, $params);

    // Decodificar respuesta JSON
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
