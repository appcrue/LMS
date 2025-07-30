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
 * AppCrue services plugin version information.
 *
 * @package local_appcrue
 * @category admin
 * @author  Juan Pablo de Castro
 * @copyright 2021 onwards juanpablo.decastro@uva.es
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_appcrue', get_string('pluginname', 'local_appcrue'));
    $ADMIN->add('localplugins', $settings);
    $settings->add(
        new admin_setting_heading(
            'local_appcrue/idp_header',
            get_string('idpheader', 'local_appcrue'),
            get_string('idpheader_help', 'local_appcrue')
        )
    );
    // API KEY for directa access without token.
    // Generate a deafult as an example.
    $defaultkey = bin2hex(random_bytes(16));

    // API Key configuration.
    $settings->add(new admin_setting_configtext(
        'local_appcrue/api_key',
        get_string('api_key', 'local_appcrue'),
        get_string('api_key_help', 'local_appcrue'),
        $defaultkey,
        PARAM_ALPHANUMEXT
    ));
    // IdP configuration.
    $settings->add(new admin_setting_configtext(
        'local_appcrue/idp_token_url',
        get_string('idp_token_url', 'local_appcrue'),
        get_string('idp_token_url_help', 'local_appcrue'),
        'https://idp.uva.es/api/adas/oauth2/tokendata',
        PARAM_URL
    ));
    $settings->add(new admin_setting_configtext(
        'local_appcrue/idp_user_json_path',
        get_string('idp_user_json_path', 'local_appcrue'),
        get_string('idp_user_json_path_help', 'local_appcrue'),
        '.USUARIO_MOODLE',
        PARAM_RAW_TRIMMED
    ));
    $fields = get_user_fieldnames();
    require_once($CFG->dirroot . '/user/profile/lib.php');
    $customfields = profile_get_custom_fields();
    $userfields = [];
    // Make the keys string values and not indexes.
    foreach ($fields as $field) {
        $userfields[$field] = $field;
    }
    foreach ($customfields as $field) {
        $userfields["profile_field_{$field->shortname}"] = $field->name;
    }
    $settings->add(new admin_setting_configselect(
        'local_appcrue/match_user_by',
        get_string('match_user_by', 'local_appcrue'),
        get_string('match_user_by_help', 'local_appcrue'),
        'id',
        $userfields
    ));


    // Autologin.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_autologin_header',
            get_string('autologinheader', 'local_appcrue'),
            get_string('autologinheader_help', 'local_appcrue')
        )
    );
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/enable_autologin',
        get_string('enable_autologin', 'local_appcrue'),
        get_string('enable_autologin_help', 'local_appcrue'),
        true
    ));
    // Select "bearer" or "token" mark in deep urls.
    $settings->add(new admin_setting_configselect(
        'local_appcrue/deep_url_token_mark',
        get_string('autologin:deep_url_token_mark', 'local_appcrue'),
        get_string('autologin:deep_url_token_mark_help', 'local_appcrue'),
        'bearer',
        [
            '' => get_string('autologin:deep_url_token_mark_disabled', 'local_appcrue'),
            'bearer' => get_string('autologin:deep_url_token_mark_bearer', 'local_appcrue'),
            'token' => get_string('autologin:deep_url_token_mark_token', 'local_appcrue'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/allow_continue',
        get_string('allow_continue', 'local_appcrue'),
        get_string('allow_continue_help', 'local_appcrue'),
        true
    ));
    $settings->add(new admin_setting_configtext(
        'local_appcrue/course_pattern',
        get_string('course_pattern', 'local_appcrue'),
        get_string('course_pattern_help', 'local_appcrue'),
        '%-{course}-{group}-%',
        PARAM_RAW_TRIMMED
    ));
    $settings->add(new admin_setting_configtextarea(
        'local_appcrue/pattern_lib',
        get_string('pattern_lib', 'local_appcrue'),
        get_string('pattern_lib_help', 'local_appcrue'),
        "course=/course/view.php?id={course}\nguia=https://docserver/grades/{param1}/{param2}/{course}/doc.pdf",
        PARAM_RAW_TRIMMED
    ));
    // Avatar service.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_avatar_header',
            get_string('avatarheader', 'local_appcrue'),
            get_string('avatarheader_help', 'local_appcrue')
        )
    );
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/enable_avatar',
        get_string('enable_avatar', 'local_appcrue'),
        get_string('enable_avatar_help', 'local_appcrue'),
        true
    ));
    // SiteMap service.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_sitemap_header',
            get_string('sitemapheader', 'local_appcrue'),
            get_string('sitemapheader_help', 'local_appcrue')
        )
    );
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/enable_sitemap',
        get_string('enable_sitemap', 'local_appcrue'),
        get_string('enable_sitemap_help', 'local_appcrue'),
        true
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/cache_sitemap',
        get_string('cache_sitemap', 'local_appcrue'),
        get_string('cache_sitemap_help', 'local_appcrue'),
        true
    ));
    $settings->add(new admin_setting_configduration(
        'local_appcrue/cache_sitemap_ttl',
        get_string('cache_sitemap_ttl', 'local_appcrue'),
        get_string('cache_sitemap_ttl_help', 'local_appcrue'),
        60 * 60,
        3600
    ));
     // Notify grade Service.
     $settings->add(
         new admin_setting_heading(
             'local_appcrue_notifygrade_header',
             get_string('notify_grade_header', 'local_appcrue'),
             get_string('notify_grade_header_help', 'local_appcrue')
         )
     );
    $settings->add(new admin_setting_configselect(
        'local_appcrue/notify_grade_sender',
        get_string('notify_grade_sender', 'local_appcrue'),
        get_string('notify_grade_sender_help', 'local_appcrue'),
        'id',
        ['anyteacher' => get_string('notify_grade_anyteacher', 'local_appcrue'),
        'webserviceuser' => get_string('notify_grade_webserviceuser', 'local_appcrue'),
        ]
    ));
    // Section for LMS AppCRUE integration.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_lms_header',
            get_string('lmsappcrue:header', 'local_appcrue'),
            get_string('lmsappcrue:header_help', 'local_appcrue')
        )
    );
    // Select mapping field.
    $settings->add(new admin_setting_configselect(
        'local_appcrue/lmsappcrue_match_user_by',
        get_string('lmsappcrue:match_user_by', 'local_appcrue'),
        get_string('lmsappcrue:match_user_by_help', 'local_appcrue'),
        'email',
        $userfields
    ));
    global $DB;
    $modules = $DB->get_records("modules");
    $modulelist = [];
    foreach ($modules as $mod) {
        $modulelist[$mod->name] = get_string("modulename", "$mod->name", null, true);
    }
    uasort($modulelist, function ($a, $b) {
        return strcmp($a, $b);
    });
    $settings->add(new admin_setting_configmultiselect(
        'local_appcrue/calendar_examen_event_type',
        get_string('calendar:examen_event_type', 'local_appcrue'),
        get_string('calendar:examen_event_type_help', 'local_appcrue'),
        ['quiz', 'quest', 'assign', 'workshop'],
        $modulelist
    ));
    // Calendar.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_calendar_header',
            get_string('calendarheader', 'local_appcrue'),
            get_string('calendarheader_help', 'local_appcrue')
        )
    );
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/enable_usercalendar',
        get_string('calendar:enable_calendar', 'local_appcrue'),
        get_string('calendar:enable_calendar_help', 'local_appcrue'),
        true
    ));
    // Enable LMS AppCRUE calendar endpoint.
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/lmsappcrue_enable_calendar',
        get_string('lmsappcrue:calendar:enable_calendar', 'local_appcrue'),
        get_string('lmsappcrue:calendar:enable_calendar_help', 'local_appcrue'),
        true
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/calendar_share_site_events',
        get_string('calendar:share_site_events', 'local_appcrue'),
        get_string('calendar:share_site_events_help', 'local_appcrue'),
        true
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/calendar_share_course_events',
        get_string('calendar:share_course_events', 'local_appcrue'),
        get_string('calendar:share_course_events_help', 'local_appcrue'),
        true
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/share_personal_events',
        get_string('calendar:share_user_events', 'local_appcrue'),
        get_string('calendar:share_user_events_help', 'local_appcrue'),
        true
    ));

    $settings->add(new admin_setting_configtext(
        'local_appcrue/calendar_event_imgdetail',
        get_string('calendar:event_imgdetail', 'local_appcrue'),
        get_string('calendar:event_imgdetail_help', 'local_appcrue'),
        '',
        PARAM_URL
    ));
    // LMS AppCRUE grades endpoint.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_lms_grades_header',
            get_string('lmsappcrue:grades', 'local_appcrue'),
            get_string('lmsappcrue:grades_help', 'local_appcrue')
        )
    );

    // Enable LMS AppCRUE grades endpoint.
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/lmsappcrue_enable_grades',
        get_string('lmsappcrue:enable_grades', 'local_appcrue'),
        get_string('lmsappcrue:enable_grades_help', 'local_appcrue'),
        true
    ));
    // LMS AppCRUE forums section.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_lms_forums_header',
            get_string('lmsappcrue:forums', 'local_appcrue'),
            get_string('lmsappcrue:forums_help', 'local_appcrue')
        )
    );
    // Enable LMS AppCRUE forums endpoint.
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/lmsappcrue_enable_forums',
        get_string('lmsappcrue:enable_forums', 'local_appcrue'),
        get_string('lmsappcrue:enable_forums_help', 'local_appcrue'),
        true
    ));
    // Time window for forums.
    $settings->add(new admin_setting_configduration(
        'local_appcrue/lmsappcrue_forums_timewindow',
        get_string('lmsappcrue:forums_timewindow', 'local_appcrue'),
        get_string('lmsappcrue:forums_timewindow_help', 'local_appcrue'),
        WEEKSECS * 4 * 6, // Default 6 months.
        WEEKSECS * 4
    ));

    // LMS AppCRUE files section.
    $settings->add(
        new admin_setting_heading(
            'local_appcrue_lms_files_header',
            get_string('lmsappcrue:files', 'local_appcrue'),
            get_string('lmsappcrue:files_help', 'local_appcrue')
        )
    );
    // Enable LMS AppCRUE files endpoint.
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/lmsappcrue_enable_files',
        get_string('lmsappcrue:enable_files', 'local_appcrue'),
        get_string('lmsappcrue:enable_files_help', 'local_appcrue'),
        true
    ));

    // Enable LMS AppCRUE notifications endpoint.
    $settings->add(new admin_setting_heading(
        'local_appcrue_lms_notifications_header',
        get_string('lmsappcrue:notifications', 'local_appcrue'),
        get_string('lmsappcrue:notifications_help', 'local_appcrue')
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/lmsappcrue_enable_notifications',
        get_string('lmsappcrue:enable_notifications', 'local_appcrue'),
        get_string('lmsappcrue:enable_notifications_help', 'local_appcrue'),
        true
    ));

    // LMS AppCRUE assignments endpoint.
    $settings->add(new admin_setting_heading(
        'local_appcrue_lms_assignments_header',
        get_string('lmsappcrue:assignments', 'local_appcrue'),
        get_string('lmsappcrue:assignments_help', 'local_appcrue')
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_appcrue/lmsappcrue_enable_assignments',
        get_string('lmsappcrue:enable_assignments', 'local_appcrue'),
        get_string('lmsappcrue:enable_assignments_help', 'local_appcrue'),
        true
    ));
}
