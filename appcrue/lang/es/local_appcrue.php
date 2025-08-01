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
 * AppCrue message plugin version information.
 *
 * @package local_appcrue
 * @category admin
 * @author  Juan Pablo de Castro, Alberto Otero Mato
 * @copyright 2021 onwards juanpablo.decastro@uva.es, alberto.otero@altia.es
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['allow_continue'] = 'Permitir fallback continue';
$string['allow_continue_help'] = 'Permitir fallback=continue que si el token es invalido, no genera error, loguea como invitado y redirige a la URL de continuación.';
$string['autologinheader'] = 'Servicio Auto-login';
$string['autologinheader_help'] = 'Permite a los usuarios saltar a Moodle desde el AppCrue. También implementa redirecciones predefinidas.';
$string['avatarheader'] = 'Servicio Avatar';
$string['avatarheader_help'] = 'Permite a cualquier aplicación con un token válido obtener la imagen de ese usuario.';
$string['cache_sitemap'] = 'Cache para sitemaps';
$string['cache_sitemap_help'] = 'Utilizar el sistema de caché de Moodle con SiteMaps.';
$string['cache_sitemap_ttl'] = 'TTL para la cache sitemaps';
$string['cache_sitemap_ttl_help'] = 'Tiempo de vida de los SiteMaps en caché.';
$string['calendar:enable_calendar'] = 'Activar el servicio de calendario único';
$string['calendar:enable_calendar_help'] = 'El servicio de calendario único es el responsable de poblar el calendario de la vista principal de AppCRUE. ' .
    ' Usa el token personal y genera una representación JSON de los eventos del usuario.';
$string['calendar:event_imgdetail'] = 'Imagen por defecto';
$string['calendar:event_imgdetail_help'] = 'Imagen por defecto asociada al evento. Se utiliza para decorar la interfaz de usuario.';
$string['calendar:examen_event_type'] = 'Tipo "Examen"';
$string['calendar:examen_event_type_help'] = 'Actividades que se marcarán como "Examen" en el "calendario único"';
$string['calendar:share_course_events'] = 'Devolver los eventos de los cursos';
$string['calendar:share_course_events_help'] = 'Devolver los eventos de los cursos';
$string['calendar:share_site_events'] = 'Devolver los eventos de la institución';
$string['calendar:share_site_events_help'] = 'Devolver los eventos de la institución';
$string['calendar:share_user_events'] = 'Devolver los eventos personales';
$string['calendar:share_user_events_help'] = 'Devolver los eventos personales';
$string['calendarheader'] = 'Servicios de eventos de calendario';
$string['calendarheader_help'] = 'AppCRUE integra eventos de calendario de dos formas: <ul><li>Calendario único es el servicio de integración original.</li><li>API de integración de LMS.</li></ul>';
$string['course_pattern'] = 'Patrón SQL para buscar un curso';
$string['course_pattern_help'] = 'El patrón SQL se utilizará para buscar un curso utilizando el campo "idnumber" y los parámetros de la petición autologin "course, group, param1, param2, param3" (no necesariamente ids de moodle)';
$string['enable_autologin'] = 'Habilita Autologin';
$string['enable_autologin_help'] = 'El servicio Autologin toma el token de un usuario, lo verifica (opcionalmente) y lo redirige a una url interna. Si no se usa un token se puede redirigir a otras URLs sin afectar a la sesión.';
$string['enable_avatar'] = 'Activar el servicio de avatar';
$string['enable_avatar_help'] = 'El servicio de avatar toma el token de un usuario, lo verifica y devuelve la imagen del usuario.';
$string['enable_sitemap'] = 'Habilitar el servicio de mapa del sitio';
$string['enable_sitemap_help'] = 'El servicio Sitemap genera una representación JSON de las categorías y los cursos.';
$string['idp_client_id'] = 'Clientid para consultar al IDP';
$string['idp_client_id_help'] = 'Id. del cliente para que el IDP invoque el servicio de token de usuario.';
$string['idp_client_secret'] = 'Client secret para llamar al IDP';
$string['idp_client_secret_help'] = 'Client secret para que el IDP invoque el servicio de token de usuario.';
$string['idp_token_url'] = 'Resolución de identidad delToken';
$string['idp_token_url_help'] = 'URL del servicio del IdP para resolver la identidad del propietario del usertoken.';
$string['idp_url'] = 'URL del IDP';
$string['idp_url_help'] = 'URL del IDP para autenticar al servicio.';
$string['idp_user_json_path'] = 'Selector del identificador';
$string['idp_user_json_path_help'] = 'Selector tipo jsonpath para identificar el valor para identificar al usuario.';
$string['idpheader'] = 'Verificación de tokens';
$string['idpheader_help'] = 'Configuración del servico de verificación de tokens AppCrue. Válido para instituciones que usan un IdP OAUth.';
$string['match_user_by'] = 'Campo de coincidencia con el perfil del usuario';
$string['match_user_by_help'] = 'El token de autorización devuelve una identificación que debe coincidir con un campo de usuario.';
$string['notify:new_grade_message'] = '### Nota para {$a->subjectname}' . "\n" . 'Tu nota es {$a->gradealpha} ({$a->grade}).' . "\n" . '{$a->revdateformat}.' . "\n" . '{$a->comment}';
$string['notify_grade_anyteacher'] = 'Cualquier profesor';
$string['notify_grade_header'] = 'Web service para notificar las notas a los estudiantes';
$string['notify_grade_header_help'] = 'El servicio de notificación de notas envía un mensaje al usuario con la nota y el comentario del profesor.';
$string['notify_grade_revdate'] = 'La revisión será el {$a->revdateformat}';
$string['notify_grade_revdate_null'] = 'No se ha especificado la fecha de revisión.';
$string['notify_grade_sender'] = 'Remitente';
$string['notify_grade_sender_help'] = 'Remitente del mensaje de notificación de notas.';
$string['notify_grade_webserviceuser'] = 'Usuario que invoca el servicio web';
$string['pattern_lib'] = 'Lista de patrones para crear URL de redirección.';
$string['pattern_lib_help'] = 'Lista de patrones de URL para generar urls de redirección. Cada línea define un patrón que puede ser solicitado para generar urls de redirección. Los parámetros permitidos son: curso, grupo, param1, param2';
$string['pluginname'] = 'Servicios de conexión a AppCrue';

$string['privacy:metadata'] = 'El plugin "AppCrue connection services" no almacena ningún dato personal.';

$string['sitemapheader'] = 'Servicio de mapa del sitio';
$string['sitemapheader_help'] = 'Genera una estructura JSON de categorías y cursos.';

$string['lmsappcrue:header'] = 'AppCRUE-LMS Widgets integración';
$string['lmsappcrue:header_help'] = 'Configuración del servicio de integración de Widgets de AppCRUE-LMS';
$string['lmsappcrue:calendar:enable_calendar'] = 'Activar el API de calendario LMS';
$string['lmsappcrue:calendar:enable_calendar_help'] = 'El API de calendario LMS permite a las aplicaciones externas obtener eventos del calendario de Moodle para un usuario.';
$string['lmsappcrue:grades'] = 'AppCRUE calificaciones';
$string['lmsappcrue:grades_help'] = 'Exponer las calificaciones del usuario a AppCRUE.';
$string['lmsappcrue:enable_grades'] = 'Activar el API de calificaciones LMS';
$string['lmsappcrue:enable_grades_help'] = 'El API de calificaciones LMS permite a las aplicaciones externas obtener calificaciones de Moodle para un usuario.';
$string['lmsappcrue:forums'] = 'AppCRUE foros';
$string['lmsappcrue:forums_help'] = 'Exponer las conversaciones del foro de usuarios a AppCRUE.';
$string['lmsappcrue:forums_timewindow'] = 'Ventana de tiempo para foros';
$string['lmsappcrue:forums_timewindow_help'] = 'Ventana de tiempo para recuperar publicaciones del foro. Se utiliza para limitar el número de publicaciones que devuelve el servicio.';
$string['lmsappcrue:enable_forums'] = 'Activar el API de foros LMS';
$string['lmsappcrue:enable_forums_help'] = 'El API de foros LMS permite a las aplicaciones externas obtener información de los foros de Moodle para un usuario.';
$string['lmsappcrue:invalidtimerange'] = 'Rango de tiempo no válido';
$string['lmsappcrue:announcements'] = 'AppCRUE anuncios';
$string['lmsappcrue:announcements_help'] = 'Exponer los anuncios de usuario a AppCRUE.';
$string['lmsappcrue:enable_announcements'] = 'Activar el API de anuncios LMS';
$string['lmsappcrue:enable_announcements_help'] = 'El API de anuncios LMS permite a las aplicaciones externas obtener anuncios de los foros de Moodle para un usuario.';
$string['lmsappcrue:files'] = 'AppCRUE archivos';
$string['lmsappcrue:files_help'] = 'Exponer los archivos de usuario a AppCRUE.';
$string['lmsappcrue:enable_files'] = 'Activar el API de archivos LMS';
$string['lmsappcrue:enable_files_help'] = 'El API de archivos LMS permite a las aplicaciones externas obtener archivos de Moodle para un usuario.';
$string['lmsappcrue:assignments'] = 'AppCRUE asignaciones';
$string['lmsappcrue:assignments_help'] = 'Exponer las asignaciones del usuario a AppCRUE. Este punto de acceso permite a AppCRUE recuperar las asignaciones del usuario, sus fechas de vencimiento y estado.';
$string['lmsappcrue:enable_assignments'] = 'Activar el API de asignaciones LMS';
$string['lmsappcrue:enable_assignments_help'] = 'El API de asignaciones LMS permite a las aplicaciones externas obtener asignaciones de Moodle para un usuario.';
