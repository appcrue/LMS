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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_appcrueservices', get_string('pluginname', 'local_appcrueservices'));

    // Show plugin description.
    $settings->add(new admin_setting_heading(
        'local_appcrueservices/desc',
        '',
        get_string('desc', 'local_appcrueservices')
    ));
    
    // Token configuration.
    $settings->add(new admin_setting_configtext(
        'local_appcrueservices/wstoken',
        get_string('wstoken', 'local_appcrueservices'),
        get_string('wstoken_desc', 'local_appcrueservices'),
        '',
        PARAM_ALPHANUMEXT
    ));
    
    // API key configuration.
    $settings->add(new admin_setting_configpasswordunmask(
        'local_appcrueservices/apikey', // Configuration name (stored in mdl_config).
        get_string('apikey', 'local_appcrueservices'), // Display name in the admin panel.
        get_string('apikey_desc', 'local_appcrueservices'), // Description (optional).
        '', // Default value (empty).
        PARAM_RAW // Parameter type (allows special characters).
    ));
    
    // Add the settings page to the Local Plugins menu.
    $ADMIN->add('localplugins', $settings);
}
