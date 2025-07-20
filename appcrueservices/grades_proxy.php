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
 * Proxy service for grades data with enhanced error handling.
 *
 * @package   local_appcrueservices
 * @author    Alberto Otero Mato
 * @copyright 2025 alberto.otero@altia.es
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

// No requiere login ya que usaremos un apikey interna.
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Function to standardize error responses
function send_error_response($exception, $debug = false) {
    $errorcode = method_exists($exception, 'errorcode') ? $exception->errorcode : 'internal_error';
    
    // Determine appropriate HTTP status code
    $httpcode = 500;
    $error_codes_map = [
        'invalidapikey' => 401,
        'missingwstoken' => 503,
        'usernotenrolled' => 403,
        'jsondecodeerror' => 400,
        'invalidparameter' => 400
    ];
    
    if (isset($error_codes_map[$errorcode])) {
        $httpcode = $error_codes_map[$errorcode];
    }

    http_response_code($httpcode);
    
    // Prepare response structure
    $response = [
        'success' => false,
        'error' => [
            'code' => $errorcode,
            'message' => $exception->getMessage(),
            'timestamp' => time()
        ]
    ];

    // Add debug info if enabled
    if ($debug) {
        $response['error']['debug'] = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
    }

    echo json_encode($response);
    exit;
}

try {
    // Validate and get parameters
    $required_params = [
        'studentemail' => ['type' => PARAM_EMAIL, 'required' => true],
        'apikey' => ['type' => PARAM_RAW, 'required' => true]
    ];

    $params = [];
    
    // Process required parameters
    foreach ($required_params as $name => $config) {
        try {
            $params[$name] = required_param($name, $config['type']);
        } catch (moodle_exception $e) {
            throw new moodle_exception('missingparam', 'local_appcrueservices', '', $name);
        }
    }

    // Validate API Key with logging
    $stored_apikey = get_config('local_appcrueservices', 'apikey');
    if (empty($stored_apikey) || $params['apikey'] !== $stored_apikey) {
        error_log("Invalid API Key attempt: " . substr($params['apikey'], 0, 3) . '...');
        throw new moodle_exception('invalidapikey', 'local_appcrueservices');
    }

    // Get web service token
    $wstoken = get_config('local_appcrueservices', 'wstoken');
    if (empty($wstoken)) {
        throw new moodle_exception('missingwstoken', 'local_appcrueservices');
    }

    // Prepare request to internal web service
    $functionname = 'local_appcrueservices_external_grades_get_grades';
    $serverurl = $CFG->wwwroot . '/webservice/rest/server.php';

    $request_params = [
        'studentemail' => $params['studentemail'],
        'apikey' => $params['apikey'],
        'moodlewsrestformat' => 'json',
        'wsfunction' => $functionname,
        'wstoken' => $wstoken
    ];

    // Make the request with timeout and logging
    $curl = new curl();
    $curl->setopt([
        'CURLOPT_TIMEOUT' => 30,
        'CURLOPT_CONNECTTIMEOUT' => 10
    ]);
    
    $response = $curl->post($serverurl, $request_params);
    $info = $curl->get_info();
    
    // Log slow responses
    if ($info['total_time'] > 2) {
        error_log("Slow grades response: {$info['total_time']}s for {$params['studentemail']}");
    }

    // Validate and decode response
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg() . " | Response: " . substr($response, 0, 200));
        throw new moodle_exception('jsondecodeerror', 'local_appcrueservices', '', null, json_last_error_msg());
    }

    // Check for error in the web service response
    if (isset($decoded['exception'])) {
        error_log("Internal WS exception: " . $decoded['message']);
        throw new moodle_exception($decoded['errorcode'] ?? 'wserror', 'local_appcrueservices', '', null, $decoded['message']);
    }

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $decoded,
        'timestamp' => time()
    ]);

} catch (moodle_exception $e) {
    send_error_response($e, debugging());
} catch (Exception $e) {
    // Catch any other exceptions
    $moodle_ex = new moodle_exception('internalerror', 'local_appcrueservices', '', null, $e->getMessage());
    send_error_response($moodle_ex, debugging());
}
