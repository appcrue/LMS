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

$functions = [
    'local_appcrueservices_external_grades_get_grades' => [
        'classname'   => 'local_appcrueservices\\external\\grades',
        'methodname'  => 'get_grades',
        'description' => 'Devuelve las calificaciones finales de un estudiante autenticado por email, usando impersonación segura.',
        'type'        => 'read',
        'capabilities'=> 'local/appcrueservices:use',
        'ajax'        => false
    ],
    'local_appcrueservices_external_calendar_get_calendar' => [
        'classname'   => 'local_appcrueservices\\external\\calendar',
        'methodname'  => 'get_calendar',
        'description' => 'Devuelve los eventos del calendario del estudiante.',
        'type'        => 'read',
        'capabilities'=> 'local/appcrueservices:use',
        'ajax'        => false
    ],
    'local_appcrueservices_external_forums_get_forums' => [
        'classname'   => 'local_appcrueservices\\external\\forums',
        'methodname'  => 'get_forums',
        'description' => 'Obtiene los foros visibles para un estudiante',
        'type'        => 'read',
        'capabilities'=> 'local/appcrueservices:use',
        'ajax'        => true,
    ],
];

$services = [
    'Servicios AppCrue' => [
        'functions' => [
            'local_appcrueservices_external_grades_get_grades',
            'local_appcrueservices_external_calendar_get_calendar',
            'local_appcrueservices_external_forums_get_forums'
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'appcrueservices',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ]
];
