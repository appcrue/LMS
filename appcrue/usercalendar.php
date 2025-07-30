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
 * Send the calendar events to the app.
 *
 * @package    local_appcrue
 * @copyright  2021 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once('locallib.php');

if (!get_config('local_appcrue', 'enable_usercalendar')) {
    @header('HTTP/1.1 404 Not Found');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}

// No requiere login ya que usaremos un apikey interna.
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
try {
    $fromdate = optional_param('fromDate', '', PARAM_ALPHANUM);
    $todate = optional_param('toDate', '', PARAM_ALPHANUM);
    $category = optional_param('category', '', PARAM_ALPHA);
    $lang = required_param('lang', PARAM_ALPHA);
    // Get the token to use in the urls.
    [$user, $diag, $token] = appcrue_get_user_from_request();
    appcrue_config_user($user, true, $lang);

    $outputmessage = new stdClass();
    $outputmessage->calendar = [];
    $PAGE->set_context(null);

    if ($user != null) {
        // Get timestamps.
        // By default events in the last 5 or next 60 days.
        if ($fromdate != '') {
            $date = DateTime::createFromFormat('Ymd', $fromdate);
            $date->setTime(0, 0, 0);
            $timestart = $date->getTimeStamp();
        } else {
            // Last 5 days.
            $timestart = time() - 432000;
        }
        if ($todate != '') {
            $date = DateTime::createFromFormat('Ymd', $todate);
            $date->setTime(0, 0, 0);
            $timeend = $date->add(new DateInterval("P1D"))->getTimestamp();
        } else {
            // Next 60 days.
            $timeend = time() + 5184000;
        }
        $limitnum = 0;

        $events = local_appcrue\calendar_service::get_events($user, $timestart, $timeend, $limitnum);
        $outputmessage = local_appcrue\calendar_service::format_events_for_usercalendar($events, $user, $category, $token);
    }

    if (debugging()) {
        $outputmessage->debug = new stdClass();
        $outputmessage->debug->user = $user ? $user->idnumber : null;
        $outputmessage->debug->token = $token;
        $outputmessage->debug->diag = $diag;
    }
    if ($diag->code == 401) {
        header('HTTP/1.0 401 Unauthorized');
    } else if ($diag->code == 404) {
        header('HTTP/1.0 404 not found');
    }
    echo json_encode($outputmessage, JSON_HEX_QUOT | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    appcrue_send_error_response($e, debugging());
}
