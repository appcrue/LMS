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
$string['privacy:metadata'] = 'El plugin Servicios AppCrue no almacena ningún dato personal.';
$string['appcrueservices:use'] = 'Permitir el uso del servicio web AppCrue';
$string['desc'] = 'Este plugin proporciona una interfaz segura basada en API para consultar datos académicos de estudiantes, como calificaciones finales y eventos de calendario, mediante su dirección de correo electrónico. Está diseñado para integraciones externas que requieren acceso controlado a información del usuario sin necesidad de autenticación directa.';
$string['wstoken'] = 'Web Service Token';
$string['wstoken_desc'] = 'Token de acceso al servicio web que se usará internamente por este plugin.';
$string['apikey'] = 'API Key';
$string['apikey_desc'] = 'Clave secreta utilizada para autenticar servicios externos.';

// Error strings.
$string['invalidapikey'] = 'API Key no válida';
$string['missingwstoken'] = 'No se ha configurado el token del servicio web.';
$string['jsondecodeerror'] = 'Error al interpretar la respuesta JSON del servidor: {$a}.';
$string['usernotenrolled'] = 'El usuario no está matriculado en ningún curso.';
$string['missingparam'] = 'Falta el parámetro requerido "{$a}"';
$string['invalidtimerange'] = 'Rango de tiempo no válido: la fecha de inicio debe ser anterior a la de fin';
$string['internalerror'] = 'Error interno del servidor';
$string['wserror'] = 'Error en el servicio web';
$string['invalidparameter'] = 'Valor de parámetro no válido';
$string['selfimpersonation'] = 'No se puede suplantar a uno mismo';

// Descripciones de funciones del servicio.
$string['service:get_grades'] = 'Obtener calificaciones del estudiante mediante suplantación segura.';
$string['service:get_forums'] = 'Obtener los foros visibles, discusiones y respuestas para un estudiante autenticado.';
$string['service:get_calendar'] = 'Recupera los eventos del calendario de un estudiante identificado por su email.';
$string['success:request_processed'] = 'Solicitud procesada con éxito';
