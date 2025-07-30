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
 * Avatar end-point, a user picture related to a valid token.
 *
 * @package    local_appcrue
 * @copyright  2021 Juan Pblo de Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing

require_once(__DIR__ . '/../../config.php');
require_once('locallib.php');

$context = context_system::instance();
$PAGE->set_context($context);

if (!get_config('local_appcrue', 'enable_avatar')) {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}
try {
    $mode = optional_param('mode', 'base64', PARAM_ALPHA);
    // Check token and get user record.
    [$user, $resultstatus] = appcrue_get_user_from_request();
    if ($resultstatus->code == 401) {
        header('HTTP/1.0 401 unauthorized');
    } else if ($resultstatus->code == 404) {
        header('HTTP/1.0 404 not found');
    } else {
        $userpicture = new user_picture($user);
        $userpicture->size = 1;
        // TODO: Get the file directly without making an HTTP request.
        $url = $userpicture->get_url($PAGE);
        $curl = new curl();
        $result = $curl->get($url);
        if ($mode == 'raw') {
            header('Content-Type: image/jpeg');
            echo $result;
        } else {
            header('Content-Type: application/base64');
            echo base64_encode($result);
        }
    }
} catch (moodle_exception $e) {
    header('HTTP/1.0 400 Bad Request');
    die();
}
