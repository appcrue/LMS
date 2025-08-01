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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

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
  * Services.
  *
  * @package    local_appcrue
  * @copyright  2021 Juan Pblo de Castro
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
 defined('MOODLE_INTERNAL') || die();

$services = [
    // The name of the web service.
    'local_appcrue_external' => [
        'functions' => [
                            'local_appcrue_send_instant_messages',
                            'local_appcrue_send_instant_message',
                            'local_appcrue_notify_grade',
                        ], // Web service functions of this service.
        'requiredcapability' => '', // If set, the web service user need this capability to access.
        // Any function of this service. For example: 'some/capability:specified'.
        'restrictedusers' => 1, // If enabled, the Moodle administrator must link some user to this service
        // ... into the administration https://server/admin/webservice/service_users.php?id=7.
        'enabled' => 0, // If enabled, the service can be reachable on a default installation.
        'shortname' => 'external_notifications', // Optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 0, // Allow file downloads.
        'uploadfiles' => 0, // Allow file uploads.
    ],

];
$functions = [
    'local_appcrue_send_instant_messages' => [         // Web service function name
        // Class containing the external function OR namespaced class in classes/external/XXXX.php.
        'classname'   => 'local_appcrue_external',
        // External function name.
        'methodname'  => 'send_instant_messages',
        // File containing the class/external function - not required if using namespaced auto-loading classes
        // ...defaults to the service's externalib.php.
        'classpath'   => 'local/appcrue/externallib.php',
        // Human readable description of the web service function.
        'description' => 'Sends a list of instant messages to users identified by any user field.',
        // Database rights of the web service function (read, write).
        'type'        => 'write',
        // Is the service available to 'internal' ajax calls.
        'ajax' => true,
        // Comma separated list of capabilities used by the function.
        'capabilities' => 'moodle/site:sendmessage',
    ],
    // Web service function name.
    'local_appcrue_send_instant_message' => [
        // Class containing the external function OR namespaced class in classes/external/XXXX.php.
        'classname'   => 'local_appcrue_external',
        // External function name.
        'methodname'  => 'send_instant_message',
        // File containing the class/external function - not required if using namespaced auto-loading classes.
        // ...defaults to the service's externalib.php.
        'classpath'   => 'local/appcrue/externallib.php',
        // Human readable description of the web service function.
        'description' => 'Sends instant message to one user identified by any user field.',
        // Database rights of the web service function (read, write).
        'type'        => 'write',
        // Is the service available to 'internal' ajax calls.
        'ajax' => true,
        // Comma separated list of capabilities used by the function.
        'capabilities' => 'moodle/site:sendmessage',
    ],
    // Web service function name.
    'local_appcrue_notify_grade' => [
        // Class containing the external function OR namespaced class in classes/external/XXXX.php.
        'classname'   => 'local_appcrue_external',
        // External function name.
        'methodname'  => 'notify_grade',
        // File containing the class/external function - not required if using namespaced auto-loading classes.
        // ...defaults to the service's externalib.php.
        'classpath'   => 'local/appcrue/externallib.php',
        // Human readable description of the web service function.
        'description' => 'Notify a oficial grade with revision info and sends instant message to one user identified by any user field.',
        // Database rights of the web service function (read, write).
        'type'        => 'write',
        // Is the service available to 'internal' ajax calls.
        'ajax' => true,
        // Comma separated list of capabilities used by the function.
        'capabilities' => 'moodle/site:sendmessage',
    ],
];
