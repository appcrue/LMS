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

// phpcs:disable moodle.Files.RequireLogin.Missing

/**
 * TODO describe file appcrue
 *
 * @package    local_appcrue
 * @copyright  2025 Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_appcrue\appcrue_service;

require_once('../../config.php');
require_once('locallib.php');

if (!get_config('local_appcrue', 'lmsappcrue_enable_grades')) {
    @header('HTTP/1.1 404 Not Found');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}

// No requiere login ya que usaremos un apikey interna.
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
// Disable context in formatting.
$PAGE->set_context(null);

try {
    $endpoint = appcrue_service::instance_from_request();
    [$data, $count] = $endpoint->get_data_response();
    // Envelope the items in a response object.
    $response = [
        'success' => true,
        'count' => $count,
        'timestamp' => time(),
        'data' => $data,
    ];
    // Send the response as JSON.
    echo json_encode($response, JSON_HEX_QUOT | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    appcrue_service::send_error_response($e, debugging());
}
