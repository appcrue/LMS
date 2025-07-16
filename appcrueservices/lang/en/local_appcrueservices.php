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

$string['pluginname'] = 'Services AppCrue';
$string['servicename'] = 'Services AppCrue';

// Config settings.
$string['privacy:metadata'] = 'The AppCrue Services plugin does not store any personal data.';
$string['local_appcrueservices:use'] = 'Allow the use of the AppCrue web service';
$string['desc'] = 'This plugin provides a secure API-based interface to retrieve student academic data, such as final grades and calendar events, using their email address. It is designed for external integrations that require controlled access to user information without direct authentication.';
$string['wstoken'] = 'Web Service Token';
$string['wstoken_desc'] = 'Access token for the web service that will be used internally by this plugin.';
$string['apikey'] = 'API Key';
$string['apikey_desc'] = 'Secret key for authentication with external services.';

// Error strings.
$string['invalidapikey'] = 'Invalid API Key';
$string['missingwstoken'] = 'The web service token has not been configured.';
$string['jsondecodeerror'] = 'Error interpreting JSON response from server: {$a}.';

// Service function descriptions.
$string['service:get_user_grades'] = 'Retrieve final course grades for a student by email.';
$string['service:get_user_forums'] = 'Retrieve visible forums, discussions, and replies for an authenticated student.';
$string['service:get_user_calendar'] = 'Retrieve calendar events for a student identified by email.';
